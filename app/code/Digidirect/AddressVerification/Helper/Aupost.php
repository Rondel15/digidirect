<?php

namespace Digidirect\AddressVerification\Helper;

use Digidirect\AddressVerification\Api\CountryAddressAttributeRepositoryInterface;
use Digidirect\AddressVerification\Model\AllowedCountriesConfiguration;
use Digidirect\AddressVerification\Model\LocationRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Digidirect\AddressVerification\Model\ResourceModel\Location\Collection;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Aupost
 *
 * @package Digidirect\AddressVerification\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Aupost extends AbstractHelper
{
    /**
     * Postcodes delimiter
     */
    const POSTCODE_RANGE_DELIMITER = '-';

    /**
     * System config file path
     */
    const AUPOST_FILE_CONFIG_PATH = 'digidirect_address_suggestion/general/address_data_file';

    /**
     * Australian ISO code
     */
    const AU_ISO = 'AU';

    /**
     * @var \Digidirect\AddressVerification\Model\Config\Source\PostcodeRange
     */
    protected $postcodeRangeSource;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var \Digidirect\AddressVerification\Api\LocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var null
     */
    protected $fileConfig = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $localeDate;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var CountryAddressAttributeRepositoryInterface
     */
    protected $countryAddressAttributeRepository;

    /**
     * @var array
     */
    protected $countryAttributes = [];

    /**
     * @var AllowedCountriesConfiguration
     */
    protected $allowedCountriesConfiguration;

    /**
     * Aupost constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\AddressVerification\Model\Config\Source\PostcodeRange $postcodeRangeSource
     * @param \Digidirect\AddressVerification\Api\LocationRepositoryInterface $locationRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param StoreManager $storeManager
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository
     * @param AllowedCountriesConfiguration $allowedCountriesConfiguration
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\AddressVerification\Model\Config\Source\PostcodeRange $postcodeRangeSource,
        \Digidirect\AddressVerification\Api\LocationRepositoryInterface $locationRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        StoreManager $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository,
        AllowedCountriesConfiguration $allowedCountriesConfiguration
    ) {
        parent::__construct($context);
        $this->postcodeRangeSource = $postcodeRangeSource;
        $this->storeManager = $storeManager;
        $this->locationRepository = $locationRepository;
        $this->localeDate = $localeDate;
        $this->countryFactory = $countryFactory;
        $this->carrierFactory = $carrierFactory;
        $this->countryAddressAttributeRepository = $countryAddressAttributeRepository;
        $this->allowedCountriesConfiguration = $allowedCountriesConfiguration;
    }

    /**
     * @param null|int $storeId
     * @param string $scope
     * @return mixed
     */
    public function getAuPostFile($storeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            Autocomplete::DIGIDIRECT_ADDRESS_VERIFICATION_GENERAL_CONFIG_PATH . '/address_data_file',
            $scope,
            $storeId
        );
    }

    /**
     * @param Collection $collection
     * @param string $countryCode
     * @param string|null $region
     * @return array
     */
    public function prepareResponseData(Collection $collection, $countryCode, $region = null)
    {
        $result = [];
        foreach ($collection as $item) {
            $state = $this->findStateByPostCode($countryCode, $item->getPostcode());
            if ($region === null || (!empty($state['state']) && (strtolower($state['state']) == strtolower($region)))) {
                $result[] = [
                    'postcode' => $item->getPostcode(),
                    'suburb' => $item->getSuburb(),
                    'state' => $state,
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $countryCode
     * @param string $postcode
     * @return array
     */
    public function findStateByPostCode($countryCode, $postcode)
    {
        $result = ['state' => '', 'abbreviation' => ''];
        if ($this->isAttributeRequire($countryCode, 'region')) {
            foreach ($this->postcodeRangeSource->getList() as $abbreviation => $item) {
                if (!empty($item['postcode_range'])) {
                    if ($this->matchesPostcodeRange($postcode, $item['postcode_range'])) {
                        $result = ['state' => $item['state'], 'abbreviation' => $abbreviation];
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $postcode
     * @param array $ranges
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function matchesPostcodeRange($postcode, array $ranges)
    {
        $matches = false;
        $postcode = trim($postcode);
        if (!empty($postcode) && is_numeric($postcode)) {
            foreach ($ranges as $range) {
                $range = trim($range);
                if ($range) {
                    $rangeItems = explode(self::POSTCODE_RANGE_DELIMITER, $range);
                    if (count($rangeItems) > 1) {
                        list($from, $to) = $rangeItems;
                        if (is_numeric($from) && is_numeric($to) && ($from) && ($to)) {
                            if (($postcode >= $from) && ($postcode <= $to)) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $matches;
    }

    /**
     * @param int $storeId
     * @return null
     */
    public function getWebsiteIdByStore($storeId)
    {
        $websiteId = null;
        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getId() == $storeId) {
                $websiteId = $store->getWebsiteId();
                break;
            }
        }
        return $websiteId;
    }

    /**
     * @param string $postcode
     * @param string $suburb
     * @param string $region
     * @param string $countryCode
     * @return array
     */
    public function searchLocation($postcode, $suburb, $region, $countryCode)
    {
        $collection = $this->getSearchLocationCollection($postcode, $suburb, $countryCode);
        return $this->prepareResponseData($collection, $countryCode, $region);
    }

    /**
     * @param string $postcode
     * @param string $suburb
     * @param string $countryCode
     * @return Collection
     */
    public function getSearchLocationCollection($postcode, $suburb, $countryCode)
    {
        $collection = $this->locationRepository->findLocation($countryCode, $postcode, $suburb);
        $storeId = $this->storeManager->getStore()->getId();
        $websiteId = $this->getWebsiteIdByStore($storeId);
        if (!$this->isUseWebsite($storeId)) {
            $collection->addFieldToFilter('store_id', $storeId);
        } else {
            if (!$this->isUseDefault($websiteId)) {
                $collection->addFieldToFilter('website_id', $websiteId);
            } else {
                $collection->addFieldToFilter('store_id', LocationRepository::DEFAULT_STORE_ID);
            }
        }
        return $collection;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isUseWebsite($storeId)
    {
        foreach ($this->getFileConfig() as $item) {
            if ($item['scope'] == 'stores' && $item['scope_id'] == $storeId) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function isUseDefault($websiteId)
    {
        foreach ($this->getFileConfig() as $item) {
            if ($item['scope'] == 'websites' && $item['scope_id'] == $websiteId) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array|null
     */
    public function getFileConfig()
    {
        if ($this->fileConfig === null) {
            $this->fileConfig = $this->locationRepository->loadFileConfig(self::AUPOST_FILE_CONFIG_PATH);
        }
        return $this->fileConfig;
    }

    /**
     * @param string $countryCode
     * @param string $postcode
     * @param string $suburb
     * @param null|string $state
     * @param null|int $stateId
     * @param bool $strongValidation
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isCombinationValid(
        $countryCode,
        $postcode,
        $suburb,
        $state = null,
        $stateId = null,
        $strongValidation = true
    ) {
        if (!$strongValidation && !$postcode && !$suburb) {
            return true;
        }

        if (!in_array($countryCode, $this->allowedCountriesConfiguration->getAllowedCountries())) {
            return true;
        }

        $customerAttributes = $this->getCustomerAttributes($countryCode);
        $searchPostcode = in_array('postcode', $customerAttributes) ? $postcode : null;
        $searchSuburb = in_array('suburb', $customerAttributes) ? $suburb : null;
        $location = $this->getSearchLocationCollection($searchPostcode, $searchSuburb, $countryCode);

        if (!$location->getSize()) {
            return false;
        }

        $response = $this->prepareResponseData($location, $countryCode);
        if (!empty($stateId)) {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            foreach ($country->getRegions() as $region) {
                if ($region->getId() == $stateId) {
                    $state = $region->getDefaultName();
                }
            }
        }
        $isMatched = false;
        foreach ($response as $item) {
            if ($this->validateAttribute($countryCode, 'postcode', $item['postcode'], $postcode)
                && $this->validateAttribute($countryCode, 'suburb', $item['suburb'], $suburb)
                && $this->validateAttribute($countryCode, 'state', $item['state']['state'], $state)
            ) {
                $isMatched = true;
                break;
            }
        }
        return $isMatched;
    }

    /**
     * @param string $countryCode
     * @param string $attribute
     * @param string $findValue
     * @param string $expectedValue
     * @return bool
     */
    protected function validateAttribute($countryCode, $attribute, $findValue, $expectedValue)
    {
        switch ($attribute) {
            case 'state':
                $result = (empty($expectedValue) || !$this->isAttributeRequire($countryCode, 'region')
                    || strtolower($findValue) == strtolower($expectedValue));
                break;
            default:
                $result = (!$this->isAttributeRequire($countryCode, $attribute)
                    || strtolower($findValue) == strtolower($expectedValue));
        }
        return $result;
    }

    /**
     * @param string $countryCode
     * @param string $attribute
     * @return bool
     */
    protected function isAttributeRequire($countryCode, $attribute)
    {
        $customerAttributes = $this->getCustomerAttributes($countryCode);
        return in_array($attribute, $customerAttributes);
    }

    /**
     * @param string $date
     * @param int $format
     * @param bool $showTime
     * @param null $timezone
     * @return \DateTime|string
     */
    public function formatDate(
        $date,
        $format = \IntlDateFormatter::SHORT,
        $showTime = true,
        $timezone = null
    ) {
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * @param string $carrierCode
     * @return bool
     */
    public function isShippingAddressValidationRequired($carrierCode)
    {
        $method = 'isShippingAddressValidationRequired';
        $result = true;
        $carrier = $this->carrierFactory->getIfActive($carrierCode);
        if ($carrier instanceof AbstractCarrier && method_exists($carrier, $method)) {
            $result = $carrier->{$method}();
        }
        return $result;
    }

    /**
     * @param string $countryCode
     * @return mixed
     */
    public function getCustomerAttributes($countryCode)
    {
        if (!isset($this->countryAttributes[$countryCode])) {
            $this->countryAttributes[$countryCode] = $this->countryAddressAttributeRepository
                ->getAddressAttributesByCountryCode($countryCode);
        }
        return $this->countryAttributes[$countryCode];
    }

    /**
     * @param string $scopeType
     * @param string $scopeCode
     * @return mixed
     */
    public function getCurrentConfigCountry($scopeType, $scopeCode)
    {
        return $this->scopeConfig->getValue('digidirect_address_suggestion/general/country_code', $scopeType, $scopeCode);
    }
}
