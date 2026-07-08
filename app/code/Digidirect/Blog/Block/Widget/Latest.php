<?php

namespace Digidirect\Blog\Block\Widget;

use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\Post\Source\DisplayType;
use Digidirect\Blog\Model\ResourceModel\Post\Collection;
use Magento\Framework\DB\Select;
use Magento\Widget\Block\BlockInterface;

class Latest extends AbstractWidget implements BlockInterface
{
    /**
     * @return Collection
     */
    public function getCollection()
    {
        if (!$this->hasData('collection')) {
            $collection = $this->postRepository->getPostList(
                Status::STATUS_ENABLED,
                $this->_storeManager->getStore()->getId(),
                date('Y-m-d')
            );
            $collection->setOrder('publish_date', 'DESC');

            $postIds = '';
            $postsDisplayType = $this->getPostsDisplayType();
            if ($postsDisplayType &&
                $postsDisplayType === DisplayType::SPECIFIED_POSTS_OPTION_VALUE &&
                $postIds = $this->getPostIds()
            ) {
                $selectedPostIds = explode(',', $postIds);
                $collection->addPostIdsFilter($selectedPostIds);
            } else {
                $count = $this->getCount();
                if (!empty($count)) {
                    $collection->setPageSize($count);
                }
            }

            if (!empty($postIds)) {
                $collection->getSelect()->reset(Select::ORDER);
                $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(main_table.entity_id, ' . $postIds . ')'));
            }

            $this->setData('collection', $collection);
            $this->categoryHelper->prepareCategoriesUrls();
        }

        return $this->getData('collection');
    }

    /**
     * Show Post's Category Name option
     * @return mixed
     */
    public function isCategoriesInclude()
    {
        return $this->getShowCategoryName();
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
        $cacheKey['is_show_category_name'] = $this->isCategoriesInclude();
        return $cacheKey;
    }
}
