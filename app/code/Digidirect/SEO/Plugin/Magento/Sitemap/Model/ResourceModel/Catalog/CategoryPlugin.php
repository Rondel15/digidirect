<?php
namespace Digidirect\SEO\Plugin\Magento\Sitemap\Model\ResourceModel\Catalog;

use Digidirect\SEO\Model\SitemapExclude;
use Digidirect\SEO\Model\SitemapExcludeRepository;
use Magento\Sitemap\Model\ResourceModel\Catalog\Category;
use Digidirect\SEO\Helper\Sitemap as SitemapHelper;

/**
 * Class CategoryPlugin
 * @package Digidirect\SEO\Plugin\Magento\Sitemap\Model\ResourceModel\Catalog
 */
class CategoryPlugin
{

    /**
     * @var SitemapHelper
     */
    private $sitemapHelper;

    /**
     * @var SitemapExcludeRepository
     */
    private $sitemapExcludeRepository;

    /**
     * CategoryPlugin constructor.
     *
     * @param SitemapHelper $sitemapHelper
     * @param SitemapExcludeRepository $sitemapExcludeRepository
     */
    public function __construct(SitemapHelper $sitemapHelper, SitemapExcludeRepository $sitemapExcludeRepository)
    {
        $this->sitemapHelper = $sitemapHelper;
        $this->sitemapExcludeRepository = $sitemapExcludeRepository;
    }

    /**
     * Around get collection array plugin
     *
     * @param Category $subject
     * @param callable $proceed
     * @param int $storeId
     * @return []
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCollection(Category $subject, callable $proceed, $storeId)
    {
        $result = $proceed($storeId);
        if (!$this->sitemapHelper->isSitemapExcludeEnabled()) {
            return $result;
        }

        $excludedIds = $this->sitemapExcludeRepository
            ->getExcludedItemIds(SitemapExclude::ITEM_TYPE_CATEGORY, $storeId);
        $collectionArray = array_diff_key($result, array_flip($excludedIds));

        return $collectionArray;
    }
}
