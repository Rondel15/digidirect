<?php

namespace Digidirect\Vii\Service\Response\Undo;

use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;
use Digidirect\AbstractGiftCard\Service\Response\HandlerInterface;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity;
use Digidirect\Vii\Model\ResourceModel\UndoQueue;
use Digidirect\Vii\Model\Mail;
use Digidirect\Vii\Api\Data\OrderInterface as ViiOrderInterface;
use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity as BaseAbstractGiftCardEntity;
use Digidirect\AbstractGiftCard\Model\Service\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Monolog\Logger as MonologLogger;

/**
 * Class Handler
 * @package Digidirect\Vii\Service\Response\PreAuthCancellation
 */
class Handler implements HandlerInterface
{
    const RESPONSE_MESSAGE_NOT_REVERSED = 'Ok - No Transaction to reverse';
    const RESPONSE_MESSAGE_REVERSED = 'Ok - Transaction Reversed';

    /**
     * @var AbstractGiftCardEntity
     */
    protected $abstractGiftCardEntityResource;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * @var UndoQueue
     */
    protected $undoQueueResource;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Mail
     */
    protected $mail;

    /**
     * Handler constructor.
     * @param AbstractGiftCardEntity $abstractGiftCardEntityResource
     * @param OrderRepositoryInterface $orderRepository
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param UndoQueue $undoQueueResource
     * @param Logger $logger
     * @param Mail $mail
     */
    public function __construct(
        AbstractGiftCardEntity $abstractGiftCardEntityResource,
        OrderRepositoryInterface $orderRepository,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        UndoQueue $undoQueueResource,
        Logger $logger,
        Mail $mail
    ) {
        $this->abstractGiftCardEntityResource = $abstractGiftCardEntityResource;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->orderRepository = $orderRepository;
        $this->undoQueueResource = $undoQueueResource;
        $this->logger = $logger;
        $this->mail = $mail;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response)
    {
        $serviceDO = SubjectReader::readService($handlingSubject);
        $service = $serviceDO->getService();
        $entity = $service->getAbstractGiftCardEntity();
        $checkResponse = $serviceDO->getCheckResponse();
        $lastTransId = $checkResponse['viiTranIdToUndo'];
        $responseMessage = $checkResponse['ResponseMessage'];

        if ($responseMessage == self::RESPONSE_MESSAGE_REVERSED) {
            $object = $service->getOrder() ?? ($service->getQuote() ?? $serviceDO->getQuote());
            $quoteId = ($object instanceof OrderInterface) ? $object->getQuoteId() : $object->getId();

            $queueItem = $this->undoQueueResource->getRecordByCondition(
                [
                    'quote_id' => $quoteId,
                    'abstract_gift_card_entity_id' => $entity->getEntityId(),
                    'last_trans_id' => $lastTransId
                ]
            );
            $entityQuoteData = $this->abstractGiftCardEntityResource->getEntityQuoteData($entity, $quoteId);
            if (!$queueItem) {
                $this->setReversedStatusToEntityQuote($entity->getEntityId(), $quoteId);
            } else {
                $this->undoQueueResource->updateRecord(
                    ['status' => AbstractGiftCardEntity::ENTITY_STATUS_REVERSED],
                    [
                        'quote_id = ?' => $quoteId,
                        'abstract_gift_card_entity_id = ?' => $entity->getEntityId(),
                        'token = ?' => $queueItem['token']
                    ]
                );

                if ($entityQuoteData->getToken() == $queueItem['token']) {
                    $this->setReversedStatusToEntityQuote($entity->getEntityId(), $quoteId);
                }
            }

            /**
             * @var \Magento\Sales\Model\Order $order
             */
            if ($order = $service->getOrder()) {
                $this->cancelOrder($order, $entity);
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $entity
     * @return $this
     * @throws LocalizedException
     */
    public function cancelOrder($order, $entity)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $entityOrderData = $entity->getEntityOrderData($order->getEntityId());
        try {
            if (!$order->canCancel()) {
                $order->setStatus(ViiOrderInterface::GIFT_CARD_REVERSED_ORDER_STATUS);
                $order->addCommentToStatusHistory(
                    __('Vii service reverted the transaction.')
                );
                $this->orderRepository->save($order);
                return $this;
            }

            if ($entityOrderData->getOrderId()) {
                $entityOrderData->setStatus(BaseAbstractGiftCardEntity::STATUS_CANCEL);
                $this->abstractGiftCardEntityRepository->saveEntityOrderData($entityOrderData);
            }

            $order->cancel();
            $order->addCommentToStatusHistory(
                __('The order was canceled because Vii service reverted the transaction.')
            );
            $this->orderRepository->save($order);
            $this->logger->debug(
                [
                    'info' => __(
                        'Order: %1 was canceled because Vii service reverted the transaction.',
                        $order->getIncrementId()
                    )
                ],
                MonologLogger::INFO
            );
            $this->sendOrderCancelationNotification($order);
        } catch (LocalizedException $exception) {
            $this->logger->debug(
                ['error' => $exception->getMessage()],
                MonologLogger::ERROR
            );
        }
        return $this;
    }

    /**
     * @param int $entityId
     * @param int $quoteId
     * @return void
     */
    protected function setReversedStatusToEntityQuote($entityId, $quoteId)
    {
        $this->abstractGiftCardEntityResource->updateEntityQuoteData(
            ['status' => AbstractGiftCardEntity::ENTITY_STATUS_REVERSED],
            [
                'quote_id = ?' => $quoteId,
                'abstract_gift_card_entity_id = ?' => $entityId
            ]
        );
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function sendOrderCancelationNotification($order)
    {
        $emailVars = [
            'order' => $order,
            'store' => $order->getStore()
        ];

        $recipientsList = $order->getCustomerEmail();
        try {
            $this->mail->sendOrderCancelEmail($recipientsList, $emailVars, $order->getStoreId());
        } catch (LocalizedException $e) {
            $this->logger->debug(
                ['error' => $e->getMessage()],
                MonologLogger::ERROR
            );
        }
    }
}
