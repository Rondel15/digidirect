<?php
namespace Digidirect\CategoryFilter\Block;

use Magento\Catalog\Block\Category\View as CategoryView;

class Category extends CategoryView
{
    /**
     * Get category Url
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->_categoryHelper->getCategoryUrl($category);
    }

    /**
     * Check if need to show "%Parent category name% all"
     * @return bool
     */
    public function showParentLink()
    {
        $currentCategory = $this->getCurrentCategory();
        return $currentCategory->getParentCategory() && $currentCategory->getParentCategory()->getLevel() != 1;
    }

    /**
     * Get children categories
     * @return array
     */
    public function getChildrenCategories()
    {
        $childrenCollection = [];
        $category = $this->getCurrentCategory();
        if ($category->hasChildren()) {
            $childrenCollection = $category->getChildrenCategories();
        }
        return $childrenCollection;
    }
}
