<?php

namespace Digidirect\Vii\Model\ResourceModel\Customer;

/**
 * Class Visitor
 * @package Digidirect\Vii\Model\ResourceModel\Customer
 */
class Visitor extends \Magento\Customer\Model\ResourceModel\Visitor
{
    const ABSTRACT_GIFT_CARD_QUOTE_VISITOR_TABLE = 'digidirect_abstract_gift_card_visitor_quote';

    /**
     * @param \Magento\Framework\DataObject $dataObject
     * @return $this
     */
    public function saveVisitorData($dataObject)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getTable(self::ABSTRACT_GIFT_CARD_QUOTE_VISITOR_TABLE),
            [
                'quote_id'  => $dataObject->getQuoteId(),
                'visitor_id' => $dataObject->getVisitorId()
            ]
        );
        return $this;
    }

    /**
     * @param int $quoteId
     * @return $this
     */
    public function deleteVisitorDataByQuoteId($quoteId)
    {
        $this->getConnection()->delete(
            $this->getTable(self::ABSTRACT_GIFT_CARD_QUOTE_VISITOR_TABLE),
            ['quote_id =?' => $quoteId]
        );
        return $this;
    }
}
