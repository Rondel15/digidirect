<?php

namespace Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue
 */
class Collection extends AbstractCollection
{
    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Digidirect\CheckoutFields\Model\PdpFieldValue::class,
            \Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue::class
        );
    }
}
