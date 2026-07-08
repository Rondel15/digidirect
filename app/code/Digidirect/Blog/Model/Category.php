<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\CategoryInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Category
 */
class Category extends \Magento\Framework\Model\AbstractModel implements CategoryInterface, IdentityInterface
{
    const CACHE_TAG_PREFIX = 'blog_category';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_blog_category';

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var IdentitiesGenerator
     */
    protected $identitiesGenerator;

    /**
     * Category constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Category $resource
     * @param ResourceModel\Category\Collection $resourceCollection
     * @param IdentitiesGenerator $identitiesGenerator
     * @param UrlModel $urlModel
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\Blog\Model\ResourceModel\Category $resource,
        \Digidirect\Blog\Model\ResourceModel\Category\Collection $resourceCollection,
        IdentitiesGenerator $identitiesGenerator,
        UrlModel $urlModel
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->identitiesGenerator = $identitiesGenerator;
        $this->urlModel = $urlModel;
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return rtrim($this->urlModel->getCategoryUrl($this->getUrlKey()), '/');
    }

    /**
     * Get Category URL path without domain
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getViewUrlPath()
    {
        return $this->urlModel->getCategoryUrlPath($this->getUrlKey());
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->identitiesGenerator->getIdentities($this, self::CACHE_TAG_PREFIX);
    }

    /**
     * @return mixed
     */
    public function getViewStores()
    {
        return $this->_resource->lookupStoreIds($this->getId());
    }
}
