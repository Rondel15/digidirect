<?php

namespace Digidirect\CheckoutFields\Api;

use Digidirect\CheckoutFields\Model\PdpFieldValue;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface PdpFieldValueRepositoryInterface
 * @package Digidirect\CheckoutFields\Api
 */
interface PdpFieldValueRepositoryInterface
{
    /**
     * Get all rows
     *
     * @param SearchCriteriaInterface $criteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Get values by Quote ID
     *
     * @param int $quoteId
     * @param string $code
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getListByQuoteId($quoteId, $code);

    /**
     * @param PdpFieldValue $object
     * @return PdpFieldValue
     */
    public function save(PdpFieldValue $object);
}
