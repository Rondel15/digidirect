<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digidirect\AI\Model\ResourceModel\Logger\Logger;

use Magento\Framework\DataObject;
use Digidirect\AI\Model;

/**
 * CMS page collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Used only for grid paging / selection working
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Digidirect\AI\Model\Logger\Types\Db::class,
            \Digidirect\AI\Model\ResourceModel\Logger\Logger::class
        );
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'queue_id') {
            $this->joinQueueLogTable();
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return $this
     */
    public function joinQueueLogTable()
    {
        if (!$this->getFlag('queue_log_table_joined')) {
            $this->getSelect()->joinLeft(
                ['aiql' => $this->getTable('digidirect_ai_queue_log')],
                'main_table.id = aiql.log_id',
                ['queue_id']
            );
            $this->setFlag('queue_log_table_joined', true);
        }
        return $this;
    }
}
