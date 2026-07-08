<?php
namespace Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Digidirect\CheckoutFields\Model\OrderFieldValue',
            'Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule'
        );
    }
}
