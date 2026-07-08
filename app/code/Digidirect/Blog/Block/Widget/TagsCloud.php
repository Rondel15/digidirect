<?php
namespace Digidirect\Blog\Block\Widget;

use Digidirect\Blog\Api\TagRepositoryInterface;
use Digidirect\Blog\Api\Data\TagInterface;

class TagsCloud extends \Magento\Framework\View\Element\Template
{
    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * TagsCloud constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param TagRepositoryInterface $tagRepository
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        TagRepositoryInterface $tagRepository
    ) {
        parent::__construct($context);
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Tag\Collection
     */
    public function getTags()
    {
        if (!$this->hasData('tags_collection')) {
            $collection = $this->tagRepository->getRandomTags($this->_storeManager->getStore()->getId());
            $this->setData('tags_collection', $collection);
        }
        return $this->getData('tags_collection');
    }

    /**
     * @return int
     */
    public function hasTags()
    {
        return $this->getTags()->getSize();
    }

    /**
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        $cacheLifetime = parent::getCacheLifetime();
        if (!$cacheLifetime) {
            $cacheLifetime = 8640;
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
        $cacheKey['tag'] = $this->getRequest()->getParam('tag', '');
        return $cacheKey;
    }

    /**
     * @param TagInterface $tag
     *
     * @return bool
     */
    public function isCurrentTag(TagInterface $tag)
    {
        return strtolower($tag->getName()) == strtolower(urldecode($this->getRequest()->getParam('tag')));
    }
}
