<?php
namespace Digidirect\LayeredNavigation\Block\Navigation;

use Magento\Framework\View\Element\Template;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /**
     * Url Builder helper object
     * @var \Digidirect\LayeredNavigation\Helper\UrlBuilder
     */
    protected $urlBuilderHelper;

    /**
     * State constructor.
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilderHelper
     * @param [] $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilderHelper,
        array $data
    ) {
        $this->urlBuilderHelper = $urlBuilderHelper;
        parent::__construct($context, $layerResolver, $data);
    }

    /**
     * Path to template file.
     * @var string
     */
    protected $_template = 'Digidirect_LayeredNavigation::layer/state.phtml';

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        $filterState = [];
        $filterState['isAjax'] = null;
        $filterState['_'] = null;

        foreach ($this->getActiveFilters() as $item) {
            $filterState[$item->getFilter()->getRequestVar()] = $item->getFilter()->getCleanValue();
        }

        $filterState = array_merge(
            $this->urlBuilderHelper->excludeParams($this->getRequest()->getParams()),
            $filterState
        );

        $params['_clearFilters'] = 1;
        $params['_current'] = false;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return $this->_urlBuilder->getUrl(ltrim($this->_request->getPathInfo(), '/'), $params);
    }

    /**
     * Get active filters from parent function and modify array
     * @param [] $activeFilters
     * @return []
     */
    public function modifyAppliedFiltersArray($activeFilters)
    {
        $newArrayFilters = [];
        foreach ($activeFilters as $filter) {
            /**
             * @var $filter \Digidirect\LayeredNavigation\Model\Layer\Filter\Attribute
             */
            $value = $filter->getValue();
            if (is_array($value)) {
                $value = implode('-', $value);
                $filter->setValue($value);
            }

            $newArrayFilters[(string)$filter->getName()][$value] = $filter;
        }

        return $newArrayFilters;
    }
}
