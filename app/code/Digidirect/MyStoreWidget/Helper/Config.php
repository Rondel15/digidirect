<?php

namespace Digidirect\MyStoreWidget\Helper;

use Digidirect\MyStoreWidget\Model\Config\Source\SearchType;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Digidirect\MyStoreWidget\Model\Config\Source\GeoLocationBehavior;
use Digidirect\MyStoreWidget\Model\System\Config\Backend\ShippingAddressInfo;
use Magento\Directory\Model\AllowedCountries;

/**
 * Class Config
 * @package Digidirect\MyStoreWidget\Helper
 */
class Config extends AbstractHelper
{
    const XML_PATH_ENABLED = 'digidirect_mystorewidget/general/enable';
    const XML_PATH_ENTITIES = 'digidirect_mystorewidget/general/entities';
    const XML_PATH_SEARCH_TYPE = 'digidirect_mystorewidget/general/search_type';
    const XML_PATH_ATTRIBUTES = 'digidirect_mystorewidget/general/search_attributes';
    const XML_PATH_RESPONSE_ATTRIBUTES = 'digidirect_mystorewidget/general/response_attributes';
    const XML_PATH_SEARCH_MIN_LENGTH = 'digidirect_mystorewidget/general/search_min_length';
    const XML_PATH_COOKIE_LIFETIME = 'digidirect_mystorewidget/general/remember_store_cookie_lifetime';
    const XML_PATH_SHOW_WIDGET_ON_CHECKOUT = 'digidirect_mystorewidget/general/show_widget_on_checkout';
    const DEFAULT_COOKIE_LIFETIME = 86400;

    const XML_PATH_SHIPPING_ADDRESS_ENABLED = 'digidirect_mystorewidget/shipping_address/enable';
    const XML_PATH_SHIPPING_ADDRESS_FIELDS_INFO = 'digidirect_mystorewidget/shipping_address/fields_info';
    const XML_PATH_SHIPPING_ADDRESS_VERIFY_ON_CHECKOUT = 'digidirect_mystorewidget/shipping_address/verify_on_checkout';

    const XML_PATH_GOOGLE_AUTO_SUGGEST_ENABLE = 'digidirect_mystorewidget/google_auto_suggest/enable';
    const XML_PATH_GOOGLE_AUTO_SUGGEST_API_KEY = 'digidirect_mystorewidget/google_auto_suggest/api_key';

    const XML_PATH_GEOLOCATION_ENABLE = 'digidirect_mystorewidget/geo_location/enable';
    const XML_PATH_GEOLOCATION_BEHAVIOR = 'digidirect_mystorewidget/geo_location/behavior';

    const XML_PATH_DISABLED_AUTOCOMPLITE_MOBILE = 'digidirect_mystorewidget/geo_location/disable_autocomplite_mobile_divice';

    /**
     * @var ShippingAddressInfo
     */
    protected $shippingAddressInfo;

    /**
     * Config constructor.
     * @param Context $context
     * @param ShippingAddressInfo $shippingAddressInfo
     */
    public function __construct(
        Context $context,
        ShippingAddressInfo $shippingAddressInfo
    ) {
        parent::__construct($context);
        $this->shippingAddressInfo = $shippingAddressInfo;
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_ENTITIES, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_ATTRIBUTES, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_RESPONSE_ATTRIBUTES, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getSearchMinLength()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_MIN_LENGTH, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return int
     */
    public function getCookieLifeTime()
    {
        $configValue = (int)$this->scopeConfig->getValue(self::XML_PATH_COOKIE_LIFETIME, ScopeInterface::SCOPE_WEBSITE);
        $duration = $configValue ? $configValue : self::DEFAULT_COOKIE_LIFETIME;

        return (int)$duration;
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     * @return array
     */
    protected function getMultiSelectConfig($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $value = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
        if ($value) {
            return explode(',', $value);
        }
        return [];
    }

    /**
     * @return bool
     */
    public function isGeoLocationEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GEOLOCATION_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isNeedKeepGeolocation()
    {
        $behavior = $this->scopeConfig->getValue(
            self::XML_PATH_GEOLOCATION_BEHAVIOR,
            ScopeInterface::SCOPE_STORE
        );

        return $behavior === GeoLocationBehavior::KEEP;
    }

    /**
     * @return bool
     */
    public function isMyStoreFromShippingAddressEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHIPPING_ADDRESS_ENABLED,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * @return array
     */
    public function getShippingAddressFieldsInfo()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_ADDRESS_FIELDS_INFO,
            ScopeInterface::SCOPE_WEBSITES
        );
        return $this->shippingAddressInfo->convertValueToArray($fields);
    }

    /**
     * @return bool
     */
    public function verifyShippingAddressOnCheckout()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHIPPING_ADDRESS_VERIFY_ON_CHECKOUT,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * @return bool
     */
    public function isGoogleAutoSuggestEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GOOGLE_AUTO_SUGGEST_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getGoogleAutoSuggestApiKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GOOGLE_AUTO_SUGGEST_API_KEY,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * @return array
     */
    public function getAvailableCountries()
    {
        return $this->getMultiSelectConfig(
            AllowedCountries::ALLOWED_COUNTRIES_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * @return bool
     */
    public function isShowWidgetOnCheckout()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_WIDGET_ON_CHECKOUT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return integer
     */
    public function getSearchType()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_TYPE);
    }

    /**
     * @return boolean
     */
    public function isSearchTypeTextInput()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_SEARCH_TYPE) === SearchType::TEXT_TYPE;
    }

    /**
     * @return bool
     */
    public function isDisabledGeoLocationAutocompliteForMobileDivices()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISABLED_AUTOCOMPLITE_MOBILE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
