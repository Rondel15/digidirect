<?php
namespace Digidirect\SocialSharing\Helper\Config;

use \Magento\Store\Model\ScopeInterface;

class Facebook extends \Digidirect\SocialSharing\Helper\Config
{
    const SOCIAL_SHARING_FACEBOOK = 'digidirect_social_sharing/sharing_facebook/';

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_FACEBOOK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed|string
     */
    public function getFacebookId()
    {
        return $this->scopeConfig->getValue(
            self::SOCIAL_SHARING_FACEBOOK . 'id',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayLikeSettings()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_FACEBOOK . 'like',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isDisplayCountSettings()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_FACEBOOK . 'count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
