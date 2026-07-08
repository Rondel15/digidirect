<?php
namespace Digidirect\AddressVerification\Model;

use Digidirect\AddressVerification\Api\ImportReportRepositoryInterface;

/**
 * Class ImportReportRepository
 * @package Digidirect\AddressVerification\Model
 */
class ImportReportRepository implements ImportReportRepositoryInterface
{
    /**
     * @var ResourceModel\ImportReport
     */
    protected $resourceModel;

    /**
     * ImportReportRepository constructor.
     * @param ResourceModel\ImportReport $resourceModel
     */
    public function __construct(
        \Digidirect\AddressVerification\Model\ResourceModel\ImportReport $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param string $countryCode
     * @param int $type
     * @param int|null $storeId
     * @param int|null $websiteId
     * @param bool $useDefault
     * @return null
     */
    public function getLastImportTime($countryCode, $type, $storeId, $websiteId, $useDefault = false)
    {
        if ($useDefault) {
            $report = $this->resourceModel->loadReportInherit($countryCode, $type, $storeId, $websiteId);
        } else {
            $report = $this->resourceModel->loadReportByStore($countryCode, $type, $storeId, $websiteId);
        }
        return $report !== false && isset($report['last_import_time'])  ? $report['last_import_time'] : null;
    }

    /**
     * @param string $countryCode
     * @param int $type
     * @param string $time
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return int
     */
    public function updateReport($countryCode, $type, $time, $storeId, $websiteId)
    {
        return $this->resourceModel->updateReport($countryCode, $type, $time, $storeId, $websiteId);
    }
}
