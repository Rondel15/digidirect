<?php

namespace Digidirect\AddressVerification\Helper;

use Digidirect\AddressVerification\Api\CountryAddressAttributeRepositoryInterface;
use Digidirect\AddressVerification\Model\AllowedCountriesConfiguration;
use Digidirect\AddressVerification\Model\Config\Source\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Digidirect\AddressVerification\Helper
 */
class Autocomplete extends AbstractHelper
{
    const DIGIDIRECT_ADDRESS_VERIFICATION_GENERAL_CONFIG_PATH = 'digidirect_address_suggestion/general';

    const ALL_COUNTRIES = 'all';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var CountryAddressAttributeRepositoryInterface
     */
    protected $countryAddressAttributeRepository;

    /**
     * @var AllowedCountriesConfiguration
     */
    protected $allowedCountriesConfiguration;

    /**
     * Autocomplete constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param AllowedCountriesConfiguration $allowedCountriesConfiguration
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        AllowedCountriesConfiguration $allowedCountriesConfiguration
    ) {
        parent::__construct($context);
        $this->countryAddressAttributeRepository = $countryAddressAttributeRepository;
        $this->jsonEncoder = $jsonEncoder;
        $this->allowedCountriesConfiguration = $allowedCountriesConfiguration;
    }

    /**
     * Check if functionality is enabled
     *
     * @return string
     */
    public function getType()
    {
        return $this->scopeConfig->getValue(
            self::DIGIDIRECT_ADDRESS_VERIFICATION_GENERAL_CONFIG_PATH . '/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function shows current "Google Autocomoplete" state which was developed first
     * Added for backward compatibility - existing add-ons should be working with version 1.0.0 and 1.0.1 without
     * redeveloping
     *
     * If you need to check other options (AU Post as example) - use direct methods
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isGoogleEnabled();
    }

    /**
     * @return bool
     */
    public function isGoogleEnabled()
    {
        return $this->getType() == Type::GOOGLE;
    }

    /**
     * @return bool
     */
    public function isAuPostEnabled()
    {
        return $this->getType() == Type::AU_POST;
    }

    /**
     * Get google places api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            self::DIGIDIRECT_ADDRESS_VERIFICATION_GENERAL_CONFIG_PATH . '/api_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getAllowedCountries()
    {
        $countries = $this->scopeConfig->getValue('general/country/allow', ScopeInterface::SCOPE_STORE);
        return count(explode(',', $countries)) > 1 ? self::ALL_COUNTRIES : $countries;
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getCountryAddressAttributesJson()
    {
        $collection = $this->countryAddressAttributeRepository->getAll();
        $attributes = [];
        foreach ($collection as $item) {
            $attributes[$item->getCountryCode()] = $item->getAttributes();
        }
        return $this->jsonEncoder->encode($attributes);
    }

    /**
     * @param string $storeId
     * @param string $websiteId
     * @return DataObject
     */
    public function getScopeInfo($storeId, $websiteId)
    {
        $scopeId = 0;
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if (!empty($websiteId)) {
            $scopeType = ScopeInterface::SCOPE_WEBSITES;
            $scopeId = $websiteId;
        } elseif ($storeId !== '0') {
            $scopeType = ScopeInterface::SCOPE_STORES;
            $scopeId = $storeId;
        }
        return new DataObject(['scope_id' => $scopeId, 'scope_type' => $scopeType]);
    }

    /**
     * @return string
     */
    public function getAllowableCountries()
    {
        $countries = $this->allowedCountriesConfiguration->getAllowedCountries();
        return is_array($countries) ? implode(',', $countries) : '';
    }
}
