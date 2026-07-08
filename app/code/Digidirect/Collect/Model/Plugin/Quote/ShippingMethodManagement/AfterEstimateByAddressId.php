<?php

namespace Digidirect\Collect\Model\Plugin\Quote\ShippingMethodManagement;

class AfterEstimateByAddressId
{
    /**
     * Filter
     *
     * @var FilterRates
     */
    protected $_filter;

    /**
     * Collect Helper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * AfterEstimateByAddressId constructor.
     *
     * @param FilterRates $filterRates
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     */
    public function __construct(
        \Digidirect\Collect\Model\Plugin\Quote\ShippingMethodManagement\FilterRates $filterRates,
        \Digidirect\Collect\Helper\Data $collectHelper
    ) {
        $this->_filter = $filterRates;
        $this->_collectHelper = $collectHelper;
    }

    /**
     * @param \Magento\Quote\Model\ShippingMethodManagement $methodManagement
     * @param \Magento\Quote\Model\Cart\ShippingMethod[] $rates
     * @return \Magento\Quote\Model\Cart\ShippingMethod[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterEstimateByAddressId(
        \Magento\Quote\Model\ShippingMethodManagement $methodManagement,
        $rates
    ) {
        if ($this->_collectHelper->isCollectEnable()) {
            return $this->_filter->filterRates($rates);
        }

        return $rates;
    }
}
