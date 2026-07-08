<?php

namespace Digidirect\Collect\Model;

use \Magento\CatalogInventory\Helper\Data as CatalogHelper;

class CollectQuantityValidator
{
    const OUT_OF_STOCK_ITEM_MESSAGE = 'This product is out of stock.';
    const OUT_OF_STOCK_QUOTE_MESSAGE = 'Some of the products are out of stock.';
    const NOT_ENOUGHT_QTY_ITEM_MESSAGE = 'Not enough quantity for this item';
    const NOT_ENOUGHT_QTY_QUOTE_MESSAGE = 'Some of the products don\'t have enough quantity.';

    /**
     * @var \Digidirect\Collect\Helper\Config\Data
     */
    protected $_collectConfigHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * CollectQuantityValidator constructor.
     *
     * @param \Digidirect\Collect\Helper\Config\Data $collectConfigHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Digidirect\Collect\Helper\Config\Data $collectConfigHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_collectConfigHelper = $collectConfigHelper;
        $this->_request = $request;
    }

    /**
     * Check product qty in source
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $collectPlaceId
     * @return bool
     */
    public function checkProductQtyInSource(\Magento\Quote\Model\Quote\Item $quoteItem, $collectPlaceId)
    {
        $source = $this->_collectConfigHelper->getSourceByStorage($quoteItem);
        if ($source) {
            /** @var $item \Magento\Quote\Model\Quote\Item */
            $productQty = $source->getProductQty($quoteItem->getProduct()->getSku(), $collectPlaceId);

            $itemMessage = '';
            $quoteMessage = '';
            $hasError = false;
            if ($productQty < 1) {
                $hasError = true;
                $itemMessage = __($this->getItemMessage());
                $quoteMessage = __($this->getQuoteMessage());
            } elseif (!$this->validateSourceQty($productQty, $quoteItem)) {
                $hasError = true;
                $itemMessage = __($this->getItemMessage(false));
                $quoteMessage = __($this->getQuoteMessage(false));
            }

            if ($hasError) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    CatalogHelper::ERROR_QTY,
                    $itemMessage
                );
                $quoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    CatalogHelper::ERROR_QTY,
                    $quoteMessage
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Quote validate
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool|string
     */
    public function isQuoteProductsQtyValid(\Magento\Quote\Model\Quote $quote)
    {
        $quoteItems = $quote->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            if ($collectPlaceId = $quoteItem->getCollectPlaceId()) {
                $checkQty = $this->checkProductQtyInSource($quoteItem, $collectPlaceId);
                if (!$checkQty) {
                    return $quoteItem->getSku();
                }
            }
        }

        return true;
    }

    /**
     * getItemMessage
     *
     * @param bool $outOfStock
     * @return string
     */
    protected function getItemMessage($outOfStock = true)
    {
        $message = self::OUT_OF_STOCK_ITEM_MESSAGE;
        if (!$outOfStock) {
            $message = self::NOT_ENOUGHT_QTY_ITEM_MESSAGE;
        }

        return $message;
    }

    /**
     * getQuoteMessage
     *
     * @param bool $outOfStock
     * @return string
     */
    protected function getQuoteMessage($outOfStock = true)
    {
        $message = self::OUT_OF_STOCK_QUOTE_MESSAGE;
        if (!$outOfStock) {
            $message = self::NOT_ENOUGHT_QTY_QUOTE_MESSAGE;
        }

        return $message;
    }

    /**
     * ValidateSourceQty
     *
     * @param string $productQty
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function validateSourceQty($productQty, $quoteItem)
    {
        $actionName = $this->_request->getActionName();

        $qty = $quoteItem->getQty();
        if ($actionName == 'add') { //add to cart
            if ($this->_request->getParam('qty')) {
                $qty += $this->_request->getParam('qty');
            }
        } elseif ($actionName == 'updateItemQty') { //update qty in sidebar
            $itemId = $this->_request->getParam('item_id');
            if ($itemId == $quoteItem->getId()) {
                $qty = $this->_request->getParam('item_qty', 0);
            }
        } elseif ($actionName == 'updatePost' && $this->_request->getParam('update_cart_action') == 'update_qty') {
            $cart = $this->_request->getParam('cart');
            if (!empty($cart) && !empty($cart[$quoteItem->getId()]) && !empty($cart[$quoteItem->getId()]['qty'])) {
                $qty = $cart[$quoteItem->getId()]['qty'];
            }
        }

        if ($productQty < $qty) {
            return false;
        }

        return true;
    }
}
