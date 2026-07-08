<?php
namespace Digidirect\LayeredNavigation\Helper;

use Digidirect\LayeredNavigation\Model\Layer\Filter\Price;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product\ProductList\Toolbar;

class UrlBuilder extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEO_PARSED_PARAMS = 'digidirect_layerednavigation_seo_parsed_params';

    /**
     * @var \Digidirect\LayeredNavigation\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $queryParamsResolver;

    /**
     * @var []
     */
    protected $_excludedParams = [];

    /**
     * UrlBuilder constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FilterSetting $filterSettingHelper
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param array $additionalExcludedParams
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Digidirect\LayeredNavigation\Helper\FilterSetting $filterSettingHelper,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        $additionalExcludedParams = []
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->queryParamsResolver = $queryParamsResolver;
        $this->_excludedParams = array_merge($this->_excludedParams, $additionalExcludedParams);
    }

    /**
     * Build url
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @param string $value
     * @param bool $clearFilters
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function buildUrl(
        \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter,
        $value,
        $clearFilters = false
    ) {
        $result = [];
        $parsedParams = $this->registry->registry(self::SEO_PARSED_PARAMS);
        $data = $this->_request->getParam($filter->getRequestVar());
        if (!$data && isset($parsedParams[$filter->getRequestVar()])) {
            $data = $parsedParams[$filter->getRequestVar()];
        }

        if ($filter instanceof Price && is_array($value)) {
            $value = implode('-', $value);
        }

        if (!empty($data)) {
            $values = explode(UrlParser::ALIAS_DELIMITER, $data);
            foreach ($values as $key => $val) {
                if (empty($val)) {
                    unset($values[$key]);
                }
            }

            $key = array_search($value, $values);
            if ($this->_isMultiselectAllowed($filter)) {
                if ($key !== false) {
                    unset($values[$key]);
                    $result = $values;
                    $clearFilters = true;
                } else {
                    $result = $values;
                    $result[] = $value;
                }
            } else {
                if ($key !== false) {
                    $result = [];
                    $clearFilters = true;
                } else {
                    $result[] = $value;
                }
            }
        } else {
            $result = [$value];
        }

        if (!empty($result)) {
            $result = implode(UrlParser::ALIAS_DELIMITER, $result);
        } else {
            $result = null;
        }

        $query = [$filter->getRequestVar() => $result];
        if (is_array($query) && !empty($parsedParams)) {
            $query = array_merge($parsedParams, $query);
        }

        $query = $this->_modifyQueryString($filter, $query);
        $query = array_merge($this->_request->getParams(), $query);
        $query = $this->excludeParams($query);

        $queryData = ['_current' => false, '_use_rewrite' => true, '_query' => $query];
        if ($clearFilters || empty(array_filter($query))) {
            $queryData['_clearFilters'] = 1;
        }

        $queryData = new \Magento\Framework\DataObject($queryData);
        $this->_eventManager->dispatch('digidirect_layerednavigation_build_url', [
            'filter' => $filter,
            'value' => $value,
            'query' => $queryData,
        ]);

        return $this->_urlBuilder->getUrl(
            ltrim($this->_request->getPathInfo(), '/'),
            $queryData->getData()
        );
    }

    /**
     * Add all variables to query
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $originalFilter
     * @param [] $query
     * @return []
     */
    protected function _modifyQueryString(\Magento\Catalog\Model\Layer\Filter\FilterInterface $originalFilter, $query)
    {
        $layer = $originalFilter->getLayer();
        $filters = $layer->getState()->getFilters();
        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $filterItem) {
                if ($filterItem->getName() == $originalFilter->getName()) {
                    continue;
                }

                /**
                 * @var $filterItem \Magento\Catalog\Model\Layer\Filter\Item
                 * @var $filter \Digidirect\LayeredNavigation\Model\Layer\Filter\Attribute
                 */
                $filter = $filterItem->getFilter();
                $requestVar = $filter->getRequestVar();
                if ($requestValue = $this->_request->getParam($requestVar)) {
                    $query[$requestVar] = $requestValue;
                }
            }
        }

        return $query;
    }

    /**
     * Check if multiple select is enabled for this attribute
     * and if attribute is multiple select
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @return bool|null
     */
    private function _isMultiselectAllowed(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        $setting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
        return $setting->isMultiselect();
    }

    /**
     * Remove extra parameters from filter url
     * @param [] $params
     * @return []
     */
    public function excludeParams($params)
    {
        foreach ($this->_excludedParams as $parameter) {
            if (isset($params[$parameter])) {
                unset($params[$parameter]);
            }
        }

        return $params;
    }
}
