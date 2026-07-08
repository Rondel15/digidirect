<?php
namespace Digidirect\SocialSharing\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SOCIAL_SHARING_ENABLED = 'digidirect_social_sharing/general/enable';

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::SOCIAL_SHARING_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
