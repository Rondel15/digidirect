<?php
namespace Digidirect\SEO\Plugin\Magento\Catalog\Model\Category;

use Digidirect\SEO\Api\SitemapExcludeRepositoryInterface;
use Digidirect\SEO\Model\SitemapExclude;
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;
use Digidirect\SEO\Helper\Sitemap as SitemapHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class DataProvider
 *
 * @package Digidirect\SEO\Plugin\Magento\Catalog\Model\Category
 */
class DataProvider
{
    /**
     * @var SitemapExcludeRepositoryInterface
     */
    protected $sitemapExcludeRepository;

    /**
     * @var SitemapHelper
     */
    protected $sitemapHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * DataProvider constructor.
     * @param SitemapExcludeRepositoryInterface $sitemapExcludeRepository
     * @param SitemapHelper $sitemapHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        SitemapExcludeRepositoryInterface $sitemapExcludeRepository,
        SitemapHelper $sitemapHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->sitemapExcludeRepository = $sitemapExcludeRepository;
        $this->sitemapHelper = $sitemapHelper;
        $this->request = $request;
    }

    /**
     * Add exclude_from_sitemap to metadata
     *
     * @param CategoryDataProvider $subject
     * @param [] $result
     * @return []
     */
    public function afterPrepareMeta(CategoryDataProvider $subject, $result)
    {
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($category = $subject->getCurrentCategory()) {
            $storeId = $category->getStoreId();
        }
        if (!$this->sitemapHelper->isSitemapExcludeEnabled(ScopeInterface::SCOPE_STORE, $storeId)) {
            return $result;
        }
        $seoTabFields = $result['search_engine_optimization']['children'];

        // Create container for 'exclude_from_sitemap' and 'use_default.exclude_from_sitemap' fields
        $seoTabFields['exclude_from_sitemap_fieldset']['arguments']['data']['config'] = [
            'componentType' => 'container',
            'component' => 'Magento_Ui/js/form/components/group',
        ];

        $sitemapExcludeFields = [];
        $sitemapExcludeFields['exclude_from_sitemap']['arguments']['data']['config'] = [
            'dataType' => 'boolean',
            'formElement' => 'checkbox',
            'prefer' => 'toggle',
            'visible' => true,
            'required' => false,
            'valueMap' => ['true' => true, 'false' => false],
            'default' => '0',
            'imports' => [
                'disabled' => '${ $.provider }:data.use_default.exclude_from_sitemap'
            ],
            'label' => __('Exclude From Sitemap'),
            'scopeLabel' => '[STORE VIEW]',
            'componentType' => 'field'
        ];
        if ($this->request->getParam('store')) {
            $sitemapExcludeFields['use_default.exclude_from_sitemap']['arguments']['data']['config'] = [
                'dataType' => 'boolean',
                'formElement' => 'checkbox',
                'visible' => true,
                'valueMap' => ['true' => true, 'false' => false],
                'description' => __('Use default'),
                'componentType' => 'field'
            ];
        }
        $seoTabFields['exclude_from_sitemap_fieldset']['children'] = $sitemapExcludeFields;
        $result['search_engine_optimization']['children'] = $seoTabFields;
        return $result;
    }

    /**
     * Set exclude_from_sitemap value
     *
     * @param CategoryDataProvider $dataProvider
     * @param [] $result
     * @return []
     */
    public function afterGetData(
        CategoryDataProvider $dataProvider,
        $result
    ) {
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($dataProvider->getCurrentCategory()) {
            $storeId = $dataProvider->getCurrentCategory()->getStoreId();
        }
        if (!$this->sitemapHelper->isSitemapExcludeEnabled(ScopeInterface::SCOPE_STORE, $storeId)) {
            return $result;
        }
        foreach ($result as $categoryId => &$categoryData) {
            $excluded = $this->sitemapExcludeRepository
                ->isExcluded(SitemapExclude::ITEM_TYPE_CATEGORY, $categoryId, $storeId);
            $categoryData['exclude_from_sitemap'] = $excluded;

            if ($this->request->getParam('store')) {
                $useDefaultSetting = $this->sitemapExcludeRepository
                    ->isDefaultSetting(SitemapExclude::ITEM_TYPE_CATEGORY, $categoryId, $storeId);
                $categoryData['use_default']['exclude_from_sitemap'] = $useDefaultSetting;
            }
        }
        return $result;
    }
}
