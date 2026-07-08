<?php
namespace Digidirect\SEO\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class TrailingSlash
 * @package Digidirect\SEO\Helper
 */
class TrailingSlash extends AbstractHelper
{
    const XML_PATH_TRAILING_SLASH_ENABLED = 'digidirect_seo/general/enable_trailing_slash';

    /**
     * Check if trailing slash enabled
     *
     * @return bool
     */
    public function isTrailingSlashEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TRAILING_SLASH_ENABLED
        );
    }

    /**
     * Adds trailing slash to url
     *
     * @param string $url
     * @return string
     */
    public function addTrailingSlash($url)
    {
        $char = substr($url, -1);
        if ($char != '/') {
            $url .= '/';
        }
        return $url;
    }
}
