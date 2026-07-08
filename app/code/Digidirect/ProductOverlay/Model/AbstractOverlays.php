<?php

namespace Digidirect\ProductOverlay\Model;

use Digidirect\ProductOverlay\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Digidirect\ProductOverlay\Api\Data\OverlayInterface;
use Digidirect\ProductOverlay\Model\Overlay\Attribute\Source;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\BasePrice;
use Digidirect\ProductOverlay\Model\Rule\ProcessorInterface;

/**
 * Class AbstractOverlays
 *
 * @package Digidirect\ProductOverlay\Model
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AbstractOverlays extends AbstractModel implements OverlayInterface, IdentityInterface
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Stock Registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * @var  array
     */
    protected $_prices;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezone;

    /**
     * @var array
     */
    protected $_info;

    /**
     * @var \Digidirect\ProductOverlay\Model\RuleFactory
     */
    protected $_overlayRuleFactory;

    /**
     * @var string
     */
    protected $_cacheTag = 'digidirect_productoverlay';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_productoverlay';

    /**
     * Product overlay cache tag
     */
    const CACHE_TAG = 'digidirect_productoverlay';

    /**
     * @var []
     */
    protected $config;

    /**
     * AbstractOverlays constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Data $helper
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param RuleFactory $overlayRuleFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param array $config
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Digidirect\ProductOverlay\Helper\Data $helper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Digidirect\ProductOverlay\Model\RuleFactory $overlayRuleFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        array $config = []
    ) {
        $this->_date = $date;
        $this->_timezone = $timezone;
        $this->_storeManager = $storeManager;
        $this->_catalogData = $catalogData;
        $this->_stockRegistry = $stockRegistry;
        $this->_priceCurrency = $priceCurrency;
        $this->_helper = $helper;
        $this->_overlayRuleFactory = $overlayRuleFactory;
        $this->config = $config;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\ProductOverlay\Model\ResourceModel\Overlays');
        $this->setIdFieldName(Overlays::OVERLAY_ID);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param null $mode
     * @param null $parent
     * @return $this
     */
    public function init(\Magento\Catalog\Model\Product $product, $mode = null, $parent = null)
    {
        $this->setProduct($product);
        $this->setParentProduct($parent);
        $this->_prices = [];

        // auto detect product page
        if ($mode) {
            $this->setMode($mode == 'category' ? Overlays::POSITION_MODE_CATEGORY : Overlays::POSITION_MODE_PRODUCT);
        } else {
            $this->setMode(Overlays::POSITION_MODE_CATEGORY);
        }

        return $this;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isApplicable()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getProduct();

        if (!$product) {
            return false;
        }

        if ($this->_helper->isEnabledOverlayManagement()) {
            $overlayIds = $product->getOverlayId();
            if ($overlayIds) {
                $overlayIds = explode(',', $overlayIds);
                return (in_array($this->getOverlayId(), $overlayIds)) ? true : false;
            }
        }

        $checkCatalogRuleOverlay = $this->checkRuleWithProcessor('catalog_rule_overlays');
        if (null !== $checkCatalogRuleOverlay) {
            return $checkCatalogRuleOverlay;
        }

        // has image for the current mode
        if (!$this->getSkipCheckImage() && !$this->getData($this->getMode() . '_img')) {
            return false;
        }

        $now = $this->_date->date();
        if ($this->getDateRangeEnabled() && ($now < $this->getFromDate() || $now > $this->getToDate())) {
            return false;
        }

        if ($this->getIsNew()) {
            $isNew = $this->_isNew($product) ? Source\IsNew::YES : Source\IsNew::NO;
            if ($this->getIsNew() != $isNew) {
                return false;
            }
        }

        if ($this->getIsSale()) {
            $isSale = $this->_isSale() ? Source\IsSale::YES : Source\IsSale::NO;
            if ($this->getIsSale() != $isSale) {
                return false;
            }
        }

        if ($this->getCustomerGroupEnabled() && $this->checkRuleWithProcessor('customer_group') === false) {
            return false;
        }

        if ($this->getPrivateSalesEnabled()) {
            if ($this->checkRuleWithProcessor('private_sales') === false) {
                return false;
            }
        }

        $stockStatus = $this->getStockStatus();
        if ($stockStatus) {
            if (!($this->checkRuleWithProcessor('stock'))) {
                return false;
            }
        }

        if (!$this->_isPriceApplicable($product)) {
            return false;
        }

        $useConditions = $inArray = false;
        // individual products logic
        if ("" != $this->getCondSerialize()) {
            $useConditions = true;

            /** @var \Digidirect\ProductOverlay\Model\Rule $overlayRule */
            $overlayRule = $this->_overlayRuleFactory->create();
            $overlayRule->setConditions([]);
            $overlayRule->setStores($this->getStores());
            $overlayRule->setConditionsSerialized($this->getCondSerialize());
            $overlayRule->setProduct($product);

            $productIds = $overlayRule->getMatchingProductIds();
            $inArray = array_key_exists($product->getId(), $productIds)
                && (array_key_exists($product->getStore()->getId(), $productIds[$product->getId()]) ||
                    array_key_exists(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $productIds[$product->getId()])
                );
        }

        return ($useConditions && $inArray) || !$useConditions;
    }

    protected function _checkOverlayManagement($product)
    {
    }

    /**
     * @param string $code
     * @return bool
     */
    protected function checkRuleWithProcessor(string $code)
    {
        $processor = $this->config['processors_factory'][$code] ?? null;
        if (!$processor || !($processor instanceof ProcessorInterface)) {
            return false;
        }
        return $processor->isApplicable($this);
    }

    /**
     * @param Product $product
     * @return float
     */
    protected function _getMinimalPrice($product)
    {
        $minimalPrice = $this->_catalogData->getTaxPrice($product, $product->getMinimalPrice(), true);

        if ($product->getTypeId() == 'grouped') {
            $associatedProducts = $this->_helper->getUsedProducts($product);
            foreach ($associatedProducts as $item) {
                $temp = $this->_catalogData->getTaxPrice($item, $item->getFinalPrice(), true);
                if (null === $minimalPrice || $temp < $minimalPrice) {
                    $minimalPrice = $temp;
                }
            }
        }

        return $minimalPrice;
    }

    /**
     * @param Product $product
     * @return float|int
     */
    protected function _getMaximalPrice($product)
    {
        $maximalPrice = 0;
        if ($product->getTypeId() == 'grouped') {
            $associatedProducts = $this->_helper->getUsedProducts($product);
            foreach ($associatedProducts as $item) {
                $qty = $item->getQty() * 1 ? $item->getQty() * 1 : 1;
                $maximalPrice += $qty * $this->_catalogData->getTaxPrice($item, $item->getFinalPrice(), true);
            }
        }
        if (!$maximalPrice) {
            $maximalPrice = $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), true);
        }

        return $maximalPrice;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _isNew(\Magento\Catalog\Model\Product $product)
    {
        $fromDate = '';
        $toDate = '';
        if ($this->_helper->getModuleConfig(Data::XML_PATH_NEW_IS_NEW, $this->_storeManager->getStore()->getId())) {
            $fromDate = $product->getProductNewFromDate();
            $toDate = $product->getProductNewToDate();
        }

        if (!$fromDate && !$toDate) {
            $newCreatingDate = $this->_helper->getModuleConfig(
                Data::XML_PATH_NEW_CREATION_DATE,
                $this->_storeManager->getStore()->getId()
            );
            if ($newCreatingDate) {
                $days = $this->_helper->getModuleConfig(
                    Data::XML_PATH_NEW_DAYS,
                    $this->_storeManager->getStore()->getId()
                );
                if (!$days) {
                    return false;
                }
                $createdAt = strtotime($product->getCreatedAt());
                $now = $this->_date->date('U');
                return ($now - $createdAt <= $days * 86400); // 60 sec. * 60 min. * 24 hours = 86400 sec.
            } else {
                return false;
            }
        }

        $now = $this->_date->date();
        if ($fromDate && $now < $fromDate) {
            return false;
        }

        if ($toDate) {
            $toDate = str_replace('00:00:00', '23:59:59', $toDate);
            if ($now > $toDate) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _loadPrices()
    {
        if (!$this->_prices) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->getProduct();
            /** @var \Magento\Catalog\Model\Product $parent */
            $parent = $this->getParentProduct();

            $regularPrice = $product->getPrice();

            $specialPrice = 0;
            if ($this->getIsSale() && $this->getSpecialPriceOnly()) {
                $now = $this->_timezone->date()->format('Y-m-d H:i:s');
                if ($product->getSpecialFromDate() && $now >= $product->getSpecialFromDate()) {
                    $specialPrice = $product->getSpecialPrice();
                    if ($product->getSpecialToDate()
                        && $now > $product->getSpecialToDate()
                    ) {
                        $specialPrice = 0;
                    }
                }
            } else {
                $specialPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();

                if ($product->getTypeId() == 'bundle') {
                    list($specialPrice) = $product->getPriceModel()->getPrice($product);
                    $regularPrice = $specialPrice;

                    $price = $product->getSpecialPrice();
                    if (!(null === $price) && $price < 100) {
                        $regularPrice = ($specialPrice / $price) * 100;
                    }
                }
            }

            if ($parent && ($parent->getTypeId() == 'grouped')) {
                $usedProds = $this->_helper->getUsedProducts($parent);
                foreach ($usedProds as $child) {
                    if ($child->getId() != $product->getId()) {
                        $regularPrice += $child->getPrice();
                        $specialPrice += $child->getFinalPrice();
                    }
                }
            }
            $this->_prices = [
                'price' => $regularPrice,
                'special_price' => $specialPrice,
            ];
        }
        return $this->_prices;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _isSale()
    {
        $price = $this->_loadPrices();

        if ($price['price'] <= 0
            || ($this->getSpecialPriceOnly() && !$price['special_price'])
        ) {
            return false;
        }

        // in dollars
        $diff = $price['price'] - $price['special_price'];
        $min = $this->_helper->getModuleConfig(
            Data::XML_PATH_ON_SALE_SALE_MIN,
            $this->_storeManager->getStore()->getId()
        );

        // in percents
        $value = $this->_calcOnSailPercent($diff, $this->_prices['price']);
        $minPercent = $this->_helper->getModuleConfig(
            Data::XML_PATH_ON_SALE_SALE_MIN_PERCENT,
            $this->_storeManager->getStore()->getId()
        );

        $percentDiscountLessThanRequired = $minPercent && ($value < $minPercent);
        $percentDiscountMoreOrEqualThanRequired = $minPercent && ($value >= $minPercent);

        if ($minPercent && $min) {
            if (($diff < 0.001 || ($min && ($diff < $min))) && $percentDiscountLessThanRequired) {
                return false;
            }
        } elseif (!$minPercent && $min) {
            if (($diff < 0.001 || ($min && ($diff < $min)))) {
                return false;
            }
        } elseif (!$min && $minPercent) {
            if ($percentDiscountLessThanRequired) {
                return false;
            }
            if ($percentDiscountMoreOrEqualThanRequired) {
                return true;
            }
        } elseif (!$min && !$minPercent) {
            if ($diff < 0.001) {
                return false;
            }
            return true;
        }

        if (($diff < 0.001 || ($min && ($diff < $min)))) {
            return false;
        }

        return true;
    }

    /**
     * @param float $value
     * @param float $price
     * @return float
     */
    protected function _calcOnSailPercent($value, $price)
    {
        $roundSetting = $this->_helper->getModuleConfig(
            Data::XML_PATH_ON_SALE_ROUNDING,
            $this->_storeManager->getStore()->getId()
        );
        switch ($roundSetting) {
            case 'floor':
                $value = floor($value * 100 / $price);
                break;
            case 'round':
                $value = round($value * 100 / $price);
                break;
            case 'ceil':
                $value = ceil($value * 100 / $price);
                break;
        }
        return $value;
    }

    /**
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _isPriceApplicable(Product $product)
    {
        if ($this->getPriceRangeEnabled()) {
            switch ($this->getByPrice()) {
                case Source\ByPrice::BASE_PRICE:
                    $price = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
                    break;
                case Source\ByPrice::SPECIAL_PRICE:
                    $price = $product->getPriceInfo()->getPrice(SpecialPrice::PRICE_CODE)->getValue();
                    break;
                case Source\ByPrice::FINAL_PRICE:
                    $price = $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), false);
                    break;
                case Source\ByPrice::FINAL_PRICE_INCL_TAX:
                    $price = $this->_catalogData->getTaxPrice($product, $product->getFinalPrice(), true);
                    break;
                case Source\ByPrice::STARTING_FROM_PRICE:
                    $price = $this->_getMinimalPrice($product);
                    break;
                case Source\ByPrice::STARTING_TO_PRICE:
                    $price = $this->_getMaximalPrice($product);
                    break;
                default:
                    $price = $product->getPrice();
                    break;
            }
            $fromPrice = $this->getFromPrice();
            $toPrice = $this->getToPrice();
            if ($product->getTypeId() == 'bundle') {
                if (!$this->_isBundlePriceApplicable($product)) {
                    return false;
                }
            } else {
                if (!is_numeric($fromPrice)) {
                    return false;
                }

                if (!is_numeric($price)) {
                    return false;
                }
                if (is_numeric($fromPrice) && (is_numeric($price) && ($price < $fromPrice))
                    || $toPrice && ($price > $toPrice)
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * The check if the overlay must be displayed for bundle product
     *
     * @param Product $product
     * @return bool
     */
    protected function _isBundlePriceApplicable(Product $product)
    {
        if ($product->getTypeId() != 'bundle') {
            return false;
        }
        $priceInfo = $product->getPriceInfo();
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);
        $regularPrice = $priceInfo->getPrice(RegularPrice::PRICE_CODE);
        $minPrice = $finalPrice->getMinimalPrice()->getValue();
        $maxPrice = $finalPrice->getMaximalPrice()->getValue();
        $minRegPrice = $regularPrice->getMinimalPrice()->getValue();
        $maxRegPrice = $regularPrice->getMaximalPrice()->getValue();
        $fromPrice = $this->getFromPrice();
        $toPrice = $this->getToPrice();
        switch ($this->getByPrice()) {
            case Source\ByPrice::BASE_PRICE:
                $minimalPrice = $minRegPrice;
                $maximalPrice = $maxRegPrice;
                break;
            case Source\ByPrice::SPECIAL_PRICE:
                if ($minPrice < $minRegPrice || $maxPrice < $maxRegPrice) {
                    $minimalPrice = $minPrice;
                    $maximalPrice = $maxPrice;
                } else {
                    $minimalPrice = null;
                    $maximalPrice = null;
                }
                break;
            case Source\ByPrice::FINAL_PRICE:
                $minimalPrice = $this->_catalogData->getTaxPrice($product, $minPrice, false);
                $maximalPrice = $this->_catalogData->getTaxPrice($product, $maxPrice, false);
                break;
            case Source\ByPrice::FINAL_PRICE_INCL_TAX:
                $minimalPrice = $this->_catalogData->getTaxPrice($product, $minPrice, true);
                $maximalPrice = $this->_catalogData->getTaxPrice($product, $maxPrice, true);
                break;
            default:
                $minimalPrice = $minPrice;
                $maximalPrice = $maxPrice;
                break;
        }
        if ((!is_numeric($minimalPrice) || !is_numeric($maximalPrice))
            || (!is_numeric($fromPrice) && !is_numeric($toPrice))
            || (is_numeric($fromPrice) && is_numeric($minimalPrice) && ($minimalPrice < $fromPrice))
            || (is_numeric($toPrice) && is_numeric($maximalPrice) && ($maximalPrice > $toPrice))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get value by overlay mode
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($key)
    {
        $data = $this->getData($this->getMode() . '_' . $key);
        if ($data == null) {
            $data = $this->getData('prod' . '_' . $key);
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getImageSize()
    {
        return (int)$this->getData($this->getMode() . '_image_size');
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::OVERLAY_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::OVERLAY_ID);
    }

    /**
     * Get position
     *
     * @return string|null
     */
    public function getPos()
    {
        return $this->getData(self::POS);
    }

    /**
     * Check is overlay single
     *
     * @return bool|null
     */
    public function getIsSingle()
    {
        return $this->getData(self::IS_SINGLE);
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get status code
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get stores
     *
     * @return array
     */
    public function getStores()
    {
        return $this->_getDataArray(self::STORES);
    }

    /**
     * Get product text
     *
     * @return string|null
     */
    public function getProdTxt()
    {
        return $this->getData(self::PROD_TXT);
    }

    /**
     * Get product image
     *
     * @return string|null
     */
    public function getProdImg()
    {
        return $this->getData(self::PROD_IMG);
    }

    /**
     * Get product image size
     *
     * @return string|null
     */
    public function getProdImageSize()
    {
        return $this->getData(self::PROD_IMAGE_SIZE);
    }

    /**
     * Get product position
     *
     * @return string|null
     */
    public function getProdPos()
    {
        return $this->getData(self::PROD_POS);
    }

    /**
     * Get category text
     *
     * @return string|null
     */
    public function getCatTxt()
    {
        return $this->getData(self::CAT_TXT);
    }

    /**
     * Get category image
     *
     * @return string|null
     */
    public function getCatImg()
    {
        return $this->getData(self::CAT_IMG);
    }

    /**
     * Get category image size
     *
     * @return string|null
     */
    public function getCatImageSize()
    {
        return $this->getData(self::CAT_IMAGE_SIZE);
    }

    /**
     * Get category position
     *
     * @return string|null
     */
    public function getCatPos()
    {
        return $this->getData(self::CAT_POS);
    }

    /**
     * Get category position
     *
     * @return string|null
     */
    public function getIsNew()
    {
        return $this->getData(self::IS_NEW);
    }

    /**
     * Get is sale
     *
     * @return string|null
     */
    public function getIsSale()
    {
        return $this->getData(self::IS_SALE);
    }

    /**
     * Get special price only
     *
     * @return string|null
     */
    public function getSpecialPriceOnly()
    {
        return $this->getData(self::SPECIAL_PRICE_ONLY);
    }

    /**
     * Get stock less
     *
     * @return string|null
     */
    public function getStockLess()
    {
        return $this->getData(self::STOCK_LESS);
    }

    /**
     * Get stock more
     *
     * @return string|null
     */
    public function getStockMore()
    {
        return $this->getData(self::STOCK_MORE);
    }

    /**
     * Get stock status
     *
     * @return string|null
     */
    public function getStockStatus()
    {
        return $this->getData(self::STOCK_STATUS);
    }

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * Get date range enabled
     *
     * @return string|null
     */
    public function getDateRangeEnabled()
    {
        return $this->getData(self::DATE_RANGE_ENABLED);
    }

    /**
     * Get from price
     *
     * @return string|null
     */
    public function getFromPrice()
    {
        return $this->getData(self::FROM_PRICE);
    }

    /**
     * Get to price
     *
     * @return string|null
     */
    public function getToPrice()
    {
        return $this->getData(self::TO_PRICE);
    }

    /**
     * Get by price
     *
     * @return string|null
     */
    public function getByPrice()
    {
        return $this->getData(self::BY_PRICE);
    }

    /**
     * Get price range enabled
     *
     * @return string|null
     */
    public function getPriceRangeEnabled()
    {
        return $this->getData(self::PRICE_RANGE_ENABLED);
    }

    /**
     * Get customer group ids
     *
     * @return string|null
     */
    public function getCustomerGroupIds()
    {
        return $this->getData(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * Get cond serialize
     *
     * @return string|null
     */
    public function getCondSerialize()
    {
        return $this->getData(self::COND_SERIALIZE);
    }

    /**
     * Get customer group enabled
     *
     * @return string|null
     */
    public function getCustomerGroupEnabled()
    {
        return $this->getData(self::CUSTOMER_GROUP_ENABLED);
    }

    /**
     * Get use for parent
     *
     * @return string|null
     */
    public function getUseForParent()
    {
        return $this->getData(self::USE_FOR_PARENT);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setId($id)
    {
        return $this->setData(self::OVERLAY_ID, $id);
    }

    /**
     * Set Pos
     *
     * @param string $pos
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setPos($pos)
    {
        return $this->setData(self::POS, $pos);
    }

    /**
     * Set is single
     *
     * @param bool $isSingle
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setIsSingle($isSingle)
    {
        return $this->setData(self::IS_SINGLE, $isSingle);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set stores
     *
     * @param string $stores
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStores($stores)
    {
        return $this->setData(self::STORES, $stores);
    }

    /**
     * Set prod txt
     *
     * @param string $prodTxt
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdTxt($prodTxt)
    {
        return $this->setData(self::PROD_TXT, $prodTxt);
    }

    /**
     * Set prod img
     *
     * @param string $prodImg
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdImg($prodImg)
    {
        return $this->setData(self::PROD_IMG, $prodImg);
    }

    /**
     * Set prod image size
     *
     * @param string $prodImageSize
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdImageSize($prodImageSize)
    {
        return $this->setData(self::PROD_IMAGE_SIZE, $prodImageSize);
    }

    /**
     * Set prod pos
     *
     * @param string $prodPos
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setProdPos($prodPos)
    {
        return $this->setData(self::PROD_POS, $prodPos);
    }

    /**
     * Set cat txt
     *
     * @param string $catTxt
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatTxt($catTxt)
    {
        return $this->setData(self::CAT_TXT, $catTxt);
    }

    /**
     * Set cat img
     *
     * @param string $catImg
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatImg($catImg)
    {
        return $this->setData(self::CAT_IMG, $catImg);
    }

    /**
     * Set cat image size
     *
     * @param string $catImageSize
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatImageSize($catImageSize)
    {
        return $this->setData(self::CAT_IMAGE_SIZE, $catImageSize);
    }

    /**
     * Set cat pos
     *
     * @param string $catPos
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCatPos($catPos)
    {
        return $this->setData(self::CAT_POS, $catPos);
    }

    /**
     * Set is new
     *
     * @param string $isNew
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setIsNew($isNew)
    {
        return $this->setData(self::IS_NEW, $isNew);
    }

    /**
     * Set is sale
     *
     * @param string $isSale
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setIsSale($isSale)
    {
        return $this->setData(self::IS_SALE, $isSale);
    }

    /**
     * Set special price only
     *
     * @param string $specialPriceOnly
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setSpecialPriceOnly($specialPriceOnly)
    {
        return $this->setData(self::SPECIAL_PRICE_ONLY, $specialPriceOnly);
    }

    /**
     * Set stock less
     *
     * @param string $stockLess
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStockLess($stockLess)
    {
        return $this->setData(self::STOCK_LESS, $stockLess);
    }

    /**
     * Set stock more
     *
     * @param string $stockMore
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStockMore($stockMore)
    {
        return $this->setData(self::STOCK_MORE, $stockMore);
    }

    /**
     * Set stock status
     *
     * @param string $stockStatus
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setStockStatus($stockStatus)
    {
        return $this->setData(self::STOCK_STATUS, $stockStatus);
    }

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * Set to date
     *
     * @param string $toDate
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * Set date range enabled
     *
     * @param string $dateRangeEnabled
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setDateRangeEnabled($dateRangeEnabled)
    {
        return $this->setData(self::DATE_RANGE_ENABLED, $dateRangeEnabled);
    }

    /**
     * Set from price
     *
     * @param string $fromPrice
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setFromPrice($fromPrice)
    {
        return $this->setData(self::FROM_PRICE, $fromPrice);
    }

    /**
     * Set to price
     *
     * @param string $toPrice
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setToPrice($toPrice)
    {
        return $this->setData(self::TO_PRICE, $toPrice);
    }

    /**
     * Set by price
     *
     * @param string $byPrice
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setByPrice($byPrice)
    {
        return $this->setData(self::BY_PRICE, $byPrice);
    }

    /**
     * Set price range enabled
     *
     * @param string $priceRangeEnabled
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setPriceRangeEnabled($priceRangeEnabled)
    {
        return $this->setData(self::PRICE_RANGE_ENABLED, $priceRangeEnabled);
    }

    /**
     * Set customer group ids
     *
     * @param string $customerGroupIds
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCustomerGroupIds($customerGroupIds)
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customerGroupIds);
    }

    /**
     * Set cond serialize
     *
     * @param string $condSerialize
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCondSerialize($condSerialize)
    {
        return $this->setData(self::COND_SERIALIZE, $condSerialize);
    }

    /**
     * Set customer group enabled
     *
     * @param string $customerGroupEnabled
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setCustomerGroupEnabled($customerGroupEnabled)
    {
        return $this->setData(self::CUSTOMER_GROUP_ENABLED, $customerGroupEnabled);
    }

    /**
     * Set use for parent
     *
     * @param bool $useForParent
     * @return \Digidirect\ProductOverlay\Api\Data\OverlayInterface
     */
    public function setUseForParent($useForParent)
    {
        return $this->setData(self::USE_FOR_PARENT, $useForParent);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getCatalogPriceRulesIds()
    {
        return $this->_getDataArray(self::CATALOG_PRICE_RULES_IDS);
    }

    /**
     * @return mixed
     */
    public function getPrivateSalesEnabled()
    {
        return $this->getData(self::PRIVATE_SALES_ENABLED);
    }

    /**
     * @return mixed
     */
    public function getStockFrom()
    {
        return $this->getData(self::STOCK_FROM);
    }

    /**
     * @return mixed
     */
    public function getStockTo()
    {
        return $this->getData(self::STOCK_TO);
    }

    /**
     * @param string $from
     * @return $this
     */
    public function setStockFrom($from)
    {
        return $this->setData(self::STOCK_FROM, $from);
    }

    /**
     * @param string $stockTo
     * @return $this
     */
    public function setStockTo($stockTo)
    {
        return $this->setData(self::STOCK_TO, $stockTo);
    }

    /**
     * @param string $key
     * @return array
     */
    protected function _getDataArray($key)
    {
        $value = $this->getData($key);
        if (!is_array($value)) {
            $value = array_filter(explode(',', $value));
            $this->setData(self::CATALOG_PRICE_RULES_IDS, $value);
        }
        return $value;
    }
}
