<?php
namespace Digidirect\ExtendedShippingRates\Model\Carrier\Method;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Model\AbstractExtensibleModel;
use Digidirect\ExtendedShippingRates\Api\Data\RateInterface;
use Digidirect\ExtendedShippingRates\Model\Config\Source\WeightType;

/**
 * Class Rate
 * @package Digidirect\ExtendedShippingRates\Model\Carrier\Method
 */
class Rate extends AbstractExtensibleModel implements RateInterface
{
    const CURRENT_RATE = 'current_rate';

    const PRICE_CALCULATION_OVERWRITE = 0;
    const PRICE_CALCULATION_SUM = 1;

    const MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRIORITY = 0;
    const MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRICE = 1;
    const MULTIPLE_RATES_PRICE_CALCULATION_MIN_PRICE = 2;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Rate constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate');
        $this->setIdFieldName('rate_id');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::RATE_ID);
    }

    /**
     * Get method id
     *
     * @return int
     */
    public function getMethodId()
    {
        return $this->getData(self::METHOD_ID);
    }

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
    }

    /**
     * Get rate method price
     *
     * @return int
     */
    public function getRateMethodPrice()
    {
        return $this->getData(self::RATE_METHOD_PRICE);
    }

    /**
     * Get title
     *
     * @return int
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get country id
     *
     * @return int
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * Get region
     *
     * @return int
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * Get zip from
     *
     * @return int
     */
    public function getZipFrom()
    {
        return $this->getData(self::ZIP_FROM);
    }

    /**
     * Get zip to
     *
     * @return int
     */
    public function getZipTo()
    {
        return $this->getData(self::ZIP_TO);
    }

    /**
     * Get price from
     *
     * @return int
     */
    public function getPriceFrom()
    {
        return $this->getData(self::PRICE_FROM);
    }

    /**
     * Get price to
     *
     * @return int
     */
    public function getPriceTo()
    {
        return $this->getData(self::PRICE_TO);
    }

    /**
     * Get qty from
     *
     * @return int
     */
    public function getQtyFrom()
    {
        return $this->getData(self::QTY_FROM);
    }

    /**
     * Get qty to
     *
     * @return int
     */
    public function getQtyTo()
    {
        return $this->getData(self::QTY_TO);
    }

    /**
     * Get weight from
     *
     * @return int
     */
    public function getWeightFrom()
    {
        return $this->getData(self::WEIGHT_FROM);
    }

    /**
     * Get weight to
     *
     * @return int
     */
    public function getWeightTo()
    {
        return $this->getData(self::WEIGHT_TO);
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Get price per product
     *
     * @return int
     */
    public function getPricePerProduct()
    {
        return $this->getData(self::PRICE_PER_PRODUCT);
    }

    /**
     * Get price per item
     *
     * @return int
     */
    public function getPricePerItem()
    {
        return $this->getData(self::PRICE_PER_ITEM);
    }

    /**
     * Get price percent per product
     *
     * @return int
     */
    public function getPricePercentPerProduct()
    {
        return $this->getData(self::PRICE_PERCENT_PER_PRODUCT);
    }

    /**
     * Get price percent per item
     *
     * @return int
     */
    public function getPricePercentPerItem()
    {
        return $this->getData(self::PRICE_PERCENT_PER_ITEM);
    }

    /**
     * Get item price percent
     *
     * @return int
     */
    public function getItemPricePercent()
    {
        return $this->getData(self::ITEM_PRICE_PERCENT);
    }

    /**
     * Get price per weight
     *
     * @return int
     */
    public function getPricePerWeight()
    {
        return $this->getData(self::PRICE_PER_WEIGHT);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getData(self::ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setId($id)
    {
        return $this->setData(self::RATE_ID, $id);
    }

    /**
     * Set method id
     *
     * @param string $methodId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setMethodId($methodId)
    {
        return $this->setData(self::METHOD_ID, $methodId);
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Set rate method price
     *
     * @param string $rateMethodPrice
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setRateMethodPrice($rateMethodPrice)
    {
        return $this->setData(self::RATE_METHOD_PRICE, $rateMethodPrice);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set country id
     *
     * @param string $countryId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * Set region
     * @param string $region
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Set region id
     *
     * @param string $regionId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * Set zip from
     *
     * @param string $zipFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setZipFrom($zipFrom)
    {
        return $this->setData(self::ZIP_FROM, $zipFrom);
    }

    /**
     * Set zip to
     *
     * @param string $zipTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setZipTo($zipTo)
    {
        return $this->setData(self::ZIP_TO, $zipTo);
    }

    /**
     * Set price from
     *
     * @param string $priceFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPriceFrom($priceFrom)
    {
        return $this->setData(self::PRICE_FROM, $priceFrom);
    }

    /**
     * Set price to
     *
     * @param string $priceTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPriceTo($priceTo)
    {
        return $this->setData(self::PRICE_TO, $priceTo);
    }

    /**
     * Set qty from
     *
     * @param string $qtyFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setQtyFrom($qtyFrom)
    {
        return $this->setData(self::QTY_FROM, $qtyFrom);
    }

    /**
     * Set qty to
     *
     * @param string $qtyTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setQtyTo($qtyTo)
    {
        return $this->setData(self::QTY_TO, $qtyTo);
    }

    /**
     * Set weight from
     *
     * @param string $weightFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setWeightFrom($weightFrom)
    {
        return $this->setData(self::WEIGHT_FROM, $weightFrom);
    }

    /**
     * Set weight to
     *
     * @param string $weightTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setWeightTo($weightTo)
    {
        return $this->setData(self::WEIGHT_TO, $weightTo);
    }

    /**
     * Set price
     *
     * @param string $price
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Set price per product
     *
     * @param string $pricePerProduct
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePerProduct($pricePerProduct)
    {
        return $this->setData(self::PRICE_PER_PRODUCT, $pricePerProduct);
    }

    /**
     * Set price per item
     *
     * @param string $pricePerItem
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePerItem($pricePerItem)
    {
        return $this->setData(self::PRICE_PER_ITEM, $pricePerItem);
    }

    /**
     * Set price percent per product
     *
     * @param string $pricePercentPerProduct
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePercentPerProduct($pricePercentPerProduct)
    {
        return $this->setData(self::PRICE_PERCENT_PER_PRODUCT, $pricePercentPerProduct);
    }

    /**
     * Set price percent per item
     *
     * @param string $pricePercentPerItem
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePercentPerItem($pricePercentPerItem)
    {
        return $this->setData(self::PRICE_PERCENT_PER_ITEM, $pricePercentPerItem);
    }

    /**
     * Set item price percent
     *
     * @param string $itemPricePercent
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setItemPricePercent($itemPricePercent)
    {
        return $this->setData(self::ITEM_PRICE_PERCENT, $itemPricePercent);
    }

    /**
     * Set price per weight
     *
     * @param string $pricePerWeight
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePerWeight($pricePerWeight)
    {
        return $this->setData(self::PRICE_PER_WEIGHT, $pricePerWeight);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set active
     *
     * @param int|bool $active
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setActive($active)
    {
        return $this->setData(self::ACTIVE, $active);
    }

    /**
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool|array
     */
    public function validateData(DataObject $dataObject)
    {
        $errors = $dataObject->getData('errors');

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $method
     * @param RateRequest $request
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    public function applyRateToMethod(
        \Magento\Quote\Model\Quote\Address\RateResult\Method $method,
        RateRequest $request,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
    ) {
        $result = $this->getCalculatedPrice($request, $methodData);
        $method->setPrice($result);

        // Change method title
        if ($this->getTitle()) {
            $method->setMethodTitle($this->getTitle());
        }

        return $method;
    }

    /**
     * @param RateRequest $request
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return float|int|mixed
     */
    public function getCalculatedPrice(
        RateRequest $request,
        \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
    ) {
        $requestProductCount = count($request->getAllItems());
        $requestItemsCost = $this->calculateItemsTotalPrice($request->getAllItems());

        $packageWeight = $this->validatePackagingWeight($request->getPackageWeight(), $methodData);

        $price['base_rice'] = $this->getPrice();
        $price['per_product'] = $requestProductCount * $this->getPricePerProduct();
        $price['per_item'] = $request->getPackageQty() * $this->getPricePerItem();
        $price['percent_per_product'] = $requestProductCount * $this->getPricePercentPerProduct() / 100;
        $price['percent_per_item'] = $request->getPackageQty() * $this->getPricePercentPerItem() / 100;
        $price['item_price_percent'] = $requestItemsCost * $this->getItemPricePercent() / 100;
        $price['per_weight'] = $packageWeight * $this->getPricePerWeight();

        $result = array_sum($price);
        if ($this->getRateMethodPrice() == self::PRICE_CALCULATION_SUM) {
            $result += $methodData->getData('price');
        }

        return $result;
    }

    /**
     * @param float $weight
     * @param \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData
     * @return float
     */
    public function validatePackagingWeight($weight, \Digidirect\ExtendedShippingRates\Model\Carrier\Method $methodData)
    {
        if ($methodData->getPackagingWeightType() == WeightType::FIXED_CODE) {
            $weight += $methodData->getPackagingWeightValue();
        }

        if ($methodData->getPackagingWeightType() == WeightType::PERCENTAGE_CODE) {
            $weight += (($weight / 100) * $methodData->getPackagingWeightValue());
        }

        return $weight;
    }

    /**
     * @param array $items
     * @return float
     */
    public function calculateItemsTotalPrice($items)
    {
        $totalPrice = 0.0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $totalPrice += $item->getBaseRowTotal();
        }

        return $totalPrice;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequest(RateRequest $request)
    {
        // Not active rates are invalid
        if (!$this->getActive()) {
            return false;
        }

        // Validate country
        if ($this->getCountryId() && $request->getDestCountryId() != $this->getCountryId()) {
            return false;
        }

        // Validate region
        if ($this->getRegionId() && $request->getDestRegionId() != $this->getRegionId()) {
            return false;
        } elseif ($this->getRegion() && $request->getDestRegionCode() != $this->getRegion()) {
            return false;
        }

        if (!$this->validateRequestByZipCode($request)) {
            return false;
        }

        if (!$this->validateRequestByPrice($request)) {
            return false;
        }

        if (!$this->validateRequestByQty($request)) {
            return false;
        }

        if (!$this->validateRequestByWeight($request)) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByZipCode(RateRequest $request)
    {
        if (!$this->getZipFrom() && !$this->getZipTo()) {
            return true;
        }

        $requestZip = $request->getDestPostcode();
        if ($this->getZipFrom() == $this->getZipTo() && $requestZip == $this->getZipFrom()) {
            return true;
        }

        if ($requestZip < $this->getZipFrom()) {
            return false;
        }

        if ($this->getZipTo() && $requestZip > $this->getZipTo()) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByPrice(RateRequest $request)
    {
        if (!$this->getPriceFrom() && !$this->getPriceTo()) {
            return true;
        }

        $requestPrice = $request->getPackageValue();
        if ($this->getPriceFrom() == $this->getPriceTo() && $requestPrice == $this->getPriceFrom()) {
            return true;
        }

        if ($requestPrice < $this->getPriceFrom()) {
            return false;
        }

        if ($this->getPriceTo() != 0 && $requestPrice > $this->getPriceTo()) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByQty(RateRequest $request)
    {
        if (!$this->getQtyFrom() && !$this->getQtyTo()) {
            return true;
        }

        $requestQty = $request->getPackageQty();
        if ($this->getQtyFrom() == $this->getQtyTo() && $requestQty == $this->getQtyFrom()) {
            return true;
        }

        if ($requestQty < $this->getQtyFrom()) {
            return false;
        }

        if ($this->getQtyTo() != 0 && $requestQty > $this->getQtyTo()) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByWeight(RateRequest $request)
    {
        if (!$this->getWeightFrom() && !$this->getWeightTo()) {
            return true;
        }

        $requestWeight = $request->getPackageWeight();
        if ($this->getWeightFrom() == $this->getWeightTo() && $requestWeight == $this->getWeightFrom()) {
            return true;
        }

        if ($requestWeight < $this->getWeightFrom()) {
            return false;
        }

        if ($this->getWeightTo() != 0 && $requestWeight > $this->getWeightTo()) {
            return false;
        }

        return true;
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        if (!$this->getData('skip_resource_after_load')) {
            parent::afterLoad();
            return $this;
        }
        $this->_afterLoad();
        return $this;
    }
}
