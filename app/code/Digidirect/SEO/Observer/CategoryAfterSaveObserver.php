<?php
namespace Digidirect\SEO\Observer;

use Digidirect\SEO\Model\SitemapExclude;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\ObserverInterface;
use Digidirect\SEO\Model\SitemapExcludeRepository;
use Magento\Framework\Event\Observer;
use Digidirect\SEO\Helper\Sitemap as SitemapHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CategoryAfterSaveObserver
 *
 * @package Digidirect\SEO\Observer
 */
class CategoryAfterSaveObserver implements ObserverInterface
{
    /**
     * @var SitemapExcludeRepository
     */
    protected $sitemapExcludeRepository;

    /**
     * @var SitemapHelper
     */
    protected $sitemapHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * CategoryAfterSaveObserver constructor.
     * @param SitemapExcludeRepository $sitemapExcludeRepository
     * @param SitemapHelper $sitemapHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        SitemapExcludeRepository $sitemapExcludeRepository,
        SitemapHelper $sitemapHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->sitemapExcludeRepository = $sitemapExcludeRepository;
        $this->sitemapHelper = $sitemapHelper;
        $this->request = $request;
    }

    /**
     * Process sitemap exclude related to the category
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $params = $this->request->getParams();
        $storeId = $category->getStoreId();

        if (!$this->sitemapHelper->isSitemapExcludeEnabled(ScopeInterface::SCOPE_STORE, $storeId)) {
            return $this;
        }
        $shouldExclude = $shouldUseDefault = null;
        if (isset($params['exclude_from_sitemap'])) {
            $shouldExclude = $params['exclude_from_sitemap'] == 'true' ? true : false;
        }
        if (isset($params['use_default']['exclude_from_sitemap'])) {
            $shouldUseDefault = $params['use_default']['exclude_from_sitemap'] == 'true' ? true : false;
        }
        if ($shouldExclude !== null || $shouldUseDefault !== null) {
            $this->sitemapExcludeRepository->processItemExclude(
                SitemapExclude::ITEM_TYPE_CATEGORY,
                $category->getId(),
                $category->getStoreId(),
                $shouldUseDefault,
                $shouldExclude
            );
        }
        return $this;
    }
}
