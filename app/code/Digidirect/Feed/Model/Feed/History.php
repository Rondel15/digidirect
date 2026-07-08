<?php

namespace Digidirect\Feed\Model\Feed;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * @method int getFeedId()
 * @method $this setFeedId($id)
 * @method string getTitle()
 * @method $this setTitle($title)
 * @method string getMessage()
 * @method $this setMessage($message)
 * @method string getType()
 * @method $this setType($type)
 */
class History extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_feed_history';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'history';

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * History constructor.
     * @param Context $context
     * @param Registry $registry
     * @param HistoryFactory $historyFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HistoryFactory $historyFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->historyFactory = $historyFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\ResourceModel\Feed\History');
    }

    /**
     * @param \Digidirect\Feed\Model\Feed $feed
     * @param string $title
     * @param string $message
     *
     * @return $this
     */
    public function add($feed, $title, $message)
    {
        /** @var History $history */
        $history = $this->historyFactory->create();
        $history->setFeedId($feed->getId())
            ->setTitle($title)
            ->setMessage($message)
            ->setType(php_sapi_name() == 'cli' ? 'CLI Mode' : 'Manual');

        $history->getResource()->save($history);

        return $this;
    }
}
