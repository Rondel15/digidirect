<?php

namespace Digidirect\Blog\Block\Widget;

use Digidirect\Blog\Model\Category\Source\BlogCategoriesDisplayType;
use Digidirect\Blog\Model\Config\Provider\Status;
use Magento\Framework\DataObject;

/**
 * Class BlogCategories
 * @package Digidirect\Blog\Block\Widget
 */
class BlogCategories extends Categories
{
    const CATEGORY_IDS = 'category_ids';
    const BLOG_CATEGOIES_DISPLAY_TYPE = 'blog_categories_display_type';
    const SHOW_ALL_CATERORY = 'show_all_category';
    const WIDGET_TEMPLATE = 'widget_template';
    const CUSTOM_TEMPLATE = 'custom_template';
    const WIDGET_TEMPLATE_PATH = 'Digidirect_Blog::widget/categories/';

    /**
     * @var string
     */
    protected $_template = self::WIDGET_TEMPLATE_PATH. 'default.phtml';

    /**
     * @return mixed
     */
    public function getCategoriesDispayType()
    {
        return $this->getData(static::BLOG_CATEGOIES_DISPLAY_TYPE);
    }

    /**
     * @return mixed
     */
    public function showAllCategory()
    {
        return $this->getData(static::SHOW_ALL_CATERORY);
    }

    /**
     * @return array
     */
    public function getCategoriesTree()
    {
        $collection = $this->getCollection();
        $tree = $this->buildTree($collection);
        return $tree;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        $categoriesDisplayType = $this->getCategoriesDispayType();
        if (!$this->hasData('category_collection')) {
            $collection = $this->categoryRepository->getCategories(Status::STATUS_ENABLED);
            if ($categoriesDisplayType === BlogCategoriesDisplayType::SPECIFIED_CATEGORIES_OPTION_VALUE &&
                $categoryIds = $this->getData(static::CATEGORY_IDS)
            ) {
                $selectedCategoryIds = explode(',', $categoryIds);
                $collection->addCategoryIdsFilter($selectedCategoryIds);
            }
            $this->setData('category_collection', $collection);
        }
        return $this->getData('category_collection');
    }

    /**
     * @param array $tree
     * @return array
     */
    public function sortOrder($tree)
    {
        $tree = parent::sortOrder($tree);
        $categoriesDisplayType = $this->getCategoriesDispayType();
        if ($categoriesDisplayType &&
            $categoriesDisplayType === BlogCategoriesDisplayType::SPECIFIED_CATEGORIES_OPTION_VALUE &&
            $categoryIds = $this->getData(static::CATEGORY_IDS)) {
            $treeOrder = explode(',', $categoryIds);
            $tree = $this->treeSorting($tree, $treeOrder);
        }

        return $tree;
    }

    /**
     * Recursive function to blog category sorting
     *
     * Note: The category list can have categories that are missing in widget option "category_ids",
     * i.e. it can be some parents categories
     *
     * As a result, we don't know the exact position of such categories,
     * therefore, the position of such category will be next for the
     * previous category with the existing position.
     * Solution is an increase of the position by 0.1
     *
     * @param array $tree
     * @param array $treeOrder
     * @return array
     */
    protected function treeSorting(array $tree, array $treeOrder)
    {
        $sortedTree = [];
        $lastKey = -1;
        foreach ($tree as $id => $node) {
            if (isset($node['children']) && !empty($node['children'])) {
                $node['children'] = $this->treeSorting($node['children'], $treeOrder);
            }
            $key = array_search($id, $treeOrder);
            if ($key !== false) {
                $sortedTree[$key] = $node;
                $lastKey = $key;
            } else {
                $sortedTree[(string)($lastKey + 0.1)] = $node;
            }
        }
        ksort($sortedTree);

        return $sortedTree;
    }

    /**
     * @return string
     */
    public function getBlogUrl()
    {
        return $this->urlModel->getBlogListUrl();
    }

    /**
     * @param string $urlKey
     * @return string
     */
    public function getCategoryUrl($urlKey)
    {
        return $this->urlModel->getCategoryUrl($urlKey);
    }

    /**
     * @param int $categoryId
     * @return string
     */
    public function getCountPosts($categoryId)
    {
        return $this->categoryRepository->getCountPostsByCategoryId(
            $categoryId,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    public function isActive($categoryId)
    {
        return $this->getTreeRenderer()->isActive($categoryId);
    }

    /**
     * @param DataObject $category
     * @return mixed
     */
    public function isEnabled($category)
    {
        return $category->getStatus();
    }

    /**
     * @param DataObject $category
     * @return bool
     */
    public function showCategory($category)
    {
        if (!is_object($category)) {
            $category = new DataObject($category);
        }

        if ($category->hasChildren()) {
            $show = false;
            foreach ($category->getChildren() as $child) {
                if ($this->showCategory($child)) {
                    $show = true;
                }
            }
            return $show;
        }
        if ($this->isEnabled($category) && $this->getCountPosts($category->getEntityId()) == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param array $category
     * @return DataObject
     */
    public function getCategoryObject($category)
    {
        $category = new DataObject($category);
        $category->setCountPosts($this->getCountPosts($category->getEntityId()));
        return $category;
    }

    /**
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        $cacheLifetime = parent::getCacheLifetime();
        if (!$cacheLifetime) {
            $cacheLifetime = 86400;
        }

        return $cacheLifetime;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        return $cacheKey;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $widgetTemplate = $this->getData(self::WIDGET_TEMPLATE);
        $customTemplate = trim($this->getData(self::CUSTOM_TEMPLATE));
        if ($customTemplate && $widgetTemplate == 'custom') {
            $templatePath = $this->getData(self::CUSTOM_TEMPLATE) . '.phtml';
            $this->setTemplate(self::WIDGET_TEMPLATE_PATH . $templatePath);
        }
        return parent::_toHtml();
    }
}
