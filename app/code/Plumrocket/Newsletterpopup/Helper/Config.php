<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Plumrocket\Base\Model\ConfigUtils;
use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;

/**
 * Class Config use for retrieve module configuration
 */
class Config extends AbstractHelper
{
    public const GOOGLE_RECAPTCHA  = 'google_recaptcha';
    public const XML_PATH_MODULE_ENABLED  = 'prnewsletterpopup/general/enable';
    public const XML_PATH_IS_ENABLED_ANALYTICS  = 'prnewsletterpopup/general/enable_analytics';
    public const XML_PATH_COOKIE_USAGE  = 'prnewsletterpopup/general/cookies_usage';
    public const XML_PATH_IS_HISTORY_ENABLED  = 'prnewsletterpopup/general/enable_history';
    public const XML_PATH_HISTORY_CLEANING  = 'prnewsletterpopup/general/erase_history';
    public const XML_PATH_SKIP_IPS  = 'prnewsletterpopup/general/ip_skip';
    public const XML_GTM_ENABLED  = 'prnewsletterpopup/general/gtm_tracking';

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Plumrocket\Base\Model\ConfigUtils
     */
    private $configUtils;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Framework\Encryption\Encryptor    $encryptor
     * @param \Magento\Framework\App\State               $state
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Plumrocket\Base\Model\ConfigUtils         $configUtils
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigUtils $configUtils
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->configUtils = $configUtils;
    }

    /**
     * @param null $store
     * @param null $scope
     * @return bool
     */
    public function isModuleEnabled($store = null, $scope = null): bool
    {
        return $this->configUtils->isSetFlag(self::XML_PATH_MODULE_ENABLED, $store, $scope);
    }

    /**
     * Check if the Google Analytics integration enabled.
     *
     * @return bool
     */
    public function isGoogleAnalyticsEnabled(): bool
    {
        return (bool) $this->configUtils->getStoreConfig(self::XML_PATH_IS_ENABLED_ANALYTICS);
    }

    /**
     * @param null $store
     * @param null $scope
     * @return int
     */
    public function getCookieUsage($store = null, $scope = null): int
    {
        return (int) $this->configUtils->getConfig(self::XML_PATH_COOKIE_USAGE, $store, $scope);
    }

    /**
     * Is enabled history.
     *
     * @return bool
     */
    public function isHistoryEnabled(): bool
    {
        return $this->configUtils->isSetFlag(self::XML_PATH_IS_HISTORY_ENABLED);
    }

    /**
     * Get number of days for history record to clear.
     *
     * @return int days
     */
    public function getHistoryExpiration(): int
    {
        return (int) $this->configUtils->getConfig(self::XML_PATH_HISTORY_CLEANING);
    }

    /**
     * Get IP list that should be excluded from history.
     *
     * @return array
     */
    public function getSkipIps(): array
    {
        $ips = (string) $this->configUtils->getStoreConfig(self::XML_PATH_SKIP_IPS);
        return $this->configUtils->splitTextareaValueByLine($ips);
    }

    /**
     * Retrieve config value according to current section identifier
     *
     * @param string $path
     * @param string|int $store
     * @param null $scope
     * @return mixed
     */
    public function getSectionConfig($path, $store = null, $scope = null)
    {
        return $this->configUtils->getConfig(
            DataHelper::SECTION_ID . '/' . $path,
            $store,
            $scope
        );
    }

    /**
     * @param null $store
     * @return string
     * @since 4.1.3
     */
    public function getReCaptchaConfigType($store = null): string
    {
        return (string) $this->getSectionConfig(self::GOOGLE_RECAPTCHA . '/google_recaptcha_config_type', $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getReCaptchaSiteKey($store = null)
    {
        return trim((string) $this->getSectionConfig(self::GOOGLE_RECAPTCHA . '/google_recaptcha_sitekey', $store));
    }

    /**
     * @param null $store
     * @return string
     */
    public function getReCaptchaSecretKey($store = null)
    {
        return trim($this->encryptor->decrypt(
            (string) $this->getSectionConfig(self::GOOGLE_RECAPTCHA . '/google_recaptcha_secretkey', $store)
        ));
    }

    /**
     * @return string
     */
    public function getConstantContactApiKey()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/constantcontact/key')
        );
    }

    /**
     * @return string
     */
    public function getConstantContactSecret()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/constantcontact/secret')
        );
    }

    /**
     * @return string
     */
    public function getConstantContactRefreshToken()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/constantcontact/refresh_token')
        );
    }

    /**
     * @return string
     */
    public function getConstantContactAccessToken()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/constantcontact/access_token')
        );
    }

    /**
     * Retrieve constant contact redirect uri
     *
     * @return string|null
     */
    public function getConstantContactRedirectUri()
    {
        $params = ['_nosid' => true];

        if (Area::AREA_ADMINHTML === $this->state->getAreaCode()) {
            $request = $this->_getRequest();
            $storeId = $request->getParam('store');

            if (! $storeId) {
                $websiteId = $request->getParam('website');
                $storeId = $this->storeManager
                    ->getWebsite($websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();

                if (! $storeId) {
                    $storeId = $this->storeManager
                        ->getWebsite(true)
                        ->getDefaultGroup()
                        ->getDefaultStoreId();
                }
            }

            $params['key'] = false;
            $params['_scope'] = $storeId;
        }

        return $this->_getUrl(
            'prnewsletterpopup/accessToken/constantContact',
            $params
        );
    }

    /**
     * @return string
     */
    public function getSalesForceAccessToken()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/salesforce/access_token')
        );
    }

    /**
     * @return string
     */
    public function getSalesForceClientId()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/salesforce/user_id')
        );
    }

    /**
     * @return string
     */
    public function getSalesForceClientSecret()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/salesforce/secret_id')
        );
    }

    /**
     * @return string
     */
    public function getSalesForceUsername()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/salesforce/app_name')
        );
    }

    /**
     * @return string
     */
    public function getSalesForcePassword()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/salesforce/api_password')
        );
    }

    /**
     * @return string
     */
    public function getSalesForceSecurityToken()
    {
        return $this->encryptor->decrypt(
            $this->getSectionConfig('integration/salesforce/api_security_token')
        );
    }

    /**
     * Check if mailchimp is enabled.
     *
     * @return bool
     */
    public function isMaichimpEnabled(): bool
    {
        return $this->configUtils->isSetFlag('prnewsletterpopup/integration/mailchimp/enable');
    }

    /**
     * Check if Google Tag Manager tracking is enabled.
     *
     * @return bool
     */
    public function isGtmTrackingEnabled(): bool
    {
        return $this->configUtils->isSetFlag(self::XML_GTM_ENABLED);
    }
}
