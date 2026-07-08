<?php
namespace Digidirect\AddressVerification\Api;

/**
 * Interface LocationRepositoryInterface
 * @package Digidirect\AddressVerification\Model
 */
interface LocationRepositoryInterface
{
    const DEFAULT_STORE_ID = 0;
    
    /**
     * @param array $data
     * @param string $countryCode
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return mixed
     */
    public function saveData(array $data, $countryCode, $storeId, $websiteId);

    /**
     * @param string $countryCode
     * @param int|null $postcode
     * @param int|null $suburb
     * @return \Digidirect\AddressVerification\Model\ResourceModel\Location\Collection
     */
    public function findLocation($countryCode, $postcode = null, $suburb = null);

    /**
     * @param string $path
     * @return array
     */
    public function loadFileConfig($path);
}
