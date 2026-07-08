<?php
namespace Digidirect\QuickView\Helper;

/**
 * Class Data
 * @package Digidirect\QuickView\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const QUICK_VIEW_ENABLED   = 'digidirect_quickview/general/quickview_enabled';
    const CLOSE_AFTER_ADD_TO_CART   = 'digidirect_quickview/general/close_after_add_to_cart';

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::QUICK_VIEW_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function closeAfterAddToCart()
    {
        return $this->scopeConfig->isSetFlag(
            self::CLOSE_AFTER_ADD_TO_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
}
