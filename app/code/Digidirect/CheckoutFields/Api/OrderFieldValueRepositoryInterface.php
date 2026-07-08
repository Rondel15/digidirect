<?php

namespace Digidirect\CheckoutFields\Api;

use Digidirect\CheckoutFields\Api\Data\OrderFieldValueInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderFieldValueRepositoryInterface
 *
 * @package Digidirect\CheckoutFields\Api
 */
interface OrderFieldValueRepositoryInterface
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
     * Get values by Order ID
     *
     * @param int $orderId
     *
     * @return OrderFieldValueInterface[]
     */
    public function getListByOrderId($orderId, bool $fromCache = self::LOAD_FROM_CACHE);

    /**
     * Move quote custom fields to order
     *
     * @param CartInterface  $quote
     * @param OrderInterface $order
     *
     * @return bool
     */
    public function moveCheckoutFieldsToOrderFromQuote(CartInterface $quote, OrderInterface $order);
}
