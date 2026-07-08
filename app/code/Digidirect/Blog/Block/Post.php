<?php

namespace Digidirect\Blog\Block;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Api\CategoryRepositoryInterface;
use Digidirect\Blog\Api\TagRepositoryInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\ResourceModel\Tag\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Post extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var PostRepositoryInterface
     */
    protected $repository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * Post constructor.
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param FilterProvider $filterProvider
     * @param PostRepositoryInterface $postRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TagRepositoryInterface $tagRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        Data $helper,
        FilterProvider $filterProvider,
        PostRepositoryInterface $postRepository,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        $this->repository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return \Digidirect\Blog\Model\Post
     */
    public function getPost()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @return string
     */
    public function getViewImage()
    {
        return $this->getViewFileUrl('Digidirect_Blog::images/views.png');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $initial_description = $this->filterProvider->getBlockFilter()->filter($this->getPost()->getContent());
        
        $initial_description = str_replace("ÔÇØ", "'", $initial_description);
        $initial_description = str_replace("ÔÇ£", "'", $initial_description);
        $initial_description = str_replace("ÔÇÖ", "'", $initial_description);
        $initial_description = str_replace("ÔÇÿ", "'", $initial_description);
        $initial_description = str_replace("ÔÇô", "'", $initial_description);
        $description         = str_replace("ÔÇª", ".", $initial_description);

        return $description;
    }

    /**
     * @return bool
     */
    public function isDisplayViews()
    {
        return $this->helper->getDisplaySettingsConfig('display_settings/display_views');
    }

    /**
     * @return bool
     */
    public function isDisplayShare()
    {
        return $this->helper->getDisplaySettingsConfig('display_settings/display_share');
    }

    /**
     * @return bool
     */
    public function isShareAbove()
    {
        return $this->helper->getDisplaySettingsConfig('display_settings/share_above');
    }

    /**
     * @return bool
     */
    public function isShareBelow()
    {
        return $this->helper->getDisplaySettingsConfig('display_settings/share_below');
    }

    /**
     * @return bool
     */
    public function isDisplayCategories()
    {
        return $this->helper->getDisplaySettingsConfig('view_page/show_category_name_onpost');
    }

    /**
     * @return bool
     */
    public function isDisplayTags()
    {
        return $this->helper->getDisplaySettingsConfig('display_settings/display_tags');
    }

    /**
     * @return Collection
     */
    public function getTags()
    {
        if (!$this->hasData('post_tags')) {
            $tags = $this->tagRepository->getTagsByPostId($this->getPost()->getId());
            $this->setData('post_tags', $tags);
        }
        return $this->getData('post_tags');
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        if (!$this->hasData('post_categories')) {
            $categories = $this->categoryRepository->getCategoriesByPostId($this->getPost()->getId(), true);
            $this->setData('post_categories', $categories);
        }
        return $this->getData('post_categories');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return $this->getPost()->getIdentities();
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
        $cacheKey['current_item'] = $this->getPost() ? $this->getPost()->getId() : null;
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        return $cacheKey;
    }
}
