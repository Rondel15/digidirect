<?php
namespace Digidirect\LayeredNavigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class FilterRemember extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $session;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * FilterRemember constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilder
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return bool
     */
    public function isFilterRememberEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/general/enable_filter_remember',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCategory()
    {
        /** @var \Magento\Catalog\Model\Category $currentCategory */
        $currentCategory = $this->registry->registry('current_category');
        if ($currentCategory === null) {
            return null;
        }
        return $currentCategory;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        $currentCategory = $this->getCategory();
        if ($currentCategory === null) {
            return 0;
        }
        return $currentCategory->getId();
    }

    /**
     * @return array
     */
    public function getCategoryParents()
    {
        $currentCategory = $this->getCategory();
        if ($currentCategory === null) {
            return [0];
        }
        return $currentCategory->getParentIds();
    }

    /**
     * @param int|null $categoryId
     * @return array
     */
    public function getStoredFilters($categoryId = null)
    {
        $filters = $this->session->getLayeredNavigationFilters();
        if (!$filters) {
            return [];
        }

        if ($categoryId === null) {
            return $filters;
        }

        if (isset($filters[$categoryId])) {
            return $filters[$categoryId];
        }

        foreach ($filters as $parentCategory => $categoryFilters) {
            if (in_array($parentCategory, $this->getCategoryParents())) {
                return $categoryFilters;
            }
        }
        return [];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function storeFilter($name, $value)
    {
        $storedFilters = $this->getStoredFilters();
        $storedFilters[$this->getCategoryId()][$name] = $value;
        $this->session->setLayeredNavigationFilters($storedFilters);
    }

    /**
     * @param string $filter
     * @return array
     */
    public function getStoredFilter($filter)
    {
        $filters = $this->getStoredFilters($this->getCategoryId());
        if (isset($filters[$filter])) {
            return $filters[$filter];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isFiltersApplied()
    {
        return $this->registry->registry(UrlBuilder::SEO_PARSED_PARAMS) !== null;
    }
}
