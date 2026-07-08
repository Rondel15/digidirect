<?php

namespace Digidirect\AI\Model\ResourceModel\Integrations;

use Digidirect\AI\Api\Data\ScheduleInterface;
use Magento\Framework\DB\Adapter\DuplicateException;

/**
 * Class Schedule
 * @package Digidirect\AI\Model\ResourceModel\Integrations
 */
class Schedule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    protected $uniqueDbIndexes = [
        [
            ScheduleInterface::PROCESS_CODE,
            ScheduleInterface::RUN_OPTIONS,
        ]
    ];

    /**
     * Schedule constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_ai_schedule_run', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return null|string
     * @throws DuplicateException
     */
    protected function getObjectIdUsingUniqueIndexes(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @see \Magento\Framework\DB\Adapter\Pdo\Mysql::getIndexList
         */
        $indexList = $this->getConnection()->getIndexList($this->getMainTable());

        $id = null;
        foreach ($indexList as $index) {
            if ($index['INDEX_TYPE'] == \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY) {
                continue;
            }
            $select = $this->getConnection()->select();
            $select->from($this->getMainTable(), $this->getIdFieldName());
            foreach ($index['COLUMNS_LIST'] as $field) {
                if ($object->getData($field) === null) {
                    continue;
                }
                $select->where($field . ' = ?', $object->getData($field));
            }
            $indexId = $this->getConnection()->fetchOne($select);
            if ($id && $indexId != $id) {
                throw new DuplicateException();
            }
            $id = $indexId;
        }

        return $id;
    }

    /**
     * replaced insert with insertOnDuplicate
     *
     * {@inheritdoc}
     */
    protected function saveNewObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $bind = $this->_prepareDataForSave($object);
        if ($this->_isPkAutoIncrement) {
            unset($bind[$this->getIdFieldName()]);
        }
        if ($id = $this->getObjectIdUsingUniqueIndexes($object)) {
            $object->setId($id);
            $condition = $this->getConnection()->quoteInto($this->getIdFieldName() . '=?', $object->getId());
            $this->getConnection()->update($this->getMainTable(), $bind, $condition);
        } else {
            $this->getConnection()->insert($this->getMainTable(), $bind);

            if ($this->_isPkAutoIncrement) {
                $object->setId($this->getConnection()->lastInsertId($this->getMainTable()));
            }
        }

        if ($this->_useIsObjectNew) {
            $object->isObjectNew(false);
        }
    }
}
