<?php

namespace Digidirect\Blog\Block;

use Digidirect\Blog\Api\Data\CategoryInterface;
use Digidirect\Blog\Model\Config\Provider\PageListType;
use Magento\Framework\DataObject\IdentityInterface;

class Category extends Blog implements IdentityInterface
{
    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Post\Collection
     */
    protected function prepareCollection()
    {
        $collection = parent::prepareCollection();
        $collection->addFieldToFilter('category.category_id', $this->getCategory()->getId());
        $this->arrowHelper->setCategoryCondition($this->getCategory()->getId());
        return $collection;
    }

    /**
     * @return \Digidirect\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->registry->registry(CategoryInterface::CURRENT_ITEM);
    }

    /**
     * @return bool
     */
    public function isListPageMode()
    {
        return $this->dataHelper->getDisplaySettingsConfig('cat_page/show_type') == PageListType::TYPE_LIST;
    }

    /**
     * @return bool
     */
    public function isShowCategoryNamesSetting()
    {
        return (bool)$this->dataHelper->getDisplaySettingsConfig('cat_page/show_category_name_oncat');
    }

    /**
     * As it is view page need to have only
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getCategory()->getIdentities();
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
        $cacheKey['current_item'] = $this->getCategory() ? $this->getCategory()->getId() : null;
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        $cacheKey['is_list'] = $this->isListPageMode();
        return $cacheKey;
    }
}
