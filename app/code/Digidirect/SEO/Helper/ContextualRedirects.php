<?php

namespace Digidirect\SEO\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class ContextualRedirects
 *
 * @package Digidirect\SEO\Helper
 */
class ContextualRedirects extends AbstractHelper
{
    const XML_PATH_CONTEXTUAL_REDIRECTS_ENABLED = 'digidirect_seo/general/enable_contextual_redirects';

    /**
     * Check if trailing slash enabled
     *
     * @return bool
     */
    public function isContextualRedirectsEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CONTEXTUAL_REDIRECTS_ENABLED);
    }
}
