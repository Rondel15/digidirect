<?php

namespace Digidirect\MyStoreWidgetCollect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Digidirect\MyStoreWidgetCollect\Helper
 */
class Config extends AbstractHelper
{
    const XML_PATH_DISABLE_FROM_CC = 'digidirect_mystorewidget/click_and_collect/disable_not_full_c_c';
    const XML_PATH_CC_RELATION_ENABLE = 'digidirect_mystorewidget/click_and_collect/c_c_relation_enable';

    /**
     * @return bool
     */
    public function isDisableFromFullCC()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DISABLE_FROM_CC, ScopeInterface::SCOPE_WEBSITE);
    }
    /**
     * @return bool
     */
    public function isRelationWithCCEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CC_RELATION_ENABLE, ScopeInterface::SCOPE_WEBSITE);
    }
}
