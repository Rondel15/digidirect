<?php
namespace Digidirect\LayeredNavigation\Plugin\Layer;

use Digidirect\LayeredNavigation\Helper;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\RequestInterface;

class FilterRemember
{
    /**
     * @var Helper\FilterRemember
     */
    protected $helper;

    /**
     * @param Helper\FilterRemember $helper
     */
    public function __construct(Helper\FilterRemember $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param AbstractFilter $filter
     * @param RequestInterface $request
     * @return array
     */
    public function beforeApply(
        AbstractFilter $filter,
        RequestInterface $request
    ) {
        if ($this->helper->isFilterRememberEnabled()) {
            $isClearFilters = $request->getParam('clearFilters');
            $requestValue = $request->getParam($filter->getRequestVar());
            if ($requestValue || $isClearFilters) {
                $this->helper->storeFilter($filter->getRequestVar(), $requestValue);
            } else {
                $storedFilter = $this->helper->getStoredFilter($filter->getRequestVar());
                if ($storedFilter) {
                    $request->setParam($filter->getRequestVar(), $storedFilter);
                }
            }
        }
        return [$request];
    }
}
