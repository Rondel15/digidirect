<?php

namespace Digidirect\ExtendedShippingRates\Model\Carrier;

use Magento\Framework\DataObject;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
use Digidirect\ExtendedShippingRates\Api\Data\MethodInterface;

/**
 * Class Method
 *
 * @package Digidirect\ExtendedShippingRates\Model\Carrier
 */
class Method extends AbstractExtensibleModel implements MethodInterface
{

    const CURRENT_METHOD = 'current_method';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Collection
     */
    protected $ratesCollection;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\MethodFactory
     */
    protected $methodResourceModelFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\MethodFactory $methodResourceModelFactory
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
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\MethodFactory $methodResourceModelFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->methodResourceModelFactory = $methodResourceModelFactory;

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
        $this->_init('Digidirect\ExtendedShippingRates\Model\ResourceModel\Method');
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Get carrier id
     *
     * @return string
     */
    public function getCarrierId()
    {
        return $this->getData(self::CARRIER_ID);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Get cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->getData(self::COST);
    }

    /**
     * Get packaging weight type
     *
     * @return string|null
     */
    public function getPackagingWeightType()
    {
        return $this->getData(self::PACKAGING_WEIGHT_TYPE);
    }

    /**
     * Get packaging weight value
     *
     * @return int|null
     */
    public function getPackagingWeightValue()
    {
        return $this->getData(self::PACKAGING_WEIGHT_VALUE);
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
     * Get rates
     *
     * @return array
     */
    public function getRates()
    {
        return (array)$this->getData(self::RATES);
    }

    /**
     * Get alternative title
     * @return string
     */
    public function getAlternativeTitle()
    {
        return $this->getData(self::ALTERNATIVE_TITLE);
    }

    /**
     * Get alternative code
     * @return string
     */
    public function getAlternativeCode()
    {
        return $this->getData(self::ALTERNATIVE_CODE);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set code
     *
     * @param string $code
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Set carrier id
     *
     * @param string $carrierId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCarrierId($carrierId)
    {
        return $this->setData(self::CARRIER_ID, $carrierId);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set price
     *
     * @param string $price
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Set cost
     *
     * @param string $cost
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCost($cost)
    {
        return $this->setData(self::COST, $cost);
    }

    /**
     * Set packaging weight type
     *
     * @param string $packagingWeightType
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setPackagingWeightType($packagingWeightType)
    {
        return $this->setData(self::PACKAGING_WEIGHT_TYPE, $packagingWeightType);
    }

    /**
     * Set packaging weight value
     *
     * @param int $packagingWeightValue
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setPackagingWeightValue($packagingWeightValue)
    {
        return $this->setData(self::PACKAGING_WEIGHT_VALUE, $packagingWeightValue);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set rates
     *
     * @param array $rates
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setRates($rates)
    {
        return $this->setData(self::RATES, $rates);
    }

    /**
     * Set alternative title
     * @param string $altTitle
     * @return $this
     */
    public function setAlternativeTitle($altTitle)
    {
        return $this->setData(self::ALTERNATIVE_TITLE, $altTitle);
    }

    /**
     * Set alternative code
     * @param string $altCode
     * @return $this
     */
    public function setAlternativeCode($altCode)
    {
        return $this->setData(self::ALTERNATIVE_CODE, $altCode);
    }

    /**
     * Set active
     *
     * @param int|bool $active
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setActive($active)
    {
        return $this->setData(self::ACTIVE, $active);
    }

    /**
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool
     */
    public function validateData(DataObject $dataObject)
    {
        $errors = [];

        if (!$dataObject->getData('code')) {
            $errors[] = __('Method code is required');
        }

        if (!$dataObject->getData('title')) {
            $errors[] = __('Title is required');
        }

        if ($dataObject->getData('price') < 0) {
            $errors[] = __('Price could not be a negative number');
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Get Method label by specified store
     *
     * @param \Magento\Store\Model\Store|int|bool|null $store
     * @return string|bool
     */
    public function getStoreLabel($store = null)
    {
        $storeId = $this->storeManager->getStore($store)->getId();
        $labels = (array)$this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (!empty($labels[0])) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve method store labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Method $methodResourceModel */
            $methodResourceModel = $this->methodResourceModelFactory->create();
            $labels = $methodResourceModel->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * Initialize method model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }
        $this->unsetData('updated_at');

        return $this;
    }

    /**
     * @return \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Collection
     */
    public function getRatesCollection()
    {
        return $this->ratesCollection;
    }

    /**
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Collection $rates
     * @return $this
     */
    public function setRatesCollection(\Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Collection $rates)
    {
        $this->ratesCollection = $rates;

        return $this;
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        if (!$this->getData('skip_resource_after_load')) {
            /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Method $methodResourceModel */
            $methodResourceModel = $this->methodResourceModelFactory->create();
            $methodResourceModel->addRates($this);
        }

        $this->_afterLoad();
        $this->updateStoredData();

        return $this;
    }

    /**
     * Synchronize object's stored data with the actual data
     *
     * @return $this
     */
    private function updateStoredData()
    {
        if (isset($this->_data)) {
            $this->storedData = $this->_data;
        } else {
            $this->storedData = [];
        }
        return $this;
    }
}
