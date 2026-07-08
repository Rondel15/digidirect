<?php
namespace Digidirect\LayeredNavigation\Helper;

use Digidirect\LayeredNavigation;
use Magento\Catalog\Model\Layer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting\CollectionFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar as ProductListToolbar;
use Magento\Theme\Block\Html\Pager;

class Data extends AbstractHelper
{
    const CATEGORY_REQUEST_VAR = 'cat';
    const AJAX_NAVIGATION_PARAM_NAME = 'ajax_navigation';
    const LAYERED_NAVIGATION = 'layered_navigation';
    const SEARCH_LAYERED_NAVIGATION = 'search_layered_navigation';
    const SEARCH_NAVIGATION_AREA = 'search_navigation';
    const SELECTOR = 'selector';
    const BLOCK = 'block';
    const NAVIGATION = 'navigation';
    const PRODUCT_LIST_ID = 'digidirect-layerednavigation-product-list';

    /**
     * Request object
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var FilterSetting
     */
    protected $settingHelper;

    /**
     * @var Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var CollectionFactory
     */
    protected $settingCollectionFactory;

    /**
     * @var Option\CollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $productUrl;

    /**
     * @var array
     */
    protected $seoSignificantUrlParameters;

    /**
     * @var array
     */
    protected $optionsSeoData;

    /**
     * Data constructor.
     * @param Context $context
     * @param FilterSetting $settingHelper
     * @param Layer\Resolver $layerResolver
     * @param CollectionFactory $settingCollectionFactory
     * @param Option\CollectionFactory $optionCollectionFactory
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Context $context,
        FilterSetting $settingHelper,
        Layer\Resolver $layerResolver,
        CollectionFactory $settingCollectionFactory,
        Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context);
        $this->settingHelper = $settingHelper;
        $this->layerResolver = $layerResolver;
        $this->settingCollectionFactory = $settingCollectionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->productUrl = $productUrl;
        $this->request = $request;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSelectedFiltersSettings()
    {
        $layer = $this->layerResolver->get();
        $appliedItems = $layer->getState()->getFilters();
        $result = [];
        foreach ($appliedItems as $item) {
            $filter = $item->getFilter();
            $setting = $this->settingHelper->getSettingByLayerFilter($filter);
            $result[] = [
                'filter' => $filter,
                'setting' => $setting,
            ];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/general/ajax_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return array
     */
    public function getOptionsSeoData()
    {
        if ($this->optionsSeoData === null) {
            $collection = $this->optionCollectionFactory->create();
            $collection->join(['a' => 'eav_attribute'], 'a.attribute_id = main_table.attribute_id', ['attribute_code']);
            $collection->setStoreFilter();
            $select = $collection->getSelect();

            $statement = $select->query();
            $rows = $statement->fetchAll();
            $this->optionsSeoData = [];
            $aliasHash = [];
            foreach ($rows as $row) {
                $alias = $this->buildUniqueAlias($row['value'], $aliasHash);
                $optionId = $row['option_id'];
                $this->optionsSeoData[$optionId] = [
                    'alias' => $alias,
                    'attribute_code' => $row['attribute_code'],
                ];
                $aliasHash[$alias] = $optionId;
            }
        }

        return $this->optionsSeoData;
    }

    /**
     * @param string $value
     * @param string $hash
     * @return string
     */
    protected function buildUniqueAlias($value, $hash)
    {
        if (preg_match('@^[\d\.]+$@s', $value)) {
            $format = $value;
        } else {
            $format = $this->productUrl->formatUrlKey($value);
        }

        $unique = $format;
        for ($i = 1; array_key_exists($unique, $hash); $i++) {
            $unique = $format . '-' . $i;
        }

        return $unique;
    }

    /**
     * Validate request
     * @return bool
     */
    public function validateRequest()
    {
        return $this->request->getParam(self::AJAX_NAVIGATION_PARAM_NAME) && !$this->request->getParam('_is');
    }

