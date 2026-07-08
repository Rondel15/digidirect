<?php

namespace Digidirect\Vii\Model\ResourceModel;

use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class UndoQueue extends AbstractDb
{
    const TABLE_NAME = 'digidirect_abstract_gift_card_undo_queue';
    const ID = 'id';
    const ABSTRACT_GIFT_CARD_ENTITY_ID = 'abstract_gift_card_entity_id';
    const QUOTE_ID = 'quote_id';
    const LAST_TRANS_ID = 'last_trans_id';
    const TOKEN = 'token';
    const STATUS = 'status';

    /**
     * Initialize table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID);
    }

    /**
     * @param int $id
     * @return array
     * @throws LocalizedException
     */
    public function getById($id)
    {
        $sql = $this->getConnection()
            ->select()
            ->from($this->getMainTable())
            ->where('id = ?', $id);
        return $this->getConnection()->fetchRow($sql);
    }

    /**
     * @param array $whereConditions
     * @return array
     * @throws LocalizedException
     */
    public function getRecordByCondition($whereConditions)
    {
        $sql = $this->getConnection()
            ->select()
            ->from($this->getMainTable());
        foreach ($whereConditions as $field => $value) {
            $sql->where($field . ' = ?', $value);
        }
        return $this->getConnection()->fetchRow($sql);
    }

    /**
     * @param array $data
     * @return int
     * @throws LocalizedException
     */
    public function addRecord($data)
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $data
        );
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAllRecords()
    {
        return $this->getConnection()->fetchAll(
            $this->getConnection()->select()
                ->from($this->getMainTable())->where('status = ?', AbstractGiftCardEntity::STATUS_HOLD)
        );
    }

    /**
     * @param int $id
     * @return int
     * @throws LocalizedException
     */
    public function deleteRecordById($id)
    {
        return $this->getConnection()->delete(
            $this->getMainTable(),
            [self::ID . ' = ?' => $id]
        );
    }

    /**
     * @param array $data
     * @param array $whereConditions
     * @return $this
     * @throws LocalizedException
     */
    public function updateRecord($data, $whereConditions)
    {
        $this->getConnection()->update(
            $this->getTable($this->getMainTable()),
            $data,
            $whereConditions
        );
        return $this;
    }

    /**
     * @param int $id
     * @return int
     * @throws LocalizedException
     */
    public function getStatus($id)
    {
        $sql = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), [self::STATUS])
            ->where('id = ?', $id);
        return $this->getConnection()->fetchOne($sql);
    }
}
