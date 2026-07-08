<?php
namespace Digidirect\AddressVerification\Api;

/**
 * Interface ImportReportRepositoryInterface
 * @package Digidirect\AddressVerification\Model
 */
interface ImportReportRepositoryInterface
{
    /**
     * @param string $countryCode
     * @param int $type
     * @param int|null $storeId
     * @param int|null $websiteId
     * @param bool $useDefault
     * @return string|null
     */
    public function getLastImportTime($countryCode, $type, $storeId, $websiteId, $useDefault = false);

    /**
     * @param string $countryCode
     * @param int $type
     * @param string $time
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return int
     */
    public function updateReport($countryCode, $type, $time, $storeId, $websiteId);
}
