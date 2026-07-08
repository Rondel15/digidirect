<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Api\Data\TagInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Tag extends \Magento\Framework\Model\AbstractModel implements TagInterface, IdentityInterface
{
    const CACHE_PREFIX = 'blog_tag';

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var IdentitiesGenerator
     */
    protected $identitiesGenerator;

    /**
     * Tag constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Tag $resource
     * @param ResourceModel\Tag\Collection $resourceCollection
     * @param IdentitiesGenerator $identitiesGenerator
     * @param UrlModel $urlModel
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\Blog\Model\ResourceModel\Tag $resource,
        \Digidirect\Blog\Model\ResourceModel\Tag\Collection $resourceCollection,
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
     * Get tag url page
     *
     * @return string
     */
    public function getUrl()
    {
        return rtrim($this->urlModel->getTagUrl($this->getName()), '/');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->identitiesGenerator->getIdentities($this, self::CACHE_PREFIX);
    }
}
