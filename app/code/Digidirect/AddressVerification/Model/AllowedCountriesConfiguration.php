<?php

namespace Digidirect\AddressVerification\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class AllowedCountriesConfiguration
{
    /**
     * @var ResourceModel\Location
     */
    protected $location;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $allowedCountriesByStore = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AllowedCountriesConfiguration constructor.
     *
     * @param ResourceModel\Location $location
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig0000000
     */
    public function __construct(
        \Digidirect\AddressVerification\Model\ResourceModel\Location $location,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->location = $location;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getAllowedCountries()
    {
        $store = $this->storeManager->getStore();
        $currentStoreId = $store->getId();
        if (!isset($this->allowedCountriesByStore[$currentStoreId])) {
            $countries = $this->scopeConfig->getValue(
                'general/country/allow',
                ScopeInterface::SCOPE_STORE
            );
            $countries = explode(',', $countries);
            /**
             * If not enabled countries at all - skip without carrying about uploaded configuration
             */
            if (empty($countries)) {
                $this->allowedCountriesByStore[$currentStoreId] = $countries;
                return $this->allowedCountriesByStore[$currentStoreId];
            }

            $uploadedCountries = $this->location->getAllowedCountriesByStore(
                $currentStoreId, $store->getWebsiteId()
            );

            $this->allowedCountriesByStore[$currentStoreId] = array_intersect($countries, $uploadedCountries);
        }
        return $this->allowedCountriesByStore[$currentStoreId];
    }
}
