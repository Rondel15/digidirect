<?php

namespace Digidirect\Vii\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Digidirect\Vii\Service\Config\Config;
use Magento\Sales\Model\Order;
use Digidirect\Vii\Model\Processor\Order\CancellationProcess;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity;
use Digidirect\AI\Helper\Queue as QueueHelper;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class CancelOrder
 * @package Digidirect\Vii\Cron
 */
class CancelOrder
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var AbstractGiftCardEntity
     */
    protected $abstractGiftCardEntityResource;

    /**
     * @var QueueHelper
     */
    protected $queue;

    /**
     * CancelOrder constructor.
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param AbstractGiftCardEntity $abstractGiftCardEntity
     * @param QueueHelper $queue
     */
    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        AbstractGiftCardEntity $abstractGiftCardEntity,
        QueueHelper $queue
    ) {
        $this->config = $config;
        $this->orderCollectionFactory = $collectionFactory;
        $this->abstractGiftCardEntityResource = $abstractGiftCardEntity;
        $this->queue = $queue;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isActive()) {
            return;
        }

        $expireDate = $this->config->getExpireDate();
        if (!$expireDate) {
            return;
        }

        $expiredGiftCardQuoteIds = $this->abstractGiftCardEntityResource->getExpiredEntityQuoteIds($expireDate);

        if (empty($expiredGiftCardQuoteIds)) {
            return;
        }

        /** @var $orders \Magento\Sales\Model\ResourceModel\Order\Collection */
        $orders = $this->orderCollectionFactory->create();
        $orders->addFieldToFilter(
            OrderInterface::STATE,
            ['nin' => [Order::STATE_PROCESSING, Order::STATE_COMPLETE, Order::STATE_CANCELED, Order::STATE_CLOSED]]
        );
        $orders->addFieldToFilter(Giftcardaccount::BASE_GIFT_CARDS_AMOUNT, ['gt' => 0]);
        $orders->addFieldToFilter(OrderInterface::QUOTE_ID, ['in' => $expiredGiftCardQuoteIds]);

        $isQueued = [];
        foreach ($orders as $order) {
            $this->pushToQueue($order);
            $isQueued[] = $order->getQuoteId();
        }
        if (!empty($isQueued)) {
            $this->abstractGiftCardEntityResource->updateEntityQuoteData(
                ['is_queued' => 1],
                [
                    'quote_id IN (?)' => implode(',', $isQueued),
                    'start_date < ?' => $expireDate
                ]
            );
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function pushToQueue(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->queue->createQueueItem(
            CancellationProcess::PROCESS_CODE,
            QueueHelper::getSerializedProcessData(
                ['order' => $order->getEntityId()]
            ),
            self::class
        );
    }
}
