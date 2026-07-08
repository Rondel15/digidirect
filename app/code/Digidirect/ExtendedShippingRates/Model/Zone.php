<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Magento\Framework\DataObject;
use Magento\Rule\Model\AbstractModel;
use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;

/**
 * Class Zone
 * @package Digidirect\ExtendedShippingRates\Model
 */
class Zone extends AbstractModel implements ZoneInterface
{
    const CURRENT_ZONE = 'current_zone';
    const ZONE_TABLE_NAME = 'digidirect_extendedshippingrates_zone';
    const ZONE_STORE_TABLE_NAME = 'digidirect_extendedshippingrates_zone_store';

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\Zone\Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\Rule\Model\Action\CollectionFactory
     */
    protected $actionsCollectionFactory;

    /**
     * @var array
     */
    protected $dataProcessors;

    /**
     * @var array
     */
    protected $defaultAggregator;

    /**
     * @return array
     */
    public static function getSimpleSetFields()
    {
        return [
            self::COUNTRY_ID,
            self::REGION_ID,
            self::POSTCODE
        ];
    }

    /**
     * Zone constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Zone\Condition\CombineFactory $condCombineFactory
     * @param \Magento\Rule\Model\Action\CollectionFactory $actionsCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $dataProcessors
     * @param array $defaultAggregator
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Digidirect\ExtendedShippingRates\Model\Zone\Condition\CombineFactory $condCombineFactory,
        \Magento\Rule\Model\Action\CollectionFactory $actionsCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $dataProcessors = [],
        array $defaultAggregator = [],
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->actionsCollectionFactory = $actionsCollectionFactory;
        $this->dataProcessors = $dataProcessors;
        $this->defaultAggregator = $defaultAggregator;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
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
        $this->_init('Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone');
        $this->setIdFieldName('entity_id');
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get priority
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->getData(self::PRIORITY);
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
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get conditions serialized
     *
     * @return string
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Get default shipping method
     *
     * @return string
     */
    public function getDefaultShippingMethod()
    {
        return $this->getData(self::DEFAULT_SHIPPING_METHOD);
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
     * Get store ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        return $this->getData(self::STORE_IDS);
    }

    /**
     * @return mixed
     */
    public function getAttributeSet()
    {
        return $this->getData(self::ATTRIBUTE_SET);
    }

    /**
     * @return mixed
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @return mixed
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @return mixed
     */
    public function getZoneId()
    {
        return $this->getData(self::ZONE_ID);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param string $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Set is active
     *
     * @param string $isActive
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Set default shipping method
     *
     * @param string $defaultShippingMethod
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setDefaultShippingMethod($defaultShippingMethod)
    {
        return $this->setData(self::DEFAULT_SHIPPING_METHOD, $defaultShippingMethod);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool|array
     */
    public function validateData(DataObject $dataObject)
    {
        $errors = parent::validateData($dataObject);

        if (!$dataObject->getName()) {
            $errors[] = __('Zone name is required');
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Initialize zone model data from array.
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        parent::loadPost($data);
        $this->unsetData('updated_at');

        return $this;
    }

    /**
     * Get zone condition combine model instance
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Zone\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get zone actions instance
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        $factory = $this->actionsCollectionFactory;
        $result = $factory->create();

        return $result;
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

    /**
     * Set attribute set
     *
     * @param int $attributeSet
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setAttributeSet($attributeSet)
    {
        return $this->setData(self::ATTRIBUTE_SET, $attributeSet);
    }

    /**
     * Set country id
     *
     * @param string $countryId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * Set region id
     *
     * @param string $regionId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @param string $zoneId
     * @return $this
     */
    public function setZoneId($zoneId)
    {
        return $this->setData(self::ZONE_ID, $zoneId);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createSimpleConditions(&$data)
    {
        $conditions = ['1' => $this->defaultAggregator];
        $key = 1;
        foreach ($this->getSimpleSetFields() as $field) {
            $processor = $this->dataProcessors[$field];
            $config = $processor->prepareData($data);
            if (!empty($config['value'])) {
                $conditions['1--' . $key] = $config;
                $key++;
            }
        }
        $data['conditions'] = $conditions;
        return $data;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function removeSimpleAttributeValues(&$data)
    {
        foreach ($this->getSimpleSetFields() as $field) {
            $data[$field] = '';
        }
        return $data;
    }
}
