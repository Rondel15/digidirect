<?php

namespace Digidirect\Utilities\Model\Catalog\ResourceModel\Category;

/**
 * Class Tree
 * @package Digidirect\Utilities\Model\Catalog\ResourceModel\Category
 */
class Tree extends \Magento\Catalog\Model\ResourceModel\Category\Tree
{
    /**
     * Load whole category tree, that will include specified categories ids.
     * Removed limitation about category level to allow derive whole tree
     *
     * @param array $ids
     * @param bool $addCollectionData
     * @param bool $updateAnchorProductCount
     * @return $this|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadByIds($ids, $addCollectionData = true, $updateAnchorProductCount = true)
    {
        $levelField = $this->_conn->quoteIdentifier('level');
        $pathField = $this->_conn->quoteIdentifier('path');
        if (empty($ids)) {
            $select = $this->_conn->select()->from($this->_table, 'entity_id');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn->select()->from($this->_table, ['path', 'level'])->where('entity_id IN (?)', $ids);
        $where = [$levelField . '=1' => true];

        foreach ($this->_conn->fetchAll($select) as $item) {
            $pathIds = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                $pathIds[count($pathIds) - 1] = '%';
                $path = implode('/', $pathIds);
                $where["{$levelField}={$level} AND {$pathField} LIKE '{$path}'"] = true;
                array_pop($pathIds);
                $level--;
            }
        }
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        } else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ' . \Magento\Framework\DB\Select::SQL_ASC);
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        if ($updateAnchorProductCount) {
            $this->_updateAnchorProductCount($arrNodes);
        }
        $childrenItems = [];
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '1', null);
        return $this;
    }

    /**
     * Load child tree
     * @param \Magento\Catalog\Model\Category $category
     * @param int $level
     * @param bool $onlyLevel
     * @return $this|bool
     */
    public function loadChildTree($category, $level = 0, $onlyLevel = false)
    {
        if (!$level && $onlyLevel) {       // show only menu title
            return false;
        }

        $select = $this->_createCollectionDataSelect();
        $select = $this->_addLevelToSelect($select, $level, $category, $onlyLevel);
        $ids = $this->_getIds($category);
        $select->where('e.entity_id IN (?)', $ids);

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }

        $childrenItems = [];
        foreach ($arrNodes as $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->_setChildrenItems($onlyLevel, $childrenItems, $category);
        return $this;
    }

    /**
     * Add new level to select
     * @param \Magento\Framework\DB\Select $select
     * @param int $level
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $onlyLevel
     * @return string
     */
    protected function _addLevelToSelect($select, $level, $category, $onlyLevel)
    {
        $levelField = $this->_conn->quoteIdentifier('level');
        if ($level) {
            $whereLevel = $category->getLevel() + $level;
            if ($onlyLevel) {
                $where = $levelField . ' = ' . $whereLevel;
            } else {
                $where = $levelField . ' < ' . $whereLevel;
            }
            $select->where($where);
        }
        return $select;
    }

    /**
     * Set children items
     * @param bool $onlyLevel
     * @param [] $childrenItems
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _setChildrenItems($onlyLevel, $childrenItems, $category)
    {
        if ($onlyLevel) {
            foreach ($childrenItems as $path => $items) {
                $this->addChildNodes($childrenItems, $path, null);
            }
        } else {
            $this->addChildNodes($childrenItems, $category->getPath(), null);
        }
        return $this;
    }

    /**
     * Get category children ids
     * @param \Magento\Catalog\Model\Category $category
     * @return []
     */
    protected function _getIds($category)
    {
        $ids = $category->getAllChildren(true);
        if (empty($ids)) {
            $select = $this->_conn->select()->from($this->_table, 'entity_id');
            $ids = $this->_conn->fetchCol($select);
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        return $ids;
    }
}
