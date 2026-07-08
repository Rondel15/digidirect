<?php

namespace Digidirect\FreeGift\Model\Cart;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Area;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class Item
 * @package Digidirect\FreeGift\Model\Cart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Item extends QuoteItem
{
    const FREE_GIFT_KEY = 'freegift_rule_id';
    const FREE_GIFT_IS_HIDDEN_FOR_CUSTOMER = 'freegift_is_hidden_for_customer';
    const FREE_GIFT_ADDED_BY_RULE_ID = 'added_by_rule_id';

    /**
     * @var \Digidirect\FreeGift\Model\ResourceModel\Rule
     */
    protected $_resourceFreeGift;

    /**
     * Item constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory
     * @param \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Digidirect\FreeGift\Model\ResourceModel\Rule $resourceFreeGift
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Digidirect\FreeGift\Model\ResourceModel\Rule $resourceFreeGift,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_resourceFreeGift = $resourceFreeGift;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $statusListFactory,
            $localeFormat,
            $itemOptionFactory,
            $quoteItemCompare,
            $stockRegistry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param QuoteItem|\Magento\Sales\Model\Order\Item $item
     * @return mixed|null
     */
    public function getRuleId($item)
    {
        if (!($item instanceof \Magento\Quote\Model\Quote\Item ||
            $item instanceof \Magento\Sales\Model\Order\Item)) {
            throw new \InvalidArgumentException(__('Can\'t check Free-gift rule for a given item.'));
        }   // @todo

        if (!($ruleId = $item->getData(self::FREE_GIFT_KEY))) {
            $buyRequest = $item->getBuyRequest();
            $ruleId = isset($buyRequest['options'][self::FREE_GIFT_KEY])
                ? $buyRequest['options'][self::FREE_GIFT_KEY] : null;

            $item->setData(self::FREE_GIFT_KEY, $ruleId);
        }
        return $ruleId;
    }

    /**
     * @param QuoteItem|\Magento\Sales\Model\Order\Item $item
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFreeGiftItem($item)
    {
        if (in_array($this->_appState->getAreaCode(), [Area::AREA_ADMIN, Area::AREA_ADMINHTML])) {
            return false;
        }
        return $this->getRuleId($item) !== null;
    }

    /**
     * @param QuoteItem $item
     * @return bool
     */
    public function isHiddenForCustomerGiftItem(QuoteItem $item)
    {
        $data = $item->getBuyRequest();
        return !empty($data['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_IS_HIDDEN_FOR_CUSTOMER]);
    }

    /**
     * @param QuoteItem $item
     * @param bool $sortByPriority
     * @param bool $withHidden
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCartItemMessage(QuoteItem $item, $sortByPriority = false, $withHidden = true)
    {
        $message = null;
        if (!$ruleId = $item->getData(Item::FREE_GIFT_ADDED_BY_RULE_ID)) {
            return $message;
        }

        $messages = $this->_resourceFreeGift->getMessagesByRuleIds(
            [$ruleId],
            $sortByPriority,
            $withHidden
        );
        foreach ($messages as $messagesItem) {
            if (!empty($messagesItem['cart_message'])) {
                $message = $messagesItem['cart_message'];
                break;
            }
        }

        return $message;
    }

    /**
     * @param OrderItem $item
     * @param bool $sortByPriority
     * @param bool $withHidden
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderItemPrefix(OrderItem $item, $sortByPriority = false, $withHidden = true)
    {
        $prefix = null;
        if ($item->getAppliedRuleIds()) {
            $prefixes = $this->_resourceFreeGift->getPrefixesByRuleIds(
                $item->getAppliedRuleIds(),
                $sortByPriority,
                $withHidden
            );
            foreach ($prefixes as $prefixItem) {
                if (!empty($prefixItem['prefix'])) {
                    $prefix = $prefixItem['prefix'];
                    break;
                }
            }
        }

        return $prefix;
    }
}
