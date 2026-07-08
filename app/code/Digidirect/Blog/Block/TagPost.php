<?php

namespace Digidirect\Blog\Block;

use Digidirect\Blog\Api\Data\TagInterface;
use Digidirect\Blog\Model\Tag;
use Magento\Framework\DataObject\IdentityInterface;

class TagPost extends Blog implements IdentityInterface
{
    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Post\Collection
     */
    protected function prepareCollection()
    {
        $this->logger->info("this->getTag()->getId() " . $this->getTag()->getId());
        $this->logger->info("TagInterface::CURRENT_ITEM " . TagInterface::CURRENT_ITEM);
        $this->logger->info("this->getTag()->getName() " . $this->getTag()->getName());
        //$this->logger->info("json_decode(this->getTag()) " . json_decode($this->getTag()));
        
        $collection = parent::prepareCollection();
        //
        $collection->addFilterByTagId($this->getTag()->getId());
        $this->arrowHelper->setTagCondition($this->getTag()->getId());
        return $collection;
    }

    /**
     * @return Tag
     */
    public function getTag()
    {
        return $this->registry->registry(TagInterface::CURRENT_ITEM);
    }

    /**
     * @return bool
     */
    public function isShowCategoryNamesSetting()
    {
        return (bool)$this->dataHelper->getDisplaySettingsConfig('cat_page/show_category_name_oncat');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->getTag()->getIdentities();
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
        $cacheKey['current_item'] = $this->getTag() ? $this->getTag()->getUrl() : null;
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        return $cacheKey;
    }
}
