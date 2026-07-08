<?php

namespace Digidirect\CheckoutFields\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class QuoteFieldValue
 * @package Digidirect\CheckoutFields\Model\ResourceModel
 */
class QuoteFieldValue extends AbstractDb
{
    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_checkout_fields_quote_field_value', 'entity_id');
    }

    /**
     * Insert fields values into table
     * @param array $dataToSave
     * @return int
     */
    public function saveCustomFieldsValuesToQuote(array $dataToSave)
    {
        $adapter = $this->getConnection();
        return $adapter->insertOnDuplicate($this->getMainTable(), $dataToSave, ['value']);
    }
}
