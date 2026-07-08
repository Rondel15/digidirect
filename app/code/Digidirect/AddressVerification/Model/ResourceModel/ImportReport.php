<?php
namespace Digidirect\AddressVerification\Model\ResourceModel;

use Digidirect\AddressVerification\Api\LocationRepositoryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;

/**
 * Class ImportReport
 * @package Digidirect\AddressVerification\Model\ResourceModel
 */
class ImportReport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'digidirect_addressverification_import_report';

    /**
     * Location constructor.
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'entity_id');
    }

    /**
     * @param string $countryCode
     * @param int $type
     * @param string $time
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return int
     */
    public function updateReport($countryCode, $type, $time, $storeId = null, $websiteId = null)
    {
        $report = $this->loadReportByStore($countryCode, $type, $storeId, $websiteId);
        if ($report !== false) {
            $result = $this->getConnection()->update(
                self::MAIN_TABLE,
                ['last_import_time' => $time],
                ['entity_id = ?' => $report['entity_id']]
            );
        } else {
            $result = $this->getConnection()->insert(
                self::MAIN_TABLE,
                [
                    'type' => (int)$type,
                    'last_import_time' => $time,
                    'country_code' => $countryCode,
                    'store_id' => $storeId,
                    'website_id' => $websiteId
                ]
            );
        }
        return $result;
    }

    /**
     * @param string $countryCode
     * @param string $type
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return array|bool
     */
    public function loadReportByStore($countryCode, $type, $storeId = null, $websiteId = null)
    {
        $report = false;
        if ($storeId !== null) {
            $report = $this->loadByTypeForStore($countryCode, $type, $storeId);
        } elseif ($websiteId) {
            $report = $this->loadByTypeForWebsite($countryCode, $type, $websiteId);
        }
        return $report;
    }

    /**
     * @param string $countryCode
     * @param int $type
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return array|bool
     */
    public function loadReportInherit($countryCode, $type, $storeId = null, $websiteId = null)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('type = ?', (int)$type)
            ->where('country_code = ?', $countryCode)
            ->limit(1);
        if ($storeId !== null) {
            $condition = new \Zend_Db_Expr(
                "IF ((SELECT COUNT(*) FROM " . $this->getMainTable() . " WHERE website_id = "
                . (int)$websiteId . " ) > 0, `website_id` = " . (int)$websiteId . ", store_id = "
                . LocationRepositoryInterface::DEFAULT_STORE_ID .")"
            );
            $select->where($condition);
        } elseif ($websiteId) {
            $select->where('store_id = ?', LocationRepositoryInterface::DEFAULT_STORE_ID);
        }
        return $this->getConnection()->fetchRow($select);
    }
    
    /**
     * @param string $countryCode
     * @param int $type
     * @param int $storeId
     * @return array
     */
    public function loadByTypeForStore($countryCode, $type, $storeId)
    {
        $select = $this->getConnection()->select()->from(self::MAIN_TABLE)
            ->where('country_code = ?', $countryCode)
            ->where('type = ?', (int)$type)
            ->where('store_id = ?', (int)$storeId);
        return $this->getConnection()->fetchRow($select);
    }

    /**
     * @param string $countryCode
     * @param int $type
     * @param int $websiteId
     * @return array
     */
    public function loadByTypeForWebsite($countryCode, $type, $websiteId)
    {
        $select = $this->getConnection()->select()->from(self::MAIN_TABLE)
            ->where('country_code = ?', $countryCode)
            ->where('type = ?', (int)$type)
            ->where('website_id = ?', (int)$websiteId);
        return $this->getConnection()->fetchRow($select);
    }
}
