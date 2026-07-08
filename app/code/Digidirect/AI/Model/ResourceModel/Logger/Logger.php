<?php

namespace Digidirect\AI\Model\ResourceModel\Logger;

use Digidirect\AI\Model\Logger\Types\TypesInterface;

/**
 * Class Logger
 * @method \Magento\Framework\Db\Adapter\Pdo\Mysql getConnection()
 * @package Digidirect\AI\Model\ResourceModel\Logger
 */
class Logger extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'digidirect_ai_logs';
    const TABLE_DETAILS_NAME = 'digidirect_ai_logs_details';
    const TABLE_CONNECTOR_DATA_NAME = 'digidirect_ai_logs_connector_data';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_ai_logs', 'id');
    }

    /**
     * @return string
     */
    protected function getDetailsTable()
    {
        return $this->getTable(self::TABLE_DETAILS_NAME);
    }

    /**
     * @return string
     */
    protected function getConnectorDataTable()
    {
        return $this->getTable(self::TABLE_CONNECTOR_DATA_NAME);
    }

    /**
     * @param array $data
     * @return int
     */
    public function saveLogData(array $data)
    {
        $this->getConnection()
            ->insert($this->getMainTable(), $data);
        return $this->getConnection()->lastInsertId($this->getMainTable());
    }

    /**
     * @param int $logId
     * @param array $data
     * @return $this
     */
    public function updateLogData($logId, array $data)
    {
        $this->getConnection()
            ->update($this->getMainTable(), $data, ['id = ?' => $logId]);
        return $this;
    }

    /**
     * @param TypesInterface $dbLogger
     * @return string
     */
    public function hasDetails(TypesInterface $dbLogger)
    {
        $logId = $dbLogger->getRecordIdentifier();
        $logId = $this->getLogDetailsColumnValue($logId, 'log_id');
        return !empty($logId);
    }

    /**
     * @param TypesInterface $dbLogger
     * @param string $message
     * @return $this
     */
    public function setDetails(TypesInterface $dbLogger, $message)
    {
        $logId = $dbLogger->getRecordIdentifier();
        if ($logId && !empty($message)) {
            $adapter = $this->getConnection();
            $data = [
                'log_id' => $logId,
                'details' => $message,
            ];
            $adapter->insertOnDuplicate($this->getDetailsTable(), $data, ['details']);
        }

        return $this;
    }

    /**
     * @param TypesInterface $dbLogger
     * @param string $message
     * @return $this
     */
    public function addDetails(TypesInterface $dbLogger, $message)
    {
        $logId = $dbLogger->getRecordIdentifier();
        if ($logId && !empty($message)) {
            $adapter = $this->getConnection();
            $detailsExpr = $adapter->getConcatSql(['details', $adapter->quote($message)], PHP_EOL);
            $adapter->update($this->getDetailsTable(), ['details' => $detailsExpr], ['log_id =?' => $logId]);
        }

        return $this;
    }

    /**
     * @param int $logId
     * @param string $column
     * @return string|false
     */
    protected function getLogDetailsColumnValue($logId, $column)
    {
        if (!$logId) {
            return false;
        }

        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getDetailsTable(), $column)
            ->where('log_id = ?', $logId)
            ->limit(1);
        return $adapter->fetchOne($select);
    }

    /**
     * @param int $logId
     * @param string $message
     * @return $this
     * @deprecated
     */
    public function update($logId, $message)
    {
        if ($logId && !empty($message)) {
            $lodDetailsTable = $this->getDetailsTable();
            $selectPreviousDetails = $this->getConnection()
                ->select()
                ->from($lodDetailsTable, ['id', 'details'])
                ->where('log_id = ?', $logId)
                ->limit(1);

            $detailsPrevious = $this->getConnection()->fetchRow($selectPreviousDetails);
            $data = [];

            $data['log_id'] = $logId;
            if ($detailsPrevious['id']) {
                $data['details'] = $detailsPrevious['details'] . PHP_EOL . $message;
                $this->getConnection()->update($lodDetailsTable, $data, 'id=' . $detailsPrevious['id']);
            } else {
                $data['details'] = $message;
                $this->getConnection()->insert($lodDetailsTable, $data);
            }
        }

        return $this;
    }

    /**
     * @param int $logId
     * @param array $connectorData
     * @return $this
     */
    public function updateConnectorData($logId, $connectorData)
    {
        if ($logId && !empty($connectorData)) {
            $connectorData['log_id'] = $logId;
            $this->getConnection()->insert(
                $this->getConnectorDataTable(),
                $connectorData
            );
        }

        return $this;
    }

    /**
     * @param mixed $log
     * @return mixed
     */
    public function attachData(&$log)
    {
        $this->attachLogData($log);
        $this->attachConnectorData($log);

        return $log;
    }

    /**
     * @param mixed $log
     * @return mixed
     */
    public function attachLogData($log)
    {
        if (!$log->getId()) {
            return $log;
        }
        $details = $this->getLogDetailsColumnValue($log->getId(), 'details');
        $details = $details ? $details : __('No Data Logged');

        return $log->setDetails($details);
    }

    /**
     * @param mixed $log
     * @return mixed
     */
    public function attachConnectorData($log)
    {
        if (!$log->getId()) {
            return $log;
        }

        $select = $this->getConnection()
            ->select()
            ->from($this->getConnectorDataTable())
            ->where("log_id = ?", $log->getId());

        $result = $this->getConnection()->fetchAll($select);

        return $log->setConnectorData($result);
    }

    /**
     * @return void
     */
    public function deleteRecords()
    {
        $this->getConnection()->delete($this->getMainTable());
    }

    /**
     * @param string $processCode
     * @return array
     */
    public function getLastLogByProcessCode($processCode)
    {
        $logsTable = $this->getMainTable();
        $logsDetailsTable = $this->getDetailsTable();
        $query = $this->getConnection()
            ->select()
            ->from($logsTable)
            ->where('process_code = ?', $processCode)
            ->order('created_at DESC')
            ->limit(1)
            ->joinLeft($logsDetailsTable, $logsTable . '.id = ' . $logsDetailsTable . '.log_id');

        return $this->getConnection()->fetchRow($query);
    }

    /**
     * @param int $days
     * @return $this
     */
    public function removeRecordsOlderThanDays($days)
    {
        $dateTime = new \DateTime();
        $dateTime->sub(new \DateInterval(sprintf('P%dD', $days)));

        $this->getConnection()
            ->delete($this->getMainTable(), ['created_at < ?' => $dateTime->format('Y-m-d 00:00:00')]);
        return $this;
    }
}
