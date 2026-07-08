<?php

namespace Digidirect\StoreLocator\Model;

use Digidirect\StoreLocator\Data\ProcessorConstants;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\StoreLocator\Helper\AeIndex;
use Digidirect\StoreLocator\Helper\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;

class EntitiesSorter
{
    const CHILD_ITEMS_KEY = 'child_items';
    const ENTITY_TYPE_KEY = 'entity_type';
    const AE_MAIN_TABLE = 'digidirect_abstractentity_entity';
    const STATUS_ACTIVE = 1;

    /**
     * @var AbstractEntityResource
     */
    protected $aeResource;

    /**
     * @var AeIndex
     */
    protected $aeIndexHelper;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * EntitiesSorter constructor.
     * @param AbstractEntityResource $aeResource
     * @param AeIndex $aeIndexHelper
     * @param Config $configHelper
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AbstractEntityResource $aeResource,
        AeIndex $aeIndexHelper,
        Config $configHelper,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->aeResource = $aeResource;
        $this->aeIndexHelper = $aeIndexHelper;
        $this->configHelper = $configHelper;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $entities
     * @return array
     */
    public function sort(array $entities)
    {
        $items = [];
        $parentChilds = [];
        foreach ($entities as $entityName => $entityData) {
            if (!empty($entityData[ProcessorConstants::ITEMS])) {
                foreach ($entityData[ProcessorConstants::ITEMS] as $entity) {
                    $entity[self::ENTITY_TYPE_KEY] = $entityName;
                    $items[$entity[AbstractEntityInterface::ENTITY_ID]] = $entity;
                    if (!empty($entity[AbstractEntityInterface::PARENT_ID])) {
                        $key = $entity[AbstractEntityInterface::PARENT_ID];
                        $parentChilds[$key][] = $entity[AbstractEntityInterface::ENTITY_ID];
                    }
                }
                $entities[$entityName][ProcessorConstants::ITEMS] = [];
            }
        }

        /**
         * Find parents wich weren't loaded, but associated with found children
         */
        $parentIdsToLoad = array_diff(array_keys($parentChilds), array_keys($items));
        $parents = $this->getGroupedEntitiesFromIndexTableByIds(
            $parentIdsToLoad,
            AbstractEntityInterface::ENTITY_ID
        );
        $items += $parents;

        /**
         * if set up to "yes" all children for parent will be added to result
         * if set up to "no" only children which was found will be added to result
         */
        if ($this->configHelper->isLoadAllChildrenForParent()) {
            $chidrenIdsToLoad = array_merge(array_keys($items), array_keys($parentChilds));
        } else {
            $chidrenIdsToLoad = array_diff(array_keys($items), array_keys($parentChilds));
        }

        /**
         * Find chidlren for parents which don't contanin children or all children which belong to parent, it depneds on
         * isLoadAllChildrenForParent setting
         */
        $chidrenForEmptyParents = $this->getGroupedEntitiesFromIndexTableByIds(
            $chidrenIdsToLoad,
            AbstractEntityInterface::PARENT_ID
        );
        $items += $chidrenForEmptyParents;
        $items = $this->buildTree($items);
        foreach ($items as $item) {
            if (!isset($entities[$item[self::ENTITY_TYPE_KEY]])) {
                $entities[$item[self::ENTITY_TYPE_KEY]] = [
                    ProcessorConstants::ITEMS => [],
                    ProcessorConstants::SETTINGS => []
                ];
            }
            $entities[$item[self::ENTITY_TYPE_KEY]][ProcessorConstants::ITEMS][] = $item;
        }
        return $entities;
    }

    /**
     * @param array $ids
     * @param string $conditionField
     * @return array
     */
    protected function getGroupedEntitiesFromIndexTableByIds(array $ids, $conditionField)
    {
        $result = [];
        if (!empty($ids)) {
            $entities = $this->loadEntitiesByIds(self::AE_MAIN_TABLE, $conditionField, $ids);
            $groupBySet = [];

            foreach ($entities as $item) {
                $key = $item[AbstractEntityInterface::ATTRIBUTE_SET_ID];
                $groupBySet[$key][] = $item[AbstractEntityInterface::ENTITY_ID];
            }
            $attributeSetList = $this->loadAttribteSetListByIds(array_keys($groupBySet));
            foreach ($groupBySet as $attributeSetId => $entityIds) {
                $attributeSet = $attributeSetList[$attributeSetId] ?? null;
                if ($attributeSet) {
                    $table = $this->aeIndexHelper->getExistsTableNameByAttributeSetName(
                        $attributeSet->getAttributeSetName()
                    );
                    if ($table) {
                        $entities = $this->loadEntitiesByIds(
                            $table,
                            AbstractEntityInterface::ENTITY_ID,
                            $entityIds,
                            self::STATUS_ACTIVE
                        );
                        if (!empty($entities)) {
                            $entities = $this->addEntityType($attributeSet->getAttributeSetName(), $entities);
                            $result = array_merge(
                                $result,
                                $entities
                            );
                        }
                    }
                }
            }
        }

        return $this->groupByEntityId($result);
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function loadAttribteSetListByIds(array $ids)
    {
        $result = [];
        $asResult = $this->attributeSetRepository->getList(
            $this->searchCriteriaBuilder->addFilter(
                'attribute_set_id',
                $ids,
                'in')
                ->create()
        );
        foreach ($asResult->getItems() as $aeItem) {
            $result[$aeItem->getAttributeSetId()] = $aeItem;
        }
        return $result;
    }

    /**
     * @param string $table
     * @param string $conditionField
     * @param array $ids
     * @param null|int $status
     * @return array
     */
    protected function loadEntitiesByIds($table, $conditionField, array $ids, $status = null)
    {
        $select = $this->aeResource
            ->getConnection()
            ->select()
            ->from($this->aeResource->getTable($table))
            ->where($conditionField . ' IN (?)', $ids);

        if ($status !== null) {
            $select->where(AbstractEntityInterface::STATUS . ' = ?', $status);
        }
        return $this->aeResource->getConnection()->fetchAll($select);
    }

    /**
     * @param $attributeSetName
     * @param array $items
     * @return array
     */
    protected function addEntityType($attributeSetName, array $items)
    {
        if (!empty($items)) {
            $entityName = $this->getEntityTypeName($attributeSetName);
            foreach ($items as $key => $item) {
                $item[self::ENTITY_TYPE_KEY] = $entityName;
                $items[$key] = $item;
            }
        }
        return $items;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function groupByEntityId(array $items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[$item[AbstractEntityInterface::ENTITY_ID]] = $item;
        }
        return $result;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function buildTree(array $items)
    {
        $result = [];
        foreach ($items as $id => &$node) {
            if (empty($node[AbstractEntityInterface::PARENT_ID])
                || !isset($items[$node[AbstractEntityInterface::PARENT_ID]])
            ) {
                $result[$id] = &$node;
            } else {
                $items[$node[AbstractEntityInterface::PARENT_ID]][self::CHILD_ITEMS_KEY][$id] = &$node;
            }
        }
        return $result;
    }

    /**
     * @param string $attributeSetName
     * @return string|string[]|null
     */
    protected function getEntityTypeName($attributeSetName)
    {
        return \preg_replace(
            '![^\w\d\s]*!',
            '',
            \str_replace(' ', '_', \mb_strtolower($attributeSetName))
        );
    }
}
