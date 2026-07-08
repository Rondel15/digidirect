<?php
namespace Digidirect\Blog\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Sitemap
 * @package Digidirect\Blog\Helper
 */
class Sitemap extends Data
{
    const SITEMAP_BLOG = 'sitemap/blog/';
    const XML_PATH_SITEMAP_BLOG_CHANGEFREQ = self::SITEMAP_BLOG . 'changefreq';
    const XML_PATH_SITEMAP_BLOG_SHOW_CATEGORIES = self::SITEMAP_BLOG . 'show_categories';
    const XML_PATH_SITEMAP_BLOG_CATEGORY_PRIORITY = self::SITEMAP_BLOG . 'category_priority';
    const XML_PATH_SITEMAP_BLOG_SHOW_POSTS = self::SITEMAP_BLOG . 'show_posts';
    const XML_PATH_SITEMAP_BLOG_POST_PRIORITY = self::SITEMAP_BLOG . 'post_priority';

    /**
     * @param int $storeId
     * @return string
     */
    public function getSitemapBlogChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SITEMAP_BLOG_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isSitemapBlogShowCategories($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SITEMAP_BLOG_SHOW_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getSitemapBlogCategoryPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SITEMAP_BLOG_CATEGORY_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isSitemapBlogShowPosts($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SITEMAP_BLOG_SHOW_POSTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getSitemapBlogPostPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SITEMAP_BLOG_POST_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
