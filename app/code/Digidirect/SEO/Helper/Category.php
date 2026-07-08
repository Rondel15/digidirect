<?php
namespace Digidirect\SEO\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Category
 * @package Digidirect\SEO\Helper
 */
class Category extends AbstractHelper
{
    const DIGIDIRECT_SEO_CATEGORY_AUTO_GENERATION_CONFIG_PATH = 'catalog/seo';

    /**
     * Check if metadata auto-generation is enabled
     *
     * @return bool
     */
    public function isMetaAutoGenerationEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::DIGIDIRECT_SEO_CATEGORY_AUTO_GENERATION_CONFIG_PATH . '/enable_category_metadata_generation'
        );
    }
}
