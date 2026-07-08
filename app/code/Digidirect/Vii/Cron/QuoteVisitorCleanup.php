<?php


namespace Digidirect\Vii\Cron;

use Digidirect\Vii\Service\Config\Config;
use Digidirect\AI\Helper\Queue as QueueHelper;
use Digidirect\Vii\Model\Processor\Quote\VisitorCleanupProcess;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoresConfig;
use Magento\Quote\Api\Data\CartInterface;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Digidirect\Vii\Model\ResourceModel\Customer\Visitor;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class QuoteVisitorCleanup
 * @package Digidirect\Vii\Cron
 */
class QuoteVisitorCleanup
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var QueueHelper
     */
    protected $queue;

    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * QuoteVisitorCleanup constructor.
     * @param Config $config
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @param QueueHelper $queue
     * @param StoresConfig $storesConfig
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        Config $config,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory,
        QueueHelper $queue,
        StoresConfig $storesConfig,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->config = $config;
        $this->quoteCollectionFactory = $collectionFactory;
        $this->queue = $queue;
        $this->storesConfig = $storesConfig;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Clean expired guest quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isActive()) {
            return;
        }

        $lifetimes = $this->storesConfig->getStoresConfigByPath(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME
        );
        foreach ($lifetimes as $storeId => $lifetime) {
            if (!$lifetime) {
                continue;
            }

            $dateTime = $this->dateTimeFactory->create();
            $activeSessionsTime = $dateTime->setTimestamp($dateTime->getTimestamp() - $lifetime)
                ->format(DateTime::DATETIME_PHP_FORMAT);

            /** @var $quotes \Magento\Quote\Model\ResourceModel\Quote\Collection */
            $quotes = $this->quoteCollectionFactory->create();
            $quotes->addFieldToFilter(CartInterface::KEY_STORE_ID, $storeId);
            $quotes->addFieldToFilter(CartInterface::KEY_IS_ACTIVE, 1);
            $quotes->addFieldToFilter(Giftcardaccount::BASE_GIFT_CARDS_AMOUNT, ['gt' => 0]);

            $quotes->getSelect()
                ->join(
                    ['qv' => Visitor::ABSTRACT_GIFT_CARD_QUOTE_VISITOR_TABLE],
                    'main_table.entity_id = qv.quote_id',
                    ['visitor_id']
                )
                ->join(
                    ['vt' => 'customer_visitor'],
                    'qv.visitor_id = vt.visitor_id',
                    ['last_visit_at']
                )
                ->where('vt.last_visit_at < ?', $activeSessionsTime);

            foreach ($quotes as $quote) {
                $this->pushToQueue($quote);
            }
        }
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function pushToQueue(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $this->queue->createQueueItem(
            VisitorCleanupProcess::PROCESS_CODE,
            QueueHelper::getSerializedProcessData(
                ['quote' => $quote->getEntityId()]
            ),
            self::class
        );
    }
}
