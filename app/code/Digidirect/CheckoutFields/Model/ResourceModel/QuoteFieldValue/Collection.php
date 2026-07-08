<?php

namespace Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;

/**
 * Class Collection
 * @package Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue
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
            'Digidirect\CheckoutFields\Model\QuoteFieldValue',
            'Digidirect\CheckoutFields\Model\ResourceModel\QuoteFieldValue'
        );
    }

    /**
     * @param array $fieldIds
     * @return Collection
     */
    public function filterByFieldIds($fieldIds)
    {
        return $this->addFieldToFilter(QuoteFieldValueInterface::FIELD_ID, ['in' => $fieldIds]);
    }
}
