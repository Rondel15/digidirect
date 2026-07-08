<?php

namespace Digidirect\Afterpay\Gateway\Request\Checkout;

use \Afterpay\Afterpay\Gateway\Request\Checkout\CheckoutDataBuilder as AfterpayClass;

class CheckoutDataBuilder extends AfterpayClass
{

    public function getItems(\Magento\Quote\Model\Quote $quote): array
    {
        $formattedItems = [];
        $quoteItems = $quote->getAllVisibleItems();
        $itemsImages = $this->getItemsImages($quoteItems);
        $isCBTCurrencyAvailable = $this->checkCBTCurrencyAvailability->checkByQuote($quote);

        foreach ($quoteItems as $item) {
            $productId = $item->getProduct()->getId();
            $amount = $isCBTCurrencyAvailable ? $item->getPriceInclTax() : $item->getBasePriceInclTax();
            $currencyCode = $isCBTCurrencyAvailable ? $quote->getQuoteCurrencyCode() : $quote->getBaseCurrencyCode();
            $qty = $item->getQty();
            $qty = (float)$qty;
            $isIntQty = floor($qty) == $qty;
            if ($isIntQty) {
                $qty = (int)$item->getQty();
            } else {
                $amount *= $item->getQty();
                $qty = 1;
            }

            $formattedItem = [
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'quantity' => $qty,
                'pageUrl' => $item->getProduct()->getProductUrl(),
                'categories' => [array_values($this->getQuoteItemCategoriesNames($item))],
                'price' => [
                    'amount' => $this->formatPrice($amount),
                    'currency' => $currencyCode
                ]
            ];

            if (isset($itemsImages[$productId]) && $image = $itemsImages[$productId]) {
                if ($imageUrl = $image->getUrl()) {
                    $formattedItem['imageUrl'] = $imageUrl;
                }
            }

            $formattedItems[] = $formattedItem;
        }
        return $formattedItems;
    }

}
