<?php

namespace Digidirect\Feed\Model\ResourceModel\Template;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_template_collection';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'collection';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\Template', 'Digidirect\Feed\Model\ResourceModel\Template');
    }
}
