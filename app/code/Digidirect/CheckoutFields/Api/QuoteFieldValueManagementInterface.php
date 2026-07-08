<?php

namespace Digidirect\CheckoutFields\Api;

use Digidirect\CheckoutFields\Api\Data\QuoteFieldValueInterface;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface QuoteFieldValueRepositoryInterface
 *
 * @package Digidirect\CheckoutFields\Api
 */
interface QuoteFieldValueManagementInterface
{
    /**
     * @param CartInterface $quote
     * @param bool          $reSave
     *
     * @return mixed
     */
    public function saveToQuoteFromExtensionAttributes(CartInterface $quote, $reSave = true);

    /**
     * @param CartInterface  $quote
     * @param OrderInterface $order
     * @param bool           $reSave
     *
     * @return mixed
     */
    public function saveToQuoteFromOrder(CartInterface $quote, OrderInterface $order , $reSave = true);

    /**
     * @param CartInterface $quote
     *
     * @return null|array|QuoteFieldValueInterface[]
     */
    public function getCustomFieldsFromExtensionAttributes(CartInterface $quote);

    /**
     * @param CartInterface $quote
     *
     * @return null|CartExtension|CartExtensionInterface
     */
    public function getQuoteExtension(CartInterface $quote);
}
