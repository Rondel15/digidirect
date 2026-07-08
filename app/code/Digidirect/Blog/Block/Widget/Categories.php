<?php

namespace Digidirect\Blog\Block\Widget;

use Digidirect\Blog\Model\Config\Provider\Status;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Categories
 * @package Digidirect\Blog\Block\Widget
 */
class Categories extends AbstractWidget implements BlockInterface
{
    const ITEM_RENDERER = 'Digidirect\Blog\Block\Widget\Categories\Renderer';

    /**
     * @return string
     */
    public function renderTree()
    {
        $collection = $this->categoryRepository->getCategories(Status::STATUS_ENABLED);
        $tree = $this->buildTree($collection);
        return $this->getTreeRenderer()->render($tree);
    }

    /**
     * @param AbstractCollection $collection
     * @param string $parentIdField
     * @return array
     */
    protected function buildTree(AbstractCollection $collection, $parentIdField = 'parent_id')
    {
        $data = $tree = [];

        /** @var AbstractModel $item */
        foreach ($collection as $item) {
            if ($item->hasData($parentIdField)) {
                $item->setData($parentIdField, (int)$item->getData($parentIdField));
                $data[$item->getId()] = $item->getData();
            }
        }

        foreach ($data as $id => &$node) {
            if (isset($node[$parentIdField]) && !$node[$parentIdField]) {
                $tree[$id] = &$node;
            } else {
                if (!isset($data[$node[$parentIdField]])) {
                    $parent = $this->categoryRepository->getById($node[$parentIdField]);
                    $data[$node[$parentIdField]] = $parent->getData();
                }
                $data[$node[$parentIdField]]['children'][$id] = &$node;
            }
        }
        $tree = $this->sortOrder($tree);
        return $tree;
    }

    /**
     * @param array $tree
     * @return array
     */
    public function sortOrder($tree)
    {
        ksort($tree);
        return $tree;
    }

    /**
     * @return \Digidirect\Blog\Block\Widget\Categories\Renderer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTreeRenderer()
    {
        return $this->getLayout()->createBlock(self::ITEM_RENDERER);
    }
}
