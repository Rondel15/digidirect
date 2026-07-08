<?php

namespace Digidirect\FreeGift\Model;

use Digidirect\FreeGift\Model\Cart\Item;
use Digidirect\FreeGift\Api\RuleRepositoryInterface as FreeGiftRuleRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Digidirect\FreeGift\Helper\Data as DataHelper;
use Digidirect\FreeGift\Helper\GiftCard as GiftCardHelper;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * @var FreeGiftRuleRepositoryInterface
     */
    protected $_freeGiftRuleRepository;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Digidirect\FreeGift\Helper\Messages
     */
    protected $_giftMessagesHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var null
     */
    protected $_freeProductsCache = null;

    /**
     * @var null
     */
    protected $_freeGiftRulesCache = null;

    /**
     * @var array
     */
    protected $_allowedTypes = [
        ProductType::TYPE_SIMPLE,
        ProductType::TYPE_VIRTUAL,
        ProductType::TYPE_BUNDLE,
        Configurable::TYPE_CODE,
    ];

    /**
     * @var Item
     */
    protected $_giftItem;

    /**
     * @var \Digidirect\FreeGift\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel
     */
    protected $_toModelConverter;

    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * @var GiftCardHelper
     */
    protected $_giftCardHelper;

    /**
     * Serializer interface instance.
     *
     * @var SerializerJson
     */
    protected $_serializer;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart $resourceCart
     * @param Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Digidirect\FreeGift\Model\Registry $giftRegistry
     * @param \Digidirect\FreeGift\Helper\Messages $giftMessagesHelper
     * @param Item $giftItem
     * @param \Digidirect\FreeGift\Helper\Config $configHelper
     * @param FreeGiftRuleRepositoryInterface $freeGiftRuleRepository
     * @param RuleRepositoryInterface $ruleRepository
     * @param \Magento\SalesRule\Model\Converter\ToModel $toModelConverter
     * @param DataHelper $dataHelper
     * @param GiftCardHelper $giftCardHelper
     * @param SerializerJson $serializer
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Digidirect\FreeGift\Model\Registry $giftRegistry,
        \Digidirect\FreeGift\Helper\Messages $giftMessagesHelper,
        Item $giftItem,
        \Digidirect\FreeGift\Helper\Config $configHelper,
        FreeGiftRuleRepositoryInterface $freeGiftRuleRepository,
        RuleRepositoryInterface $ruleRepository,
        \Magento\SalesRule\Model\Converter\ToModel $toModelConverter,
        DataHelper $dataHelper,
        GiftCardHelper $giftCardHelper,
        SerializerJson $serializer,
        array $data = []
    ) {
        $this->_giftRegistry = $giftRegistry;
        $this->_giftMessagesHelper = $giftMessagesHelper;
        $this->_productFactory = $productFactory;
        $this->_giftItem = $giftItem;
        $this->_configHelper = $configHelper;
        $this->_ruleRepository = $ruleRepository;
        $this->_freeGiftRuleRepository = $freeGiftRuleRepository;
        $this->_toModelConverter = $toModelConverter;
        $this->_dataHelper = $dataHelper;
        $this->_giftCardHelper = $giftCardHelper;
        $this->_serializer = $serializer;
        parent::__construct(
            $eventManager,
            $scopeConfig,
            $storeManager,
            $resourceCart,
            $checkoutSession,
            $customerSession,
            $messageManager,
            $stockRegistry,
            $stockState,
            $quoteRepository,
            $productRepository,
            $data
        );
    }

    /**
     * @return array
     */
    public function getAllowedProductTypes()
    {
        return $this->_allowedTypes;
    }

    /**
     * @param Product $product
     * @param int $qty
     * @param bool $ruleId
     * @param array $requestParams
     * @param bool $showMessage
     * @param bool $isHiddenForCustomer
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addFreeGiftToCart(
        Product $product,
        $qty = null,
        $ruleId = false,
        $requestParams = [],
        $showMessage = true,
        $isHiddenForCustomer = false
    ) {
        $availableQty = $this->checkAvailableQty($product, $qty);
        if ($availableQty <= 0) {
            if (!$isHiddenForCustomer) {
                $this->_giftMessagesHelper->addAvailabilityError($product);
            }
            return false;
        } else {
            if (!$isHiddenForCustomer && $availableQty < $qty) {
                $this->_giftMessagesHelper->showMessage(__(
                    "We apologize, but requested quantity of free gift <%1> is not available at the moment",
                    $product->getName()
                ), false, true);
                return false;
            }
        }

        $qty = $availableQty;
        $requestInfo = [
            'qty' => $qty,
            'options' => []
        ];

        if (!empty($requestParams)) {
            $requestInfo = array_merge_recursive($requestParams, $requestInfo);
        }

        $requestInfo['options'][Item::FREE_GIFT_KEY] = $ruleId;
        if ($isHiddenForCustomer) {
            $requestInfo['options'][Item::FREE_GIFT_IS_HIDDEN_FOR_CUSTOMER] = 1;
        }

        try {
            $product->setData(Item::FREE_GIFT_KEY, $ruleId);
            $product->setData(Item::FREE_GIFT_IS_HIDDEN_FOR_CUSTOMER, $isHiddenForCustomer);
            $this->addAdditionalFreeGiftAttributes($product);

            $this->addProduct($product, $requestInfo);
            $this->_giftRegistry->restore($product->getData('sku'));
            if (!$isHiddenForCustomer && $showMessage) {
                $this->_giftMessagesHelper->showMessage(__(
                    "Free gift <%1> was added to your shopping cart",
                    $product->getName()
                ), false, true);
            }
        } catch (\Exception $e) {
            if (!$isHiddenForCustomer) {
                $this->_giftMessagesHelper->showMessage($e->getMessage(), true, true);
            }
        }

        return true;
    }

    /**
     * Add Free-gift product's attributes as Custom options to display on checkout&cart.
     *
     * @param Product $product
     * @return void
     */
    public function addAdditionalFreeGiftAttributes(Product $product)
    {
        if ($this->_configHelper->getShowFreeItemAttributes() &&
            $freeGiftAdditionalAttributes = $this->_configHelper->getFreeItemAttributesArray()
        ) {
            $options = [];
            foreach ($freeGiftAdditionalAttributes as $attribute) {
                if ($product->hasData($attribute)) {
                    $value = $product->getData($attribute);
                    if (is_array($value)) {
                        $value = implode(";", array_filter($value, function ($v) {
                            return !is_array($v);
                        }));
                    }

                    $options[] = [
                        'label' => $attribute,  // can be empty
                        'value' => $value,
                    ];
                }
            }

            if (!empty($options)) {
                $product->addCustomOption(
                    'additional_options',
                    $this->_serializer->serialize($options)
                );
            }
        }
    }

    /**
     * @param Product $product
     * @param int $qtyRequested
     * @return mixed
     */
    public function checkAvailableQty(
        Product $product,
        $qtyRequested
    ) {
        if ($product->getTypeId() != Product\Type::TYPE_SIMPLE) {
            return $qtyRequested;
        }

        $stockStatus = $this->stockRegistry->getStockStatus(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );

        $stockItem = $stockStatus->getStockItem();
        if (!$stockItem->getManageStock()) {
            return $qtyRequested;
        }

        $qtyAdded = 0;
        foreach ($this->getItems() as $item) {
            if ($item->getProductId() == $product->getId()) {
                $qtyAdded += $item->getQty();
            }
        }

        $qty = $stockStatus->getQty() - $qtyAdded;
        return min($qty, $qtyRequested);
    }

    /**
     * @return bool|\Magento\Framework\Data\Collection\AbstractDb
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getNewFreeGiftItems()
    {
        if (!$this->getQuote()->getId()) {
            return [];
        }

        if ($this->_freeProductsCache === null) {
            $items = $this->_giftRegistry->getLimits();

            $groups = $items['_groups'];
            unset($items['_groups']);

            if (!$items && !$groups) {
                $this->_freeProductsCache = [];
                return [];
            }

            $allowedSku = array_keys($items);
            foreach ($groups as $rule) {
                $allowedSku = array_merge($allowedSku, $rule['sku']);
            }

            $products = $this->_productFactory->create()->getCollection()
                ->addAttributeToSelect([
                    'name',
                    'small_image',
                    'status',
                    'visibility',
                    'price'
                ])
                ->addFieldToFilter('sku', ['in' => $allowedSku]);

            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), $this->getAllowedProductTypes())) {
                    $this->_giftMessagesHelper->showMessage(__(
                        "We apologize, but products of type <%1> are not supported",
                        $product->getTypeId()
                    ));
                    $products->removeItemByKey($key);
                }

                if (!$product->isInStock() || !$product->isSalable()
                    || !$this->checkAvailableQty($product, 1)
                ) {
                    $this->_giftMessagesHelper->addAvailabilityError($product);
                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }
            }

            if ($products->getSize()) {
                $this->_giftMessagesHelper->clearFreeGiftMessages();
                $this->_freeProductsCache = $products;
            } else {
                $this->_freeProductsCache = [];
            }
        }

        return $this->_freeProductsCache;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getNewFreeGiftRules()
    {
        $rules = [];
        $freeProducts = $this->getNewFreeGiftItems();
        if (empty($freeProducts)) {
            return $rules;
        }

        if ($this->_freeGiftRulesCache === null) {
            $items = $this->_giftRegistry->getLimits();
            $groups = $items['_groups'];
            unset($items['_groups']);

            if (!$items && !$groups) {
                $this->_freeGiftRulesCache = [];
                return $rules;
            }

            foreach ($groups as $ruleId => $group) {
                $rules[$ruleId] = $group['sku'];
            }

            foreach ($items as $sku => $item) {
                $rules[$item['rule_id']][] = $sku;
            }

            foreach ($rules as $ruleId => &$rule) {
                $products = array_unique($rule);
                /** @var \Magento\SalesRule\Model\Data\Rule $salesRuleData */
                $salesRuleData = $this->_ruleRepository->getById($ruleId);
                /** @var \Magento\SalesRule\Model\Rule $salesRule */
                $salesRule = $this->_toModelConverter->toModel($salesRuleData);
                $freeGift = $this->_freeGiftRuleRepository->loadBySalesrule($salesRule);

                $rule = [
                    'sales_rule' => $salesRule,
                    'free_gift' => $freeGift,
                    'products' => [],
                ];

                foreach ($products as $product) {
                    foreach ($freeProducts as $freeProduct) {
                        /** @var Product $freeProduct */
                        if ($freeProduct->getSku() == $product) {
                            $rule['products'][] = $freeProduct;
                        }
                    }
                }
                $rule = new DataObject($rule);
            }
            $this->_freeGiftRulesCache = $rules;
        }
        return $this->_freeGiftRulesCache;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectTotals(\Magento\Quote\Model\Quote $quote)
    {
        $this->setQuote($quote);
        $this->clearQuoteFreeGifts();
        $this->addFreeGiftsToQuote();
        $this->updateQuoteTotalQty();
    }

    /**
     * Add Free Gift items to quote
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function addFreeGiftsToQuote()
    {
        $quote = $this->getQuote();
        $addAutomatically = $this->_configHelper->isAddProductsAutomatically();
        $toAdd = $this->_giftRegistry->getFreeGiftItems();
        unset($toAdd['_groups']);

        foreach ($quote->getAllItems() as $item) {
            $sku = $item->getProduct()->getData('sku');
            if (!isset($toAdd[$sku])) {
                continue;
            }

            if ($this->_giftItem->isFreeGiftItem($item)) {
                if ($addAutomatically || !empty($toAdd[$sku]['is_hidden_for_customer'])) {
                    $toAdd[$sku]['qty'] -= $item->getQty();
                }
            }
        }

        $deleted = $this->_giftRegistry->getDeletedItems();
        $collectorData = [];

        foreach ($toAdd as $sku => $item) {
            $add = $addAutomatically || !empty($item['is_hidden_for_customer']);
            if (!$add) {
                continue;
            }
            if ($item['qty'] > 0 && $item['auto_add'] && !isset($deleted[$sku])) {
                $product = $this->_productFactory->create()->loadByAttribute('sku', $sku);
                if ($product) {
                    if (isset($collectorData[$product->getId()])) {
                        $collectorData[$product->getId()]['qty'] += $item['qty'];
                    } else {
                        $product->setData(Item::FREE_GIFT_ADDED_BY_RULE_ID, $item['rule_id']);
                        $collectorData[$product->getId()] = [
                            'product' => $product,
                            'qty' => $item['qty'],
                            'is_hidden_for_customer' => !empty($item['is_hidden_for_customer'])
                        ];
                    }
                }
            }
        }

        foreach ($collectorData as $item) {
            $this->addFreeGiftToCart(
                $item['product'],
                $item['qty'],
                false,
                [],
                empty($item['is_hidden_for_customer']),
                !empty($item['is_hidden_for_customer'])
            );
        }
    }

    /**
     * Clear invalid Free Gift items from quote
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function clearQuoteFreeGifts()
    {
        /**
         * @var $item \Magento\Quote\Model\Quote\Item
         */
        $quote = $this->getQuote();
        $allowedItems = $this->_giftRegistry->getFreeGiftItems();
        foreach ($quote->getAllItems() as $item) {
            if ($this->_giftItem->isFreeGiftItem($item)) {
                if ($item->getParentItem()) {
                    continue;
                }

                $product = $item->getProduct();
                $sku = $product->getData('sku');
                $ruleId = $this->_giftItem->getRuleId($item);
                if (isset($allowedItems['_groups'][$ruleId])) {
                    if ($allowedItems['_groups'][$ruleId]['qty'] <= 0) {
                        $this->removeItem($item->getId());
                    } else {
                        if ($item->getQty() > $allowedItems['_groups'][$ruleId]['qty']) {
                            $item->setQty($allowedItems['_groups'][$ruleId]['qty']);
                        }
                    }

                    $allowedItems['_groups'][$ruleId]['qty'] -= $item->getQty();
                } else {
                    if (isset($allowedItems[$sku])) {
                        if ($allowedItems[$sku]['qty'] <= 0) {
                            $this->removeItem($item->getId());
                        } else {
                            if ($item->getQty() > $allowedItems[$sku]['qty']) {
                                $item->setQty($allowedItems[$sku]['qty']);
                            }
                        }

                        $allowedItems[$sku]['qty'] -= $item->getQty();
                    } else {
                        $this->removeItem($item->getId());
                    }
                }
                // giftcard: remove auto added giftcard if there is no configured amounts or set minimal amount
                if (!$item->isDeleted() && $ruleId === false && $product->getTypeId() == 'giftcard') {
                    $amount = $this->_giftCardHelper->getFreeGiftGiftcardAmount($product);
                    if ($amount > 0) {
                        $this->_giftCardHelper->setGiftCardOptionsToQuoteItem($item, ['giftcard_amount' => $amount]);
                    } else {
                        $this->removeItem($item->getId());
                    }
                }
            }
        }
    }

    /**
     * Update quote Total Qty before saving
     *
     * @return void
     */
    protected function updateQuoteTotalQty()
    {
        $quote = $this->getQuote();
        $quote->setItemsCount(0);
        $quote->setItemsQty(0);
        $quote->setVirtualItemsQty(0);

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($this->_dataHelper->isHiddenForCustomerGiftItem($item)) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $qty = $quote->getVirtualItemsQty() + $child->getQty() * $item->getQty();
                        $quote->setVirtualItemsQty($qty);
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $item->getQty());
            }

            $quote->setItemsCount($quote->getItemsCount() + 1);
            $quote->setItemsQty((int)$quote->getItemsQty() + $item->getQty());
        }
    }
}
