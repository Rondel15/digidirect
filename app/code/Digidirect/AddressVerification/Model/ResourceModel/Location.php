<?php

namespace Digidirect\AddressVerification\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;

/**
 * Class Location
 *
 * @package Digidirect\AddressVerification\Model\ResourceModel
 */
class Location extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAIN_TABLE = 'digidirect_addressverification_locations';

    /**
     * Location constructor.
     *
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
     * @param array $data
     * @param string $countryCode
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return int
     */
    public function saveData(array $data, $countryCode, $storeId = null, $websiteId = null)
    {
        $connection = $this->getConnection();

        if ($storeId !== null) {
            $connection->delete(
                self::MAIN_TABLE,
                ['store_id = ?' => (int)$storeId, 'country_code = ?' => $countryCode]
            );
        } elseif ($websiteId) {
            $connection->delete(
                self::MAIN_TABLE,
                ['website_id = ?' => (int)$websiteId, 'country_code = ?' => $countryCode]
            );
        }
        return $connection->insertMultiple(self::MAIN_TABLE, $data);
    }

    /**
     * @param string $path
     * @return array
     */
    public function loadAupostConfig($path)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('core_config_data'))
            ->where('path = ?', $path);
        return $connection->fetchAll($select);
    }

    /**
     * @param int $storeId
     * @param int|null $websiteId
     * @return array
     */
    public function getAllowedCountriesByStore($storeId, $websiteId = null)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), ['country_code'])
            ->where($this->getQuoteInto('store_id IN (?)', [0, $storeId]))
            ->distinct();

        if ($websiteId) {
            $select->orWhere($this->getQuoteInto('website_id IN (?)', [0, $websiteId]));
        }

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param string $text
     * @param mixed $value
     * @param null $type
     * @param null $count
     * @return string
     */
    protected function getQuoteInto($text, $value, $type = null, $count = null)
    {
        return $this->getConnection()->quoteInto($text, $value, $type, $count);
    }
}
