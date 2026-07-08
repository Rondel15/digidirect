<?php

namespace Digidirect\Blog\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Arrow extends Data
{
    const
        SOURCE_CATEGORY     = 'category',
        SOURCE_ARCHIVE      = 'archive',
        SOURCE_TAG          = 'tag',
        SOURCE_SEARCH       = 'search',
        SOURCE_BREADCRUMB   = 'breadcrumb';

    const CONDITION_KEY = 'blog_arrow_condition';

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Arrow constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param Data $dataHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct
    (
        Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Data $dataHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->redirect = $redirect;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Digidirect\Blog\Model\ResourceModel\Post\Collection $collection
     * @return void
     */
    public function applyCondition(\Digidirect\Blog\Model\ResourceModel\Post\Collection $collection)
    {
        if ($this->storeManager->getStore()->getBaseUrl() == $this->redirect->getRefererUrl()) {
            $this->clearCondition();
        }
        $condition = $this->getCondition();
        if (!empty($condition)) {
            switch ($condition['source']) {
                case self::SOURCE_ARCHIVE:
                    $collection->addFilterByMonth($condition['params']['month'])
                        ->addFilterByYear($condition['params']['year']);
                    break;
                case self::SOURCE_SEARCH:
                    $collection->addFieldToFilter(
                        ['title', 'short_content', 'content'],
                        [
                            ['like' => '%' . $condition['params'] . '%'],
                            ['like' => '%' . $condition['params'] . '%'],
                            ['like' => '%' . $condition['params'] . '%'],
                        ]
                    );
                    break;
                case self::SOURCE_TAG:
                    $collection->addFilterByTagId($condition['params']);
                    break;
                case self::SOURCE_CATEGORY:
                    $collection->addFieldToFilter('category.category_id', $condition['params']);
                    break;
            }
        }
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setCategoryCondition($value)
    {
        $this->setCondition(self::SOURCE_CATEGORY, $value);
        return $this;
    }

    /**
     * @param string $search
     * @return $this
     */
    public function setSearchCondition($search)
    {
        $this->setCondition(self::SOURCE_SEARCH, $search);
        return $this;
    }

    /**
     * @param int $month
     * @param int $year
     * @return $this
     */
    public function setArchiveCondition($month, $year)
    {
        $this->setCondition(self::SOURCE_ARCHIVE, ['month' => $month, 'year' => $year]);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setTagCondition($value)
    {
        $this->setCondition(self::SOURCE_TAG, $value);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setBreadCrumbCondition($value)
    {
        $this->setCondition(self::SOURCE_BREADCRUMB, $value);
        return $this;
    }

    /**
     * @param string $source
     * @param mixed $params
     * @return $this
     */
    public function setCondition($source, $params)
    {
        $this->dataPersistor->set(self::CONDITION_KEY, json_encode(['source' => $source, 'params' => $params]));
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getCondition()
    {
        $condition = $this->dataPersistor->get(self::CONDITION_KEY);
        return !empty($condition) ? json_decode($condition, true) : null;
    }

    /**
     * @return void
     */
    public function clearCondition()
    {
        $this->dataPersistor->clear(self::CONDITION_KEY);
    }

    /**
     * @return bool
     */
    public function isCategoryCondition()
    {
        $condition = $this->getCondition();
        return !empty($condition['source']) && $condition['source'] == self::SOURCE_CATEGORY;
    }

    /**
     * @return null|mixed
     */
    public function getParams()
    {
        $condition = $this->getCondition();
        return !empty($condition['params']) ? $condition['params'] : null;
    }
}
