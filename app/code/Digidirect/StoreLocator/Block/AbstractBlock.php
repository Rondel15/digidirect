<?php

namespace Digidirect\StoreLocator\Block;

use Digidirect\StoreLocator\Helper\ApiKey;
use Digidirect\StoreLocator\Helper\Directory;
use Digidirect\StoreLocator\Model\Frontend\UrlModifier;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use Magento\Directory\Model\CountryFactory;
use Digidirect\GoogleApi\Helper\Config as GoogleApiHelper;

/**
 * @since 1.4.0 uses object manager for backward compatibility
 */
class AbstractBlock extends Template
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var GoogleApiHelper
     */
    protected $googleApiConfigHelper;

    /**
     * @var array
     */
    protected $countryNameByCode = [];

    /**
     * @var null|Directory
     */
    protected $directoryCountryHelper = null;

    /**
     * @var null|ApiKey
     */
    protected $apiKeyHelper = null;

    /**
     * @var null
     */
    private $urlModifier = null;

    /**
     * AbstractBlock constructor.
     * @param Context $context
     * @param ConfigHelper $configHelper
     * @param CountryFactory $countryFactory
     * @param GoogleApiHelper $googleApiConfigHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        CountryFactory $countryFactory,
        GoogleApiHelper $googleApiConfigHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->countryFactory = $countryFactory;
        $this->googleApiConfigHelper = $googleApiConfigHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return ConfigHelper
     */
    public function getSystemConfig()
    {
        return $this->configHelper;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getSystemConfig()->isEnable() ? parent::_toHtml() : '';
    }

    /**
     * @deprecated see getApiKey()
     * @return mixed
     */
    public function getGoogleApiKey()
    {
        return $this->googleApiConfigHelper->getGoogleApiKey();
    }

    /**
     * @return mixed
     */
    public function getPageMetaDescription()
    {
        return $this->configHelper->getPageMetaDescription();
    }

    /**
     * @return int
     */
    public function getDefaultRadius()
    {
        return $this->configHelper->getDefaultRadius(true);
    }

    /**
     * @return array
     */
    public function getRadiusOptions()
    {
        return $this->configHelper->getRadiusOptions();
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->getCountryName($this->getDefaultCountryCode());
    }

    /**
     * @return string
     */
    public function getDefaultCountryCode()
    {
        return $this->configHelper->getDefaultCountry();
    }

    /**
     * @return array
     */
    public function getAvailableCountries()
    {
        return $this->configHelper->getAvailableCountries();
    }

    /**
     * @return mixed
     */
    public function getDefaultImage()
    {
        return $this->configHelper->getDefaultImage();
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->configHelper->getSortOrder();
    }

    /**
     * @return int
     */
    public function getExtendRadius()
    {
        return $this->configHelper->getExtendRadius();
    }

    /**
     * @return mixed
     */
    public function getSearchMinLength()
    {
        return $this->configHelper->getSearchMinLength();
    }

    /**
     * @return mixed
     */
    public function getExclusiveIcon()
    {
        return $this->configHelper->getExclusiveIcon();
    }

    /**
     * @return bool
     */
    public function isEnableDirections()
    {
        return $this->configHelper->isEnableDirections();
    }

    /**
     * @return mixed|string
     */
    public function getMetric()
    {
        return $this->configHelper->getMetric();
    }

    /**
     * @return int
     */
    public function getStoresOnLocatorPage()
    {
        return $this->configHelper->getStoresOnLocatorPage();
    }

    /**
     * Retrieve entity country name
     *
     * @param string $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        return $this->getDirectoryHelper()->getCountryName($countryCode);
    }

    /**
     * @return Directory|mixed|null
     */
    protected function getDirectoryHelper()
    {
        if (null === $this->directoryCountryHelper) {
            $this->directoryCountryHelper = ObjectManager::getInstance()->get(Directory::class);
        }
        return $this->directoryCountryHelper;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @return boolean
     */
    public function getOpenInPopup()
    {
        return $this->configHelper->getOpenInPopup($this->_storeManager->getStore()->getId());
    }

    /**
     * @since 1.5.0
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->getApiKeyHelper()->getApiKey();
    }

    /**
     * @since 1.5.0
     * @return ApiKey|mixed|null
     */
    protected function getApiKeyHelper()
    {
        if (null === $this->apiKeyHelper) {
            $this->apiKeyHelper = ObjectManager::getInstance()->get(APiKey::class);
        }

        return $this->apiKeyHelper;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->getUrlModifier()->modify(parent::getUrl($route, $params));
    }

    /**
     * @return UrlModifier
     */
    private function getUrlModifier(): UrlModifier
    {
        if (null === $this->urlModifier) {
            $this->urlModifier = ObjectManager::getInstance()->get(UrlModifier::class);
        }

        return $this->urlModifier;
    }

    /**
     * @return bool
     */
    public function isAskToUseGeolocationOnFirstVisit(): bool
    {
        return $this->configHelper->isAskToUseGeolocationOnFirstVisit();
    }
}
