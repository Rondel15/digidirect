<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
use Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface;

/**
 * Class Carrier
 * @package Digidirect\ExtendedShippingRates\Model
 */
class Carrier extends AbstractExtensibleModel implements CarrierInterface
{
    const CURRENT_CARRIER = 'current_carrier';

    const CARRIER_TABLE_NAME = 'digidirect_extendedshippingrates_carrier';
    const METHOD_TABLE_NAME = 'digidirect_extendedshippingrates_methods';
    const RATE_TABLE_NAME = 'digidirect_extendedshippingrates_rates';
    const CARRIER_LABELS_TABLE_NAME = 'digidirect_extendedshippingrates_carrier_label';
    const METHOD_LABELS_TABLE_NAME = 'digidirect_extendedshippingrates_methods_label';

    const DEFAULT_MODEL = 'Digidirect\ExtendedShippingRates\Model\Carrier\Artificial';
    const DEFAULT_TYPE = 'I';
    const DEFAULT_ERROR_MESSAGE =
        'This shipping method is not available. To use this shipping method, please contact us.';

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Method\Collection
     */
    protected $methodsCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Carrier constructor.
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
        $this->_init('Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier');
        $this->setIdFieldName('carrier_id');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::CARRIER_ID);
    }

    /**
     * Get carrier code
     *
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->getData(self::CARRIER_CODE);
    }

    /**
     * Get sallowspecific
     *
     * @return string
     */
    public function getSallowspecific()
    {
        return $this->getData(self::SALLOWSPECIFIC);
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->getData(self::MODEL);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get specificerrmsg
     *
     * @return string
     */
    public function getSpecificerrmsg()
    {
        return $this->getData(self::SPECIFICERRMSG);
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
     * Get methods
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->getData(self::METHODS);
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
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setId($id)
    {
        return $this->setData(self::CARRIER_ID, $id);
    }

    /**
     * Set carrier code
     *
     * @param string $carrierCode
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setCarrierCode($carrierCode)
    {
        return $this->setData(self::CARRIER_CODE, $carrierCode);
    }

    /**
     * Set sallowspecific
     *
     * @param string $sallowspecific
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setSallowspecific($sallowspecific)
    {
        return $this->setData(self::SALLOWSPECIFIC, $sallowspecific);
    }

    /**
     * Set model
     *
     * @param string $model
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setModel($model)
    {
        return $this->setData(self::MODEL, $model);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set type
     *
     * @param string $type
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set specificerrmsg
     *
     * @param string $specificerrmsg
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setSpecificerrmsg($specificerrmsg)
    {
        return $this->setData(self::SPECIFICERRMSG, $specificerrmsg);
    }

    /**
     * Set price
     *
     * @param string $price
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set methods
     *
     * @param array $methods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setMethods($methods)
    {
        return $this->setData(self::METHODS, $methods);
    }

    /**
     * Set active
     *
     * @param int|bool $active
     * @return \Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface
     */
    public function setActive($active)
    {
        return $this->setData(self::ACTIVE, $active);
    }

    /**
     * Set if not yet and retrieve method store labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * @return ResourceModel\Method\Collection
     */
    public function getMethodsCollection()
    {
        return $this->methodsCollection;
    }

    /**
     * @param ResourceModel\Method\Collection $methods
     * @return $this
     */
    public function setMethodsCollection(\Digidirect\ExtendedShippingRates\Model\ResourceModel\Method\Collection $methods)
    {
        $this->methodsCollection = $methods;
        return $this;
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

        if (!$dataObject->getCarrierCode()) {
            $errors[] = __('Carrier code is required');
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Initialize carrier model data from array.
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

        return $this;
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
