<?php

namespace Digidirect\SEO\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class HreflangTags
 */
class HreflangTags extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLE_HREFLANG_TAGS = 'digidirect_seo/general/enable_hreflang_tags';
    const XML_PATH_HREFLANG_CODE_SELECTION = 'digidirect_seo/general/hreflang_code_selection';

    /**
     * @return bool
     */
    public function isHreflangTagEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_HREFLANG_TAGS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getHreflangCodeSelection()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_HREFLANG_CODE_SELECTION, ScopeInterface::SCOPE_STORE);
    }
}
