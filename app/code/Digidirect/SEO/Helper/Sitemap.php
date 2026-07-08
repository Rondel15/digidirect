<?php
namespace Digidirect\SEO\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Sitemap
 * @package Digidirect\SEO\Helper
 */
class Sitemap extends AbstractHelper
{
    const XML_PATH_SITEMAP_EXCLUDE_ENABLED = 'digidirect_seo/sitemap/exclude_from_sitemap';

    /**
     * Check if sitemap exclude enabled
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isSitemapExcludeEnabled($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SITEMAP_EXCLUDE_ENABLED,
            $scopeType,
            $scopeCode
        );
    }
}
