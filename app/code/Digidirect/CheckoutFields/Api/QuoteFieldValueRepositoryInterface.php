<?php

namespace Digidirect\CheckoutFields\Api;

use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;
use Digidirect\CheckoutFields\Model\QuoteFieldValue;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Interface QuoteFieldValueRepositoryInterface
 *
 * @package Digidirect\CheckoutFields\Api
 */
interface QuoteFieldValueRepositoryInterface
{
    const LOAD_FROM_CACHE = true;

    /**
     * Get all rows
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Get values by Quote ID
     *
     * @param int  $quoteId
     * @param bool $fromCache
     *
     * @return QuoteFieldValueInterface[]
     */
    public function getListByQuoteId($quoteId, bool $fromCache = self::LOAD_FROM_CACHE);

    /**
     * @param array $quoteFieldValues
     *
     * @return array
     * @throws CouldNotSaveException
     */
    public function saveAll(array $quoteFieldValues);

    /**
     * @param CartInterface $quote
     * @param array $params
     * @param bool $reSave
     * @return bool
     * @throws LocalizedException
     */
    public function saveToQuote(CartInterface $quote, $params = [], $reSave = true);

    /**
     * @param QuoteFieldValueInterface $quoteFieldValue
     *
     * @return bool
     */
    public function delete(QuoteFieldValueInterface $quoteFieldValue);

    /**
     * @param $quoteId
     * @param $fieldId
     *
     * @return ExtensibleDataInterface[]
     */
    public function getCustomField($quoteId, $fieldId);
}
