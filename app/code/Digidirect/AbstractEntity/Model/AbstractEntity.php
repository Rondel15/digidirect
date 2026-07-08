<?php

namespace Digidirect\AbstractEntity\Model;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface\Proxy as AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Api\Data\RelatedAttributesInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity\UrlProcessor;
use Digidirect\AbstractEntity\Model\AbstractEntity\UrlProcessorFactory;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractEntity
 * @package Digidirect\AbstractEntity\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractEntity extends AbstractExtensibleModel implements AbstractEntityInterface, RelatedAttributesInterface
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY_TYPE = 'digidirect_abstractentity';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_abstractentity';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'abstractentity';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlProcessor
     */
    protected $urlProcessor;

    /**
     * @var UrlProcessorFactory
     */
    protected $urlProcessorFactory;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var array
     */
    protected $multiSelectEntities = [];

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * AbstractEntity constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param UrlProcessorFactory $urlProcessorFactory
     * @param FilterManager $filterManager
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterFactory $filterFactory
     * @param EavConfig $eavConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        UrlProcessorFactory $urlProcessorFactory,
        FilterManager $filterManager,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        FilterFactory $filterFactory = null,
        EavConfig $eavConfig = null
    ) {
        $this->storeManager = $storeManager;
        $this->urlProcessorFactory = $urlProcessorFactory;
        $this->filterManager = $filterManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()->get(
            SearchCriteriaBuilder::class
        );
        $this->filterFactory = $filterFactory ?: ObjectManager::getInstance()->get(FilterFactory::class);
        $this->eavConfig = $eavConfig ?: ObjectManager::getInstance()->get(EavConfig::class);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AbstractEntityResource::class);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get attribute set id
     * @return string
     */
    public function getAttributeSetId()
    {
        return $this->getData(self::ATTRIBUTE_SET_ID);
    }

    /**
     * Set attribute set id
     * @param int $setId
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setAttributeSetId($setId)
    {
        return $this->setData(self::ATTRIBUTE_SET_ID, $setId);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Is visible on frontend
     * @return bool
     */
    public function isVisibleOnFrontend()
    {
        return $this->getData(self::VISIBLE_ON_FRONTEND);
    }

    /**
     * Set visible on frontend
     * @param bool $visibleOnFrontend
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setVisibleOnFrontend($visibleOnFrontend)
    {
        return $this->setData(self::VISIBLE_ON_FRONTEND, $visibleOnFrontend);
    }

    /**
     * Get url key
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Set url key
     * @param string $urlKey
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::NAME, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::NAME, $updatedAt);
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData(self::STORE_ID)) {
            return $this->getData(self::STORE_ID);
        }
        return 0;
    }

    /**
     * Set brand store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->_getData(self::PARENT_ID);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setParentId($id)
    {
        return $this->setData(self::PARENT_ID, $id);
    }

    /**
     * Retrieve array of store ids for this brand.
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $storeIds = [];
            if ($stores = $this->storeManager->getStores()) {
                $storeIds = array_keys($stores);
            }
            $this->setStoreIds($storeIds);
        }
        return $this->getData('store_ids');
    }

    /**
     * Process url rewrites
     * @return $this
     */
    public function processUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->processUrlRewrites($this);
        return $this;
    }

    /**
     * Delete url rewrites
     * @return $this
     */
    public function deleteUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->deleteUrlRewrites($this);
        return $this;
    }

    /**
     * @return UrlProcessor
     */
    protected function getUrlProcessor()
    {
        if (null === $this->urlProcessor) {
            $this->urlProcessor = $this->urlProcessorFactory->create();
        }
        return $this->urlProcessor;
    }

    /**
     * Processing object before save data
     *
     * @return AbstractExtensibleModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $this->validate();
        if(empty($this->getUrlKey()))
        {
            $urlKey = '';
        }
        else 
        {
            $urlKey = trim($this->getUrlKey());
        }
        
        $name = $this->getName();
        if (($urlKey === '' || $urlKey === null) && $name) {
            $this->setUrlKey($this->filterManager->translitUrl($name));
        }

        try {
            $urlKeyAttribute = $this->getResource()->getAttribute(AbstractEntityInterface::URL_KEY);
        } catch (\Throwable $e) {
            return parent::beforeSave();
        }

        $i = 0;
        $urlKeyValue = $this->getUrlKey();
        while (true) {
            if ($this->getResource()->checkAttributeUniqueValue($urlKeyAttribute, $this)) {
                break;
            } else {
                $this->setUrlKey($urlKeyValue . '-' . ++$i);
            }
        }

        return parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        if (!$this->hasData('entity_name')) {
            $this->setData('entity_name', $this->getResource()->getAttributeSetNameById($this->getAttributeSetId()));
        }
        return $this->getData('entity_name');
    }

    /**
     * @param string $attributeName
     * @return AbstractEntityInterface|null
     */
    public function getAttributeObject($attributeName)
    {
        $entityId = (int)$this->getData($attributeName);
        if (!$entityId) {
            return null;
        }

        try {
            $object = $this->abstractEntityRepository->getById($entityId);
        } catch (\Exception $e) {
            $object = null;
        }

        return $object;
    }

    /**
     * @param array $relatedAttributes
     * @return $this
     */
    public function loadRelatedAttributes(array $relatedAttributes)
    {
        foreach ($relatedAttributes as $relatedAttribute) {
            if ($attributeObject = $this->getAttributeObject($relatedAttribute)) {
                $this->setData($relatedAttribute . '_object', $attributeObject);
                $this->setData($relatedAttribute . '_data', $attributeObject->getData());
            }
        }
        return $this;
    }

    /**
     * @param string $attributeCode
     * @param string $glue
     * @param bool $reload
     * @return AbstractEntityInterface[]
     */
    public function getMultiSelectEntities($attributeCode, $glue = ',', $reload = false)
    {
        if (!isset($this->multiSelectEntities[$attributeCode]) || $reload) {
            $this->multiSelectEntities[$attributeCode] = [];
            $entityIds = array_filter(explode($glue, $this->getData($attributeCode)));
            foreach ($entityIds as $entityId) {
                try {
                    $this->multiSelectEntities[$attributeCode][] = $this->abstractEntityRepository->getById($entityId);
                } catch (\Exception $e) {
                    //continue;
                }
            }
        }
        return $this->multiSelectEntities[$attributeCode];
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_validate_before', $this->_getEventData());
        $result = $this->_getResource()->validate($this);
        $this->_eventManager->dispatch($this->_eventPrefix . '_validate_after', $this->_getEventData());
        return $result;
    }

    /**
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param int|string $attributeSet
     * @param array|string|integer|\Magento\Framework\App\Config\Element $attributes
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntitySearchResultsInterface
     */
    public function getChildrenCollection(
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        $attributeSet = null,
        $attributes = null
    ) {
        if (null === $searchCriteriaBuilder) {
            $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        }

        /** @var Filter $filter */
        $filter = $this->filterFactory->create();
        $filter->setField(self::PARENT_ID)->setValue($this->getId());
        $searchCriteriaBuilder->addFilter($filter);

        return $this->abstractEntityRepository->getList($searchCriteriaBuilder->create(), $attributeSet, $attributes);
    }

    /**
     * @inheritdoc
     */
    protected function getCustomAttributesCodes()
    {
        if ($this->customAttributesCodes === null) {
            $allAttributes = $this->eavConfig->getEntityAttributes(self::ENTITY_TYPE, $this);
            $allAttributeCodes = array_keys($allAttributes);
            $this->customAttributesCodes = array_diff(
                $allAttributeCodes,
                AbstractEntityInterface::ATTRIBUTES
            );
        }
        return $this->customAttributesCodes;
    }
}