    /**
     * @param string $path
     * @return bool|array
     */
    public function parseRequestUri($path = null)
    {
        if ($path === null) {
            $path = $this->request->getRequestUri();
        }

        $path = preg_replace('/\?.*/i', '', $path);
        if (!preg_match('/^(.*)\/' . Url::FILTERS_DELIMITER . '\/(.*)$/', $path, $matches)) {
            return false;
        }
        return $matches;
    }

    /**
     * Make mapping array
     * @return []
     */
    public function getBlocksMap()
    {
        $map = [];
        $selectors = $this->_getSelectors();
        $blocks = $this->_getBlocks();

        foreach ($this->_getAllowedAreas() as $area) {
            $map[$area] = [
                self::SELECTOR => $selectors[$area],
                self::BLOCK => $blocks[$area]
            ];
        }

        $mapData = new \Magento\Framework\DataObject($map);
        $this->_eventManager->dispatch('digidirect_layerednavigation_generate_blocks_map', [
            'map' => $mapData
        ]);

        return $mapData->getData();
    }

    /**
     * Get selectors array
     * @return []
     */
    protected function _getSelectors()
    {
        $listSelectors = ['#' . self::PRODUCT_LIST_ID];
        return [
            self::LAYERED_NAVIGATION => $listSelectors,
            self::NAVIGATION => ['.filter.block'],
            self::SEARCH_LAYERED_NAVIGATION => $listSelectors,
            self::SEARCH_NAVIGATION_AREA => ['.filter.block'],
        ];
    }

    /**
     * Get block names array
     * @return []
     */
    protected function _getBlocks()
    {
        return [
            self::LAYERED_NAVIGATION => 'category.products.list',
            self::SEARCH_LAYERED_NAVIGATION => 'search.result',
            self::NAVIGATION => 'catalog.leftnav',
            self::SEARCH_NAVIGATION_AREA => 'catalogsearch.leftnav'
        ];
    }

    /**
     * Get removable blocks when reminder is enabled
     * @return []
     */
    public function getRemovableBlocks()
    {
        return $this->_getBlocks();
    }

    /**
     * Get allowed for layered navigation areas array
     * @return []
     */
    protected function _getAllowedAreas()
    {
        return [
            self::LAYERED_NAVIGATION,
            self::NAVIGATION,
            self::SEARCH_LAYERED_NAVIGATION,
            self::SEARCH_NAVIGATION_AREA
        ];
    }

    /**
     * Check if we need to apply filter only by clicking "Apply" button
     * @return bool
     */
    public function isApplyButtonEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/general/enable_apply_button',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Check if we need to apply filter only by clicking "Apply" button on mobile
     * @return bool
     */
    public function isMobileApplyButtonEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/general/enable_mobile_apply_button',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Hide the list of filters after making a selection,
     * so that customer will be able to see all options
     *
     * @return bool
     */
    public function hideOptionsAfterSelect()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/general/hide_options_after_select',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Return category suffix
     * @return string
     */
    public function getSuffix()
    {
        return $this->scopeConfig->getValue('catalog/seo/category_url_suffix');
    }

    /**
     * @param LayoutInterface $layout
     * @return array
     */
    public function getPagerParams(LayoutInterface $layout)
    {
        $result = [
            'currentCount' => 0,
            'totalCount' => 0,
            'nextUrl' => '',
        ];

        $pageBlocks = $layout->getAllBlocks();
        foreach ($pageBlocks as $pageBlock) {
            if ($pageBlock instanceof ProductListToolbar && $pageBlock->getCollection() !== null) {
                $collection = $pageBlock->getCollection();
                $childNames = $pageBlock->getChildNames();
                foreach ($childNames as $childName) {
                    $child = $layout->getBlock($childName);
                    if ($child instanceof Pager) {
                        $child->setLimit($pageBlock->getLimit());
                        $child->setCollection($collection);
                        $result = [
                            'currentCount' => $collection->count(),
                            'totalCount' => $child->getTotalNum(),
                            'nextUrl' => $child->getTotalNum() > $collection->count() ? $child->getNextPageUrl() : '',
                        ];
                    }
                }
            }
        }
        return $result;
    }
}
