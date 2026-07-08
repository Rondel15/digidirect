<?php

namespace Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Import;

use Digidirect\ExtendedShippingRates\Model\Zone;
use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;

/**
 * Class Data
 * @package Digidirect\ExtendedShippingRates\Model\ResourceModel\Zone\Import
 */
class Data extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->getTable(Zone::ZONE_TABLE_NAME), ZoneInterface::ZONE_ID);
    }

    /**
     * Save import rows bunch.
     *
     * @param array $data
     * @return int
     */
    public function saveBunch(array $data)
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $data
        );
    }

    /**
     * @param array $zoneIds
     * @return array
     */
    public function getSavedRowIds($zoneIds)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [ZoneInterface::ZONE_ID => ZoneInterface::ENTITY_ID])
            ->where(ZoneInterface::ZONE_ID . ' in (?)', $zoneIds);
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $data
     * @return int
     */
    public function saveDefaultStore($data)
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getTable(Zone::ZONE_STORE_TABLE_NAME),
            $data
        );
    }
}
