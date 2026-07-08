<?php

namespace Digidirect\AbstractEntity\Model;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntitySearchResultsInterfaceFactory;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as ResourceAbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory as AbstractEntityCollectionFactory;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\FlatCollectionFactory as AbstractEntityFlatCollectionFactor;
use Digidirect\AbstractEntity\Helper\Image as ImageHelper;
use Digidirect\AbstractEntity\Helper\ImageFactory as ImageHelperFactory;
use Digidirect\AbstractEntity\Model\AbstractEntity\Media\ImageProcessorFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\AbstractEntity\Helper\Config as ConfigHelper;
use Digidirect\AbstractEntity\Helper\Data as Helper;

/**
 * Class AbstractEntityRepository
 * @package Digidirect\AbstractEntity\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractEntityRepository implements AbstractEntityRepositoryInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AbstractEntityFactory
     */
    protected $abstractEntityFactory;

    /**
     * @var AbstractEntityCollectionFactory
     */
    protected $abstractEntityCollectionFactory;

    /**
     * @var AbstractEntityFlatCollectionFactory
     */
    protected $abstractEntityFlatCollectionFactory;

    /**
     * @var AbstractEntitySearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceAbstractEntity
     */
    protected $resource;

    /**
     * @var ImageHelperFactory
     */
    protected $imageHelperFactory;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var ImageProcessorFactory
     */
    protected $imageProcessorFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var AbstractEntityInterface[]
     */
    protected $abstractEntityCache = [];

    /**
     * @param ResourceAbstractEntity $resource
     * @param AbstractEntityFactory $abstractEntityFactory
     * @param AbstractEntityCollectionFactory $abstractEntityCollectionFactory
     * @param AbstractEntitySearchResultsInterfaceFactory $searchResultsFactory
     * @param ImageHelperFactory $imageHelperFactory
     * @param ImageProcessorFactory $imageProcessorFactory
     * @param StoreManagerInterface $storeManager
     * @param AbstractEntityFlatCollectionFactor $abstractEntityFlatCollectionFactory
     * @param ConfigHelper $configHelper
     * @param Helper $helper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceAbstractEntity $resource,
        AbstractEntityFactory $abstractEntityFactory,
        AbstractEntityCollectionFactory $abstractEntityCollectionFactory,
        AbstractEntitySearchResultsInterfaceFactory $searchResultsFactory,
        ImageHelperFactory $imageHelperFactory,
        ImageProcessorFactory $imageProcessorFactory,
        StoreManagerInterface $storeManager,
        AbstractEntityFlatCollectionFactor $abstractEntityFlatCollectionFactory,
        ConfigHelper $configHelper,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->abstractEntityFactory = $abstractEntityFactory;
        $this->abstractEntityCollectionFactory = $abstractEntityCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->imageProcessorFactory = $imageProcessorFactory;
        $this->storeManager = $storeManager;
        $this->abstractEntityFlatCollectionFactory = $abstractEntityFlatCollectionFactory;
        $this->configHelper = $configHelper;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AbstractEntityInterface $abstractEntity)
    {
        if ($abstractEntity->getStoreId() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $abstractEntity->setStoreId($storeId);
        }

        try {
            $images = $this->_processImages($abstractEntity);
            $abstractEntity->getResource()->save($abstractEntity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the entity: %1',
                $exception->getMessage()
            ));
        }

        if (!empty($images)) {
            $this->getImageHelper()->deleteOldImage($images);
        }

        if (isset($this->abstractEntityCache[$abstractEntity->getId()])) {
            unset($this->abstractEntityCache[$abstractEntity->getId()]);
        }

        return $abstractEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($abstractEntityId, $storeId = null, $reload = false)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->abstractEntityCache[$abstractEntityId][$storeId]) || $reload) {
            $abstractEntity = $this->abstractEntityFactory->create();
            $abstractEntity->setStoreId($storeId);
            $abstractEntity->getResource()->load($abstractEntity, $abstractEntityId);
            if (!$abstractEntity->getId()) {
                throw new NoSuchEntityException(__('AbstractEntity with id "%1" does not exist.', $abstractEntityId));
            }
            $this->abstractEntityCache[$abstractEntityId][$storeId] = $abstractEntity;
        }

        return $this->abstractEntityCache[$abstractEntityId][$storeId];
    }

    /**
     * @param null|string $attributeSet
     * @param null|array $attributes
     * @return ResourceAbstractEntity\Collection|ResourceAbstractEntity\FlatCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollection($attributeSet = null, $attributes = null)
    {
        $attributeSetId = $this->resource->getAttributeSetIdByName($attributeSet);
        if ($attributeSetId !== null
            && $this->storeManager->getStore()->getId() //there is no flat table for store_id = 0
            && $this->configHelper->isIndexTableEnableForEntity($attributeSetId)
        ) {
            return $this->_getIndexCollection($attributeSet);
        }
        return $this->_getEavCollection($attributeSetId, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria, $attributeSet = null, $attributes = null)
    {
        $collection = $this->getCollection($attributeSet, $attributes);

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->setStoreId($filter->getValue());
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? SortOrder::SORT_ASC : SortOrder::SORT_DESC
                );
            }
        }

        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        if ($collection->getPageSize()) {
            $searchResults->setTotalCount($collection->getSize());
        } else {
            $searchResults->setTotalCount(count($collection->getItems()));
        }

        return $searchResults;
    }

    /**
     * @param string $attributeSet
     * @return ResourceAbstractEntity\FlatCollection
     */
    protected function _getIndexCollection($attributeSet)
    {
        $tablePostfix = $this->helper->getIndexTablePostfix($attributeSet);

        return $this->abstractEntityFlatCollectionFactory->create(['entityName' => $tablePostfix]);
    }

    /**
     * @param int $attributeSetId
     * @param array $attributes
     * @return ResourceAbstractEntity\Collection
     */
    protected function _getEavCollection($attributeSetId, $attributes)
    {
        $collection = $this->abstractEntityCollectionFactory->create();

        if ($attributeSetId !== null) {
            if ($attributeSetId) {
                $collection->addFieldToFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $attributeSetId);
            }
        }

        if ($attributes !== null) {
            $collection->addAttributeToSelect($attributes);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AbstractEntityInterface $abstractEntity)
    {
        try {
            $abstractEntityId = $abstractEntity->getId();
            $abstractEntity->getResource()->delete($abstractEntity);
            if (isset($this->abstractEntityCache[$abstractEntityId])) {
                unset($this->abstractEntityCache[$abstractEntityId]);
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AbstractEntity: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($abstractEntityId)
    {
        return $this->delete($this->getById($abstractEntityId));
    }

    /**
     * Process images
     * @param \Digidirect\AbstractEntity\Model\AbstractEntity $abstractEntity
     * @return string[]|null
     * @throws LocalizedException
     */
    protected function _processImages($abstractEntity)
    {
        $image = [];
        foreach ($this->getImageHelper()->getImages() as $key) {
            if ($imageFile = $this->getImageHelper()->getImageInfo($abstractEntity, $key)) {
                $result = $this->imageProcessorFactory->create()->save($imageFile);
                //Result can be empty array and it mean that image stay the same
                if (isset($result['error'])) {
                    throw new LocalizedException(__($result['error']));
                } elseif (!empty($result['file'])) {
                    // Remember old image and delete earlier
                    if ($abstractEntity->hasData($key)
                        && ($img = $abstractEntity->getData($key))
                        && $img != $result['file']
                    ) {
                        $image[] = $img;
                    }
                    $abstractEntity->setData($key, $result['file']);
                }
            } else { // It mean that image was deleted or it wasn't uploaded
                if ($img = $abstractEntity->getData($key)) {
                    $image[] = $img;
                }

                $abstractEntity->setData($key, null);
            }
        }
        return $image;
    }

    /**
     * Get image helper
     * @return ImageHelper
     */
    protected function getImageHelper()
    {
        if (null === $this->imageHelper) {
            $this->imageHelper = $this->imageHelperFactory->create();
        }
        return $this->imageHelper;
    }
}
