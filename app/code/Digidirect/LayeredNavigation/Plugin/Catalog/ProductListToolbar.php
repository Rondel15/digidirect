<?php
namespace Digidirect\LayeredNavigation\Plugin\Catalog;

class ProductListToolbar
{
    /**
     * @var \Digidirect\LayeredNavigation\Helper\Data
     */
    protected $helper;

    /**
     * Url builder Helper
     * @var \Digidirect\LayeredNavigation\Helper\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * CatalogToolbarPlugin constructor.
     * @param \Digidirect\LayeredNavigation\Helper\Data $helper
     * @param \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilder
     */
    public function __construct(
        \Digidirect\LayeredNavigation\Helper\Data $helper,
        \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilder
    ) {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param \Closure $closure
     * @param array $params
     * @return mixed
     */
    public function aroundGetPagerUrl(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $closure,
        $params = []
    ) {
        if ($this->helper->isAjaxEnabled()) {
            $params['isAjax'] = null;
            $params['_'] = null;
        }

        return $closure($params);
    }

    /**
     * Before get url change original arguments - set current to false and modify query
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param string $routeOriginal
     * @param [] $paramsOriginal
     * @return []
     */
    public function beforeGetUrl(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $routeOriginal,
        $paramsOriginal
    ) {
        $request = $subject->getRequest();
        $paramsOriginal['_query'] = $this->urlBuilder->excludeParams($request->getParams());
        $paramsOriginal['_escape'] = false;
        return [$routeOriginal, $paramsOriginal];
    }
}
