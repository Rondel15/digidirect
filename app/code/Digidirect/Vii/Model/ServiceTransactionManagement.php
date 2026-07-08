<?php

namespace Digidirect\Vii\Model;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface;
use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity as ViiAbstractGiftCardEntity;
use Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface as ViiAbstractGiftCardEntityRepositoryInterface;
use Digidirect\Vii\Model\ResourceModel\UndoQueue;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\GiftCardAccount\Model\GiftcardaccountFactory;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger as MonologLoger;

class ServiceTransactionManagement implements \Digidirect\Vii\Api\ServiceTransactionManagementInterface
{
    /**
     * @var SessionManager
     */
    protected $session;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var GiftcardaccountFactory
     */
    protected $giftCardAccountFactory;

    /**
     * @var AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * @var ViiAbstractGiftCardEntityRepositoryInterface
     */
    protected $viiGiftCardEntityRepository;

    /**
     * @var ViiAbstractGiftCardEntity
     */
    protected $abstractGiftCardEntity;

    /**
     * @var UndoQueue
     */
    protected $undoQueueResource;

    /**
     * @var AbstractGiftCardLoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $pushedToQueue = false;

    /**
     * ServiceTransactionManagement constructor.
     * @param SessionManager $session
     * @param CartRepositoryInterface $cartRepository
     * @param GiftcardaccountFactory $giftcardAccountFactory
     * @param AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param ViiAbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param ViiAbstractGiftCardEntity $abstractGiftCardEntity
     * @param UndoQueue $undoQueueResource
     * @param AbstractGiftCardLoggerInterface $logger
     */
    public function __construct(
        SessionManager $session,
        CartRepositoryInterface $cartRepository,
        GiftcardaccountFactory $giftcardAccountFactory,
        AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        ViiAbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        ViiAbstractGiftCardEntity $abstractGiftCardEntity,
        UndoQueue $undoQueueResource,
        AbstractGiftCardLoggerInterface $logger
    ) {
        $this->session = $session;
        $this->cartRepository = $cartRepository;
        $this->giftCardAccountFactory = $giftcardAccountFactory;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->viiGiftCardEntityRepository = $viiGiftCardEntityRepository;
        $this->abstractGiftCardEntity = $abstractGiftCardEntity;
        $this->undoQueueResource = $undoQueueResource;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function isProcessQueued()
    {
        return $this->pushedToQueue;
    }

    /**
     * @param int $quoteId
     * @param null|\Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $forceRemove
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reversePreviousTransaction($quoteId, $order = null, $forceRemove = false)
    {
        $lastTransId = $this->session->getData('last_failed_transaction_id');
        $abstractGiftCardEntityId = $this->session->getData('last_failed_abstract_gift_card_entity');

        $result = false;
        if ($lastTransId && $abstractGiftCardEntityId) {
            $quote = $this->cartRepository->get($quoteId);
            $entity = $this->abstractGiftCardEntityRepository->get($abstractGiftCardEntityId);
            $giftCardAccount = $this->giftCardAccountFactory->create()->loadByCode($entity->getCode());
            $entityQuoteData = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                $entity,
                $quote->getId()
            );
            $service = $entity->getService();
            $service->setLastTransId($lastTransId);
            $service->setQuote($quote);
            $service->setStore($quote->getStoreId());
            if ($order) {
                $service->setOrder($order);
            }

            if ($entityQuoteData && $entityQuoteData->getStatus() == AbstractGiftCardEntity::STATUS_HOLD) {
                try {
                    $service->validate()->undo($lastTransId);
                    $entityQuoteDataUpdated = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                        $entity,
                        $quote->getId()
                    );
                    if ($entityQuoteDataUpdated
                        && $entityQuoteDataUpdated->getStatus() == ViiAbstractGiftCardEntity::ENTITY_STATUS_REVERSED
                    ) {
                        if ($forceRemove) {
                            $this->removeGiftCartAccountFromQuote($quote, $giftCardAccount);
                        }
                        $result = true;
                    }
                } catch (LocalizedException $exception) {
                    if ($this->pushUndoProcessToQueue($service)) {
                        $this->pushedToQueue = true;
                        if ($forceRemove) {
                            $this->removeGiftCartAccountFromQuote($quote, $giftCardAccount);
                        }
                        $result = true;
                    }
                }
            }
            $this->session->unsLastFailedTransactionId();
            $this->session->unsLastFailedAbstractGiftCardEntity();
        }
        return $result;
    }

    /**
     * @param \Digidirect\Vii\Model\Service\Adapter $service
     * @return bool
     */
    public function pushUndoProcessToQueue($service)
    {
        /**
         * @var \Digidirect\Vii\Model\Service\Adapter $service
         */
        $result = false;
        $lastTransId = $service->getLastTransId();
        $entity = $service->getAbstractGiftCardEntity();
        $quote = $service->getQuote();
        $entityQuoteData = $this->abstractGiftCardEntity->getEntityQuoteData($entity, $quote->getId());
        if ($entityQuoteData
            && $entityQuoteData->getStatus() == AbstractGiftCardEntity::STATUS_HOLD
        ) {
            $data = [
                UndoQueue::ABSTRACT_GIFT_CARD_ENTITY_ID => $entityQuoteData->getAbstractGiftCardEntityId(),
                UndoQueue::QUOTE_ID => $quote->getId(),
                UndoQueue::LAST_TRANS_ID => $lastTransId,
                UndoQueue::TOKEN => $entityQuoteData->getToken(),
                UndoQueue::STATUS => $entityQuoteData->getStatus()
            ];

            try {
                $result = $this->undoQueueResource->addRecord($data);
            } catch (\Throwable $e) {
                $message = 'Can not add record to queue. Error message: ' . $e->getMessage();
                $this->logger->debug($message, MonologLoger::ERROR);
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Magento\GiftCardAccount\Model\Giftcardaccount $giftCardAccount
     * @return void
     */
    public function removeGiftCartAccountFromQuote($quote, $giftCardAccount)
    {
        $quote->setIsActive(true);
        $giftCardAccount->removeFromCart(true, $quote);
    }
}
