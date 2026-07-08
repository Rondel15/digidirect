<?php
namespace Digidirect\SocialSharing\Helper\Config;

use \Magento\Store\Model\ScopeInterface;

class Twitter extends \Digidirect\SocialSharing\Helper\Config
{
    const SOCIAL_SHARING_TWITTER = 'digidirect_social_sharing/sharing_twitter/';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_TWITTER . 'enable',
            ScopeInterface::SCOPE_STORE
        );
    }
}
