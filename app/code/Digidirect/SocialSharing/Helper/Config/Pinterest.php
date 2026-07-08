<?php
namespace Digidirect\SocialSharing\Helper\Config;

use \Magento\Store\Model\ScopeInterface;

class Pinterest extends \Digidirect\SocialSharing\Helper\Config
{
    const SOCIAL_SHARING_PIN = 'digidirect_social_sharing/sharing_pin/';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_PIN . 'enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isPinDisplayCountSettings()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_PIN . 'count',
            ScopeInterface::SCOPE_STORE
        );
    }
}
