<?php

namespace Digidirect\Vii\Model\Processor;

use Digidirect\AI\Model\Engine\Processor\ProcessorInterface;
use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity as ViiAbstractGiftCardEntity;
use Digidirect\Vii\Model\ResourceModel\UndoQueue;
use Digidirect\Vii\Service\Config\Config;
use Digidirect\Vii\Model\ServiceTransactionManagement;
use Digidirect\Vii\Service\Exeption\ServiceExeption;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Digidirect\AI\Model\Logger\Logger;

class UndoProcess extends \Digidirect\Vii\Model\Processor\ProcessAbstract implements ProcessorInterface
{
    const PROCESS_CODE = 'digidirect_vii_undo';

    /**
     * @var bool
     */
    protected $useQueue = false;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var UndoQueue
     */
    protected $undoQueueResource;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ServiceTransactionManagement
     */
    protected $serviceTransactionManagement;

    /**
     * UndoProcess constructor.
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param CollectionFactory $orderCollectionFactory
     * @param UndoQueue $undoQueueResource
     * @param Config $config
     * @param ServiceTransactionManagement $serviceTransactionManagement
     * @param null $initParams
     */
    public function __construct(
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        CartRepositoryInterface $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        CollectionFactory $orderCollectionFactory,
        UndoQueue $undoQueueResource,
        Config $config,
        ServiceTransactionManagement $serviceTransactionManagement,
        $initParams = null
    ) {
        parent::__construct(
            $giftCAHelper,
            $giftcardaccountFactory,
            $abstractGiftCardEntityRepository,
            $viiGiftCardEntityRepository,
            $initParams
        );
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->undoQueueResource = $undoQueueResource;
        $this->config = $config;
        $this->serviceTransactionManagement = $serviceTransactionManagement;
    }

    /**
     * @return bool
     * @throws \Digidirect\AI\Model\Engine\Exception\EngineException
     * @throws \Digidirect\AI\Model\Engine\Processor\Exception\ProcessException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function process()
    {
        $requestParameters = $this->getRunOptions();
        $this->validateRequestParams();
        $queueItemId = $requestParameters['id'];
        $quote = $requestParameters['quote_id'];
        $transId = $requestParameters['last_trans_id'];
        $abstractGiftCardEntityId = $requestParameters['abstract_gift_card_entity_id'];
        if (is_numeric($quote)) {
            $quote = $this->quoteRepository->get($quote);
        }

        if (!$quote instanceof CartInterface) {
            throw new \InvalidArgumentException('Invalid request param.');
        }

        try {
            /**
             * @var \Magento\GiftCardAccount\Model\GiftCardAccount $giftCardAccount
             * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $entity
             */
            $entity = $this->abstractGiftCardEntityRepository->get($abstractGiftCardEntityId);
            $giftCardAccount = $this->giftCardAccountFactory->create()->loadByCode($entity->getCode());
            $entityQuoteData = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                $entity,
                $quote->getId()
            );

            $service = $entity->getService();
            $service->setQuote($quote);
            $service->setStore($quote->getStoreId());

            $this->getLogger()->addIdentifyingParams(
                [
                    'gift_card' => $entity->getCode()
                ]
            );

            if ($quote->getReservedOrderId()) {
                $order = $this->getOrder($quote);
                if ($order->getId()) {
                    $service->setOrder($order);
                }
            }
            $service->validate()->undo($transId);
            $queueItemAfterUndo = $this->undoQueueResource->getById($queueItemId);
            $status = isset($queueItemAfterUndo[UndoQueue::STATUS]) ? $queueItemAfterUndo[UndoQueue::STATUS] : null;
            $token = isset($queueItemAfterUndo[UndoQueue::TOKEN]) ? $queueItemAfterUndo[UndoQueue::TOKEN] : null;

            // remove queue item from table if undo was responded
            $this->undoQueueResource->deleteRecordById($queueItemId);
            if ($this->isHold($status) && $token) {
                if ($service->getOrder()) {
                    //if nothing to revert try to redeem one more time
                    try {
                        $entityOrderData = new \Magento\Framework\DataObject(['status' => $entity->getStatus()]);
                        $order = $service->getOrder();
                        $amount = $entityQuoteData->getAmount();
                        if ($this->config->isAcceptAvailable($order) && $entityQuoteData) {
                            $service->validate()->accept($amount, $token);
                            $entityOrderData->setStatus(AbstractGiftCardEntity::STATUS_ACCEPT);
                            $entityOrderData->setOrderId($order->getId());
                            $entityOrderData->setAmount($amount);
                            $entityOrderData->setToken($token);
                            $entityOrderData->setAbstractGiftCardEntityId($entity->getEntityId());
                            $this->abstractGiftCardEntityRepository->saveEntityOrderData($entityOrderData);
                        }
                    } catch (\Exception $exception) {
                        if ($exception instanceof ServiceExeption) {
                            $this->serviceTransactionManagement->pushUndoProcessToQueue($service);
                        }
                        $this->getLogger()->info($exception->getMessage(), [], Logger::LOG_PLACE_FILE_AND_DB);
                    }
                } else {
                    $service->validate()->cancel(__('Service does not response'), $token);
                }
            }

            if ($this->isReversed($status)) {
                if ($service->getOrder()) {
                    return true;
                }
                $cards = $this->giftCAHelper->getCards($quote);
                foreach ($cards as $card) {
                    if ($card['c'] != $entity->getCode()) {
                        continue;
                    }
                    try {
                        $giftCardAccount->removeFromCart(true, $quote);
                    } catch (LocalizedException $exception) {
                        $this->getLogger()->error($exception->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->getLogger()->info($e->getMessage(), [], Logger::LOG_PLACE_FILE_AND_DB);
        }
        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($quote)
    {
        $collection = $this->orderCollectionFactory->create();
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         */
        $order = $collection->addAttributeToFilter(OrderInterface::INCREMENT_ID, $quote->getReservedOrderId())
            ->addAttributeToFilter(OrderInterface::QUOTE_ID, $quote->getId())
            ->getFirstItem();
        return $order;
    }

    /**
     * @param int $status
     * @return bool
     */
    public function isReversed($status)
    {
        return $status == ViiAbstractGiftCardEntity::ENTITY_STATUS_REVERSED;
    }

    /**
     * @param int $status
     * @return bool
     */
    public function isHold($status)
    {
        return $status == AbstractGiftCardEntity::STATUS_HOLD;
    }

    /**
     * @return void
     */
    public function validateRequestParams()
    {
        $requestParameters = $this->getRunOptions();

        if (!isset($requestParameters['id'])) {
            throw new \InvalidArgumentException('Invalid request param. Queue Item Id should be specified.');
        }

        if (!isset($requestParameters['quote_id'])) {
            throw new \InvalidArgumentException('Invalid request param.');
        }

        if (!isset($requestParameters['last_trans_id'])) {
            throw new \InvalidArgumentException(
                'Invalid request param. Last transaction id is not specified.'
            );
        }

        if (!isset($requestParameters['abstract_gift_card_entity_id'])) {
            throw new \InvalidArgumentException(
                'Invalid request param. Abstract Gift Card Entity Id is not specified.'
            );
        }
    }
}
