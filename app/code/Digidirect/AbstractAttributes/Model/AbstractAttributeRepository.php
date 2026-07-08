<?php
namespace Digidirect\AbstractAttributes\Model;

use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class AbstractAttributeRepository
 * @package Digidirect\AbstractAttributes\Model
 */
class AbstractAttributeRepository implements \Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface
{
    use AbstractRepositoryTrait;

    /**
     * @var AbstractAttributeFactory
     */
    protected $abstractAttributeFactory;

    /**
     * @var \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var ResourceModel\AbstractAttribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModel\AbstractAttribute
     */
    protected $resourceModel;

    /**
     * AbstractAttributeRepository constructor.
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterfaceFactory $abstractAttributeFactory
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param ResourceModel\AbstractAttribute $resourceModel
     * @param ResourceModel\AbstractAttribute\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterfaceFactory $abstractAttributeFactory,
        \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeSearchResultsInterfaceFactory $searchResultsFactory,
        ResourceModel\AbstractAttribute $resourceModel,
        ResourceModel\AbstractAttribute\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->abstractAttributeFactory = $abstractAttributeFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, $forceReload = false)
    {
        if (!isset($this->instances[$id]) || $forceReload) {
            /** @var \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute */
            $abstractAttribute = $this->abstractAttributeFactory->create();
            $this->resourceModel->load($abstractAttribute, $id);
            if (!$abstractAttribute->getId()) {
                throw new NoSuchEntityException(__('Requested abstract attribute doesn\'t exist'));
            }
            $this->instances[$id] = $abstractAttribute;
        }
        return $this->instances[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getByAttributeId($attributeId, $storeId = null, $forceReload = false)
    {
        $cacheKey = $attributeId . '_' . $storeId;
        if (!isset($this->instances[$cacheKey]) || $forceReload) {
            /** @var \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute */
            $abstractAttribute = $this->abstractAttributeFactory->create();
            if ($storeId !== null) {
                $abstractAttribute->setStoreId($storeId);
            }
            $this->resourceModel->load($abstractAttribute, $attributeId, AbstractAttributeInterface::ATTRIBUTE_ID);
            if (!$abstractAttribute->getAttributeId()) {
                throw new NoSuchEntityException(__('Requested abstract attribute doesn\'t exist'));
            }
            if ($storeId !== null) {
                $abstractAttribute->setAttributeStoreId($storeId);
            }
            $this->instances[$cacheKey] = $abstractAttribute;
        }
        return $this->instances[$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute)
    {
        $storeId = $abstractAttribute->getStoreId();
        if ($id = $abstractAttribute->getAttributeId()) {
            try {
                $data = $abstractAttribute->getData();
                $abstractAttribute = $this->getByAttributeId($id, $storeId, true);
                if ($abstractAttribute->getStoreId() != $storeId) {
                    $abstractAttribute->isObjectNew(true);
                }
                $abstractAttribute->addData($data);
            } catch (NoSuchEntityException $e) {
            }
        }

        try {
            $this->_processUrlKey($abstractAttribute);
            $this->resourceModel->save($abstractAttribute);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save abstract attribute'));
        }

        return $this->get($abstractAttribute->getId(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute)
    {
        $id = $abstractAttribute->getAttributeId();

        try {
            unset($this->instances[$id]);
            $this->resourceModel->delete($abstractAttribute);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove abstract attribute ID%1', $id)
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($abstractAttributeId)
    {
        $abstractAttribute = $this->get($abstractAttributeId);
        return $this->delete($abstractAttribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbstractAttributes($status = null, $storeId = null)
    {
        $collection = $this->getCollection();
        if (is_bool($status)) {
            $collection->addFilter(AbstractAttributeInterface::STATUS, $status);
        }

        if ($storeId !== null) {
            $collection->addStoreFilter($storeId);
        }

        $collection->addOrder(AbstractAttributeInterface::ATTRIBUTED_LABEL, Collection::SORT_ORDER_ASC);
        return $collection->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->getCollection();

        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->_addFilterGroupToCollection($group, $collection);
        }

        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Get collection
     * @return ResourceModel\AbstractAttribute\Collection
     */
    protected function getCollection()
    {
        /** @var ResourceModel\AbstractAttribute\Collection $collection */
        $collection = $this->collectionFactory->create();
        return $collection;
    }

    /**
     * Validate url key
     * @param string $urlKey
     * @return true If url key is valid
     * @throws ValidatorException
     */
    protected function _validateUrlKey($urlKey)
    {
        return true;
    }
}
