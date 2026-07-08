<?php

namespace Digidirect\Blog\Helper;

use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GENERAL_SETTINGS = 'digidirect_blog/general/';
    const BLOG_SETTINGS    = 'digidirect_blog/';
    const COMMENT_SETTINGS = 'digidirect_blog/comments/';
    const ENABLE_DISPLAY_ALTERNATES_LOCALES_CATEGORY =
        'digidirect_blog/opengraph_settings/enable_display_alternative_locales_category_page';

    const ENABLE_DISPLAY_ALTERNATES_LOCALES_POST =
        'digidirect_blog/opengraph_settings/enable_display_alternative_locales_post_page';

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->getGeneralSettingsConfig('active') && $this->isModuleOutputEnabled('Digidirect_Blog');
    }

    /**
     * @param string $storePath
     * @return mixed
     */
    public function getStoreConfig($storePath)
    {
        return $this->scopeConfig->getValue($storePath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $setting
     * @param string $scopeType
     * @param null|int $scopeCode
     * @return string
     */
    public function getGeneralSettingsConfig(
        $setting,
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(self::GENERAL_SETTINGS . $setting, $scopeType, $scopeCode);
    }

    /**
     * @param string $setting
     * @param string $scopeType
     * @param null|int $scopeCode
     * @return string
     */
    public function getRelatedSettingsConfig(
        $setting,
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(self::BLOG_SETTINGS . $setting, $scopeType, $scopeCode);
    }

    /**
     * @param string $setting
     * @param string $scopeType
     * @param null|int $scopeCode
     * @return string
     */
    public function getDisplaySettingsConfig(
        $setting,
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(self::BLOG_SETTINGS . $setting, $scopeType, $scopeCode);
    }

    /**
     * @return number
     */
    public function getPostPerPage()
    {
        return abs((int)$this->getGeneralSettingsConfig('postonlist'));
    }

    /**
     * @param string $setting
     * @param string $scopeType
     * @param null|int $scopeCode
     * @return string
     */
    public function getCommentSettingsConfig(
        $setting,
        $scopeType = ScopeInterface::SCOPE_STORE,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(self::COMMENT_SETTINGS . $setting, $scopeType, $scopeCode);
    }

    /**
     * It does not make any sense to localize hardcoded date but for legacy this method is introduced
     *
     * @return bool
     * @since 2.0.1
     */
    public function isDateLocalizationRequired(): bool
    {
        return $this->scopeConfig->isSetFlag('dev/blog/localize_publish_date', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isEnableDisplayAlternatesLocalesTagCategoryPage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_DISPLAY_ALTERNATES_LOCALES_CATEGORY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnableDisplayAlternatesLocalesTagPostPage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_DISPLAY_ALTERNATES_LOCALES_POST,
            ScopeInterface::SCOPE_STORE
        );
    }
}
