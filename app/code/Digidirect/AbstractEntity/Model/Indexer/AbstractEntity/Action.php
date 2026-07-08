<?php

namespace Digidirect\AbstractEntity\Model\Indexer\AbstractEntity;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntityIndex;
use Digidirect\AbstractEntity\Helper\Config as ConfigHelper;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\ResourceModel\AttributeLoader;
use Digidirect\AbstractEntity\Model\AbstractEntity as Entity;
use Digidirect\AbstractEntity\Helper\Data as Helper;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Action
 *
 * @package Digidirect\AbstractEntity\Model\Indexer\AbstractEntity
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Action
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AbstractEntityIndex
     */
    protected $abstractEntityIndex;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

    /**
     * @var array
     */
    protected $attributeSetNames = [];

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * Action constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param AbstractEntityIndex $abstractEntityIndex
     * @param ConfigHelper $configHelper
     * @param AttributeSetRepository $attributeSetRepository
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Helper $helper
     * @param StoreManagerInterface $storeManager
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AbstractEntityIndex $abstractEntityIndex,
        ConfigHelper $configHelper,
        AttributeSetRepository $attributeSetRepository,
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Helper $helper,
        StoreManagerInterface $storeManager,
        AbstractEntityRepositoryInterface $abstractEntityRepository
    ) {
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->abstractEntityIndex = $abstractEntityIndex;
        $this->configHelper = $configHelper;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper = $helper;
    }

    /**
     * @param array $ids
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function reindex(array $ids = [])
    {
        if (!$this->configHelper->isEnable()) {
            return $this;
        }

        foreach ($this->storeManager->getStores() as $store) {
            $entities = $this->configHelper->getEntities();
            foreach ($entities as $entity) {
                $aeCollection = $this->collectionFactory->create();
                $aeCollection->setStoreId($store->getId());
                $aeCollection->setStore($store);
                $aeCollection->setFlag('reindex', true);
                $attributes = $this->_getAttributesBySetId($entity);
                $entityName = $this->_getAttributeSetName($entity);
                $aeCollection
                    ->addAttributeToSelect(AbstractEntityInterface::NAME, true)
                    ->addAttributeToSelect(AbstractEntityInterface::URL_KEY, true)
                    ->addAttributeToSelect($attributes, true)
                    ->addFieldToFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, ['in' => $entity]);
                $this->abstractEntityIndex->createTableFromSelect(
                    $aeCollection->getSelect(),
                    $entityName,
                    (int)$store->getId()
                );
            }
        }
        return $this;
    }

    /**
     * @param int $id
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function _getAttributesBySetId($id)
    {
        $attr = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeLoader::ATTRIBUTE_SET_ID, $id)
            ->addFilter(Attribute::KEY_USE_IN_INDEX_TABLE, Attribute::USE_IN_INDEX_TABLE_ENABLE)
            ->create();
        $attributes = $this->attributeRepository->getList(Entity::ENTITY_TYPE, $searchCriteria)->getItems();
        foreach ($attributes as $attribute) {
            $attr[] = $attribute->getData('attribute_code');
        }

        return $attr;
    }

    /**
     * @param int $id
     * @return mixed
     */
    protected function _getAttributeSetName($id)
    {
        if (isset($this->_getAttributeSetNames()[$id])) {
            return $this->_getAttributeSetNames()[$id];
        }

        return $id;
    }

    /**
     * @return array
     */
    protected function _getAttributeSetNames()
    {
        if (!$this->attributeSetNames) {
            foreach ($this->attributeSetRepository->getList()->getItems() as $attributeSet) {
                $this->attributeSetNames[$attributeSet->getAttributeSetId()] = $attributeSet->getAttributeSetName();
            }
        }

        return $this->attributeSetNames;
    }

    /**
     * @param AbstractEntityInterface $ae
     * @param array $arguments
     * @return $this
     */
    public function reindexEntityInfo(AbstractEntityInterface $ae, $arguments = [])
    {
        $storeId = $ae->getStoreId();

        $reindex = function (AbstractEntityInterface $ae, $storeId) use ($arguments) {
            $entityName = $this->_getAttributeSetName($ae->getAttributeSetId());
            $this->abstractEntityIndex->updateEntityIndexData($ae, $entityName, $storeId, $arguments);
        };

        /**
         * As AE is being saved through entity manager actual information is already in db
         * So we get all stores and load entity by store then save in flat tables
         */
        if (Store::DEFAULT_STORE_ID == $storeId) {
            foreach ($this->storeManager->getStores() as $store) {
                $storeId = (int)$store->getId();
                $ae = $this->abstractEntityRepository->getById($ae->getId(), $storeId, true);
                $reindex($ae, $storeId);
            }
        } else {
            $reindex($ae, $storeId);
        }

        return $this;
    }
}
