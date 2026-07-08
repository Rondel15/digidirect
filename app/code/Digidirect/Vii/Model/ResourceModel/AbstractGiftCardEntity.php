<?php

namespace Digidirect\Vii\Model\ResourceModel;

/**
 * Class AbstractGiftCardEntity
 * @package Digidirect\Vii\Model\ResourceModel
 */
class AbstractGiftCardEntity extends \Digidirect\AbstractGiftCard\Model\ResourceModel\AbstractGiftCardEntity
{
    const ENTITY_QUOTE_RELATED_TABLE = 'digidirect_abstract_gift_card_entity_quote';
    const ENTITY_STATUS_REVERSED = 4;

    /**
     * @param \Magento\Framework\DataObject $entityQuoteData
     * @param array $fields
     * @return $this
     */
    public function saveEntityQuoteData(\Magento\Framework\DataObject $entityQuoteData, $fields = [])
    {
        if ($entityQuoteData->getAbstractGiftCardEntityId() && $entityQuoteData->getQuoteId()) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(self::ENTITY_QUOTE_RELATED_TABLE),
                [
                    'quote_id'                      => $entityQuoteData->getQuoteId(),
                    'abstract_gift_card_entity_id'  => $entityQuoteData->getAbstractGiftCardEntityId(),
                    'token'                         => $entityQuoteData->getToken(),
                    'amount'                        => $entityQuoteData->getAmount(),
                    'status'                        => $entityQuoteData->getStatus(),
                ],
                $fields
            );
        }
        return $this;
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity $entity
     * @param int $quoteId
     * @return \Magento\Framework\DataObject|null
     */
    public function getEntityQuoteData($entity, $quoteId)
    {
        if ($entity->getEntityId()) {
            $select = $this->getConnection()
                ->select()
                ->from($this->getTable(self::ENTITY_QUOTE_RELATED_TABLE))
                ->where('abstract_gift_card_entity_id = ?', $entity->getEntityId())
                ->where('quote_id = ?', $quoteId);
            $row = $this->getConnection()->fetchRow($select);
            if (is_array($row) && !empty($row)) {
                $entityQuoteData = new \Magento\Framework\DataObject($row);
                return $entityQuoteData;
            }
        }
        return null;
    }

    /**
     * @param string $expireDate
     * @param int $isQueued
     * @return array
     */
    public function getExpiredEntityQuoteIds($expireDate, $isQueued = 0)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable(self::ENTITY_QUOTE_RELATED_TABLE), ['quote_id'])
            ->where('status = ?', \Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity::STATUS_HOLD)
            ->where('start_date < ?', $expireDate)
            ->where('is_queued = ?', $isQueued)
            ->group('quote_id');

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param int $entityId
     * @param int $quoteId
     * @return $this
     */
    public function deleteEntityQuoteData($entityId, $quoteId)
    {
        $this->getConnection()->delete(
            $this->getTable(self::ENTITY_QUOTE_RELATED_TABLE),
            ['abstract_gift_card_entity_id =?' => $entityId, 'quote_id =?' => $quoteId]
        );
        return $this;
    }

    /**
     * @param array $data
     * @param array $whereConditions
     * @return $this
     */
    public function updateEntityQuoteData($data, $whereConditions)
    {
        $this->getConnection()->update(
            $this->getTable(self::ENTITY_QUOTE_RELATED_TABLE),
            $data,
            $whereConditions
        );
        return $this;
    }
}
