<?php

namespace Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Digidirect\CheckoutFields\Api\Data\OrderFieldValueInterface;

/**
 * Class Collection
 * @package Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue
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
            'Digidirect\CheckoutFields\Model\OrderFieldValue',
            'Digidirect\CheckoutFields\Model\ResourceModel\OrderFieldValue'
        );
    }

    /**
     * @param array $fieldIds
     * @return Collection
     */
    public function filterByFieldIds($fieldIds)
    {
        return $this->addFieldToFilter(OrderFieldValueInterface::FIELD_ID, ['in' => $fieldIds]);
    }
}
