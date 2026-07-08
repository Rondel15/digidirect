<?php
namespace Digidirect\ShippingAvailabilityCheck\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Digidirect\ShippingAvailabilityCheck\Helper
 */
class Data extends \Digidirect\Utilities\Helper\Data
{
    const XML_PATH_ENABLE_SHIPPING_AVAILABILITY_CHECK = 'digidirect_shippingavailabilitycheck/general/enable';
    const XML_PATH_ALL_SHIPPING_METHODS_TO_DISPLAY = 'digidirect_shippingavailabilitycheck/general/all_methods';
    const XML_PATH_DISPLAY_FOR_OUT_OF_STOCK = 'digidirect_shippingavailabilitycheck/general/display_for_out_of_stock';

    /**
     * @return bool
     */
    public function isShippingAvailabilityCheckEnable()
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_ENABLE_SHIPPING_AVAILABILITY_CHECK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function allShippingMethodsToDisplay()
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_ALL_SHIPPING_METHODS_TO_DISPLAY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayForOutOfStockEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_DISPLAY_FOR_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
    }
}
