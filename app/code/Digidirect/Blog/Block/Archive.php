<?php

namespace Digidirect\Blog\Block;

/**
 * Class Archive
 */
class Archive extends Blog
{
    const CURRENT_MONTH = 'current_blog_archive_month';

    const CURRENT_YEAR = 'current_blog_archive_year';

    /**
     * @return int
     */
    public function getMonth()
    {
        return (int)$this->registry->registry(self::CURRENT_MONTH);
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return (int)$this->registry->registry(self::CURRENT_YEAR);
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Post\Collection
     */
    protected function prepareCollection()
    {
        $collection = parent::prepareCollection();
        $collection->addFilterByMonth($this->getMonth())->addFilterByYear($this->getYear());
        $this->arrowHelper->setArchiveCondition($this->getMonth(), $this->getYear());
        return $collection;
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
        $cacheKey['current_item'] = $this->getMonth() . $this->getYear();
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        return $cacheKey;
    }
}
