<?php
namespace Digidirect\SocialSharing\Helper\Config;

use \Magento\Store\Model\ScopeInterface;

class Google extends \Digidirect\SocialSharing\Helper\Config
{
    const SOCIAL_SHARING_GOOGLE = 'digidirect_social_sharing/sharing_google/';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_GOOGLE . 'enable_plus_one',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isShareEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_GOOGLE . 'enable_share',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayCountSettings()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_GOOGLE . 'count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
