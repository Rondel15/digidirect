<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote as QuoteOriginal;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\Model\Cart as CartPlugin;
use Digidirect\ExtendedCartPriceRules\Plugin\Magento\Wishlist\Model\Item as WishlistItemPlugin;

/**
 * Class Quote
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model
 */
class Quote extends AbstractRestrictAddToCart
{
    const QTY_BEFORE_PRODUCT_UPDATED = 'qty_before_product_updated';

    /**
     * @var CartPlugin
     */
    protected $cartPlugin;

    /**
     * @var WishlistItemPlugin
     */
    protected $wishlistItemPlugin;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var QuoteOriginal
     */
    protected $guestQuote;

    /**
     * Quote constructor.
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @param MessageManagerInterface $messageManager
     * @param Combine $condition
     * @param CartPlugin $cartPlugin
     * @param WishlistItemPlugin $wishlistItemPlugin
     */
    public function __construct(
        ExtendedCartPriceRule $extendedCartPriceRule,
        MessageManagerInterface $messageManager,
        Combine $condition,
        CartPlugin $cartPlugin,
        WishlistItemPlugin $wishlistItemPlugin
    ) {
        parent::__construct($extendedCartPriceRule, $messageManager, $condition);
        $this->cartPlugin = $cartPlugin;
        $this->wishlistItemPlugin = $wishlistItemPlugin;
    }

    /**
     * @param QuoteOriginal $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddProduct(
        QuoteOriginal $subject,
        \Magento\Catalog\Model\Product $product,
        $request
    ) {
        $this->product = $product;
        return [$product, $request];
    }

    /**
     * @param QuoteOriginal $subject
     * @param CartItemInterface|string $result
     * @return CartItemInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterAddProduct(
        QuoteOriginal $subject,
        $result
    ) {
        if (!$result instanceof CartItemInterface || !$this->product) {
            return $result;
        }
        $beforeUpdateItemsInfo = $this->cartPlugin->getBeforeUpdateItemsInfo();
        if (!empty($beforeUpdateItemsInfo)) {
            foreach ($subject->getAllVisibleItems() as $item) {
                if ($item->getProduct()->getId() == $this->product->getId()
                    && array_key_exists($item->getId(), $beforeUpdateItemsInfo)
                    && $beforeUpdateItemsInfo[$item->getId()]['updated']) {
                    $this->product->setData(
                        self::QTY_BEFORE_PRODUCT_UPDATED,
                        $beforeUpdateItemsInfo[$item->getId()]['qty']
                    );
                    if ($updatedProducts = $subject->getUpdatedProducts()) {
                        $updatedProducts[] = $this->product;
                    } else {
                        $updatedProducts = [$this->product];
                    }
                    $subject->setUpdatedProducts($updatedProducts);
                }
            }
            return $result;
        }

        $wishlistItemToAdd = $this->wishlistItemPlugin->getWishlistItemToAdd();
        if ($wishlistItemToAdd && $wishlistItemToAdd->getProduct()->getId() == $this->product->getId()) {
            if ($wishlistItemsToAdd = $subject->getWishlistItemsToAdd()) {
                $wishlistItemsToAdd[] = $wishlistItemToAdd;
            } else {
                $wishlistItemsToAdd = [$wishlistItemToAdd];
            }
            $subject->setWishlistItemsToAdd($wishlistItemsToAdd);
            return $result;
        }

        $subject->setLastAddedProduct($this->product);
        return $result;
    }

    /**
     * @param QuoteOriginal $subject
     * @param QuoteOriginal $quote
     * @return array
     */
    public function beforeMerge(QuoteOriginal $subject, QuoteOriginal $quote)
    {
        $this->guestQuote = $quote;
        return [$quote];
    }

    /**
     * @param QuoteOriginal $subject
     * @param QuoteOriginal $result
     * @return array|QuoteOriginal
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMerge(QuoteOriginal $subject, QuoteOriginal $result)
    {
        $quote = $this->guestQuote;
        $result->collectTotals();
        $restrict = $this->extendedCartPriceRule->getRestrictAddToCartErrorMessages($result);
        if (empty($restrict)) {
            return $result;
        }

        $added = $quote->getAllVisibleItems();
        foreach ($added as $addedItem) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($result->getAllItems() as $item) {
                if ($item->compare($addedItem)) {
                    $result->deleteItem($item);
                    $result->getBillingAddress()->unsetData('cached_items_all');
                    $result->getShippingAddress()->unsetData('cached_items_all');
                }
            }
        }
        $result->setTotalsCollectedFlag(false);
        foreach ($result->getAllAddresses() as $address) {
            $address->setAppliedRuleIds('');
            $address->isObjectNew(true);
        }

        $result->setAppliedRuleIds('');
        $result->collectTotals();
        $result->setHasError(true);
        $this->addWarningMessage(
            __('We can\'t merge your cart with previously added product due to Cart Rules restriction.')
        );
        $this->addWarningMessages($restrict);
        return $result;
    }
}
