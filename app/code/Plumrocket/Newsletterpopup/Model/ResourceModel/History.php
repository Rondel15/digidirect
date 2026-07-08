<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class History extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('plumrocket_newsletterpopup_history', 'entity_id');
    }

    public function getActionMainTable()
    {
        return $this->getTable('plumrocket_newsletterpopup_history_action');
    }

    public function insertOnDuplicate($table, array $data, array $fields = [])
    {
        // $table = $this->_resources->getTableName($table);
        // $table = $this->getTable($table)
        $this->_getConnection('write')->insertOnDuplicate($table, $data, $fields);
        return $this->_getConnection('write')->lastInsertId();
    }
}
