<?php

namespace Digidirect\FreeGift\Model\Rule\Action\Discount;

use Digidirect\FreeGift\Api\RuleRepositoryInterface;
use Digidirect\FreeGift\Api\Data\RuleInterface;

abstract class AbstractDiscount extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_giftItem;

    /**
     * @var \Digidirect\FreeGift\Model\Cart
     */
    protected $_giftCart;

    /**
     * AbstractDiscount constructor.
     *
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param RuleRepositoryInterface $ruleRepository
     * @param \Digidirect\FreeGift\Model\Cart $giftCart
     * @param \Digidirect\FreeGift\Model\Cart\Item $giftItem
     * @param \Digidirect\FreeGift\Model\Registry $giftRegistry
     */
    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        RuleRepositoryInterface $ruleRepository,
        \Digidirect\FreeGift\Model\Cart $giftCart,
        \Digidirect\FreeGift\Model\Cart\Item $giftItem,
        \Digidirect\FreeGift\Model\Registry $giftRegistry
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->_ruleRepository = $ruleRepository;
        $this->_giftCart = $giftCart;
        $this->_giftItem = $giftItem;
        $this->_giftRegistry = $giftRegistry;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $this->_addFreeItems($rule, $item, $qty);
        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $qty
     * @return bool
     */
    protected function _addFreeItems(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item $item,
        $qty
    ) {
        if (!$this->_giftRegistry->getApplyAttempt($rule->getId())) {
            return false;
        }

        $freeGiftRule = $this->_ruleRepository->loadBySalesrule($rule);
        $giftSku = $freeGiftRule->getSkuArray();
        if (!$freeGiftRule->getId() || empty($giftSku)) {
            return false;
        }

        $quote = $item->getQuote();
        $qty = $this->_getFreeItemsQty($rule, $quote);
        if (!$qty) {
            return false;
        }

        $isHiddenForCustomer = $this->_isFreeGiftHidden($freeGiftRule);
        $isTypeOneOf = $freeGiftRule->getType() == RuleInterface::RULE_TYPE_ONE;
        $this->_giftRegistry->addFreeGiftItem($giftSku, $qty, $rule->getId(), $isTypeOneOf, $isHiddenForCustomer);

        return true;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote $quote
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote $quote
    ) {
        $qty = max(1, $rule->getDiscountAmount());
        return $qty;
    }

    /**
     * @param RuleInterface $freeGiftRule
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isFreeGiftHidden(RuleInterface $freeGiftRule)
    {
        return false;
    }
}
