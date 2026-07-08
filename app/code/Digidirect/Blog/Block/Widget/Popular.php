<?php

namespace Digidirect\Blog\Block\Widget;

use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\ResourceModel\Post\Collection;

class Popular extends AbstractWidget
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
            $collection->setOrder('views', 'DESC');
            $this->setData('collection', $collection);
            $this->categoryHelper->prepareCategoriesUrls();
            $count = $this->getCount();
            if (!empty($count)) {
                $collection->setPageSize($count);
            }
        }
        return $this->getData('collection');
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
}
