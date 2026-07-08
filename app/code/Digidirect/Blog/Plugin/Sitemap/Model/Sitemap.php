<?php
namespace Digidirect\Blog\Plugin\Sitemap\Model;

use Digidirect\Blog\Api\CategoryRepositoryInterfaceFactory;
use Digidirect\Blog\Api\PostRepositoryInterfaceFactory;
use Digidirect\Blog\Helper\Sitemap as SitemapHelper;
use Digidirect\Blog\Model\Config\Provider\Status;
use Magento\Framework\DataObject;

/**
 * Class Sitemap
 * @package Digidirect\Blog\Plugin\Sitemap\Model
 */
class Sitemap
{
    /**
     * @var SitemapHelper
     */
    protected $sitemapHelper;

    /**
     * @var CategoryRepositoryInterfaceFactory
     */
    protected $categoryRepositoryInterfaceFactory;

    /**
     * @var PostRepositoryInterfaceFactory
     */
    protected $postRepositoryInterfaceFactory;

    /**
     * Sitemap constructor.
     * @param SitemapHelper $sitemapHelper
     * @param CategoryRepositoryInterfaceFactory $categoryRepositoryInterfaceFactory
     * @param PostRepositoryInterfaceFactory $postRepositoryInterfaceFactory
     */
    public function __construct(
        SitemapHelper $sitemapHelper,
        CategoryRepositoryInterfaceFactory $categoryRepositoryInterfaceFactory,
        PostRepositoryInterfaceFactory $postRepositoryInterfaceFactory
    ) {
        $this->sitemapHelper = $sitemapHelper;
        $this->categoryRepositoryInterfaceFactory = $categoryRepositoryInterfaceFactory;
        $this->postRepositoryInterfaceFactory = $postRepositoryInterfaceFactory;
    }

    /**
     * @param \Digidirect\Utilities\Model\Sitemap $sitemap
     * @return void
     */
    public function beforeGenerateXml(\Digidirect\Utilities\Model\Sitemap $sitemap)
    {
        if ($this->sitemapHelper->isSitemapBlogShowCategories($sitemap->getStoreId())) {
            $sitemap->addSiteMapItem(new DataObject([
                'changefreq' => $this->sitemapHelper->getSitemapBlogChangefreq($sitemap->getStoreId()),
                'priority' => $this->sitemapHelper->getSitemapBlogCategoryPriority($sitemap->getStoreId()),
                'collection' => $this->prepareCollection(
                    $this->getBlogCategoryCollection()
                )
            ]));
        }

        if ($this->sitemapHelper->isSitemapBlogShowPosts($sitemap->getStoreId())) {
            $sitemap->addSiteMapItem(new DataObject([
                'changefreq' => $this->sitemapHelper->getSitemapBlogChangefreq($sitemap->getStoreId()),
                'priority' => $this->sitemapHelper->getSitemapBlogPostPriority($sitemap->getStoreId()),
                'collection' => $this->prepareCollection(
                    $this->getBlogPostCollection($sitemap)
                )
            ]));
        }
    }

    /**
     * Get Blog Categories
     *
     * @return array
     */
    protected function getBlogCategoryCollection()
    {
        $categoryRepository = $this->categoryRepositoryInterfaceFactory->create();
        $categories = $categoryRepository->getCategories(Status::STATUS_ENABLED);

        return $categories->getItems();
    }

    /**
     * Get Blog Posts
     *
     * @param \Digidirect\Utilities\Model\Sitemap $sitemap
     * @return mixed
     */
    protected function getBlogPostCollection($sitemap)
    {
        $postRepository = $this->postRepositoryInterfaceFactory->create();
        $posts = $postRepository->getPostList(
            Status::STATUS_ENABLED,
            $sitemap->getStoreId(),
            date('Y-m-d')
        );

        return $posts->getItems();
    }

    /**
     * @param array $collection
     * @return array
     */
    protected function prepareCollection(array $collection)
    {
        $items = [];
        foreach ($collection as $item) {
            $items[] = new DataObject([
                'url'        => $item->getViewUrlPath(),
                'updated_at' => $item->getUpdatedAt(),
            ]);
        }

        return $items;
    }
}
