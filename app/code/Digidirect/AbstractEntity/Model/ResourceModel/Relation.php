<?php
namespace Digidirect\AbstractEntity\Model\ResourceModel;

use Digidirect\AbstractEntity\Model\RelationInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Relation
 * @package Digidirect\AbstractEntity\Model\ResourceModel
 */
class Relation extends AbstractDb implements RelationInterface
{
    const PARENT_ATTRIBUTE_SET_ID = 'parent_attribute_set_id';
    const IS_PARENT_REQUIRED = 'is_parent_required';

    /**
     * Initialize resource model and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_abstractentity_entity_relation', AbstractEntityInterface::ATTRIBUTE_SET_ID);
    }

    /**
     * @param int $entityId
     * @param array $data
     * @return $this
     */
    public function processRelation($entityId, $data)
    {
        $isParentRequired = $data[self::IS_PARENT_REQUIRED] ?? 0;
        if (!empty($data[self::PARENT_ATTRIBUTE_SET_ID])) {
            $parentEntityId = (int)$data[self::PARENT_ATTRIBUTE_SET_ID];
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                [
                    AbstractEntityInterface::ATTRIBUTE_SET_ID => $entityId,
                    self::PARENT_ATTRIBUTE_SET_ID => $parentEntityId,
                    self::IS_PARENT_REQUIRED => $isParentRequired
                ]
            );
        } else {
            $this->getConnection()->update(
                $this->getMainTable(),
                [
                    self::IS_PARENT_REQUIRED => $isParentRequired
                ],
                AbstractEntityInterface::ATTRIBUTE_SET_ID . ' = ' . $entityId
            );
        }

        return $this;
    }

    /**
     * @param int $id
     * @return string
     */
    public function getParentAttributeSetId($id)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable(),
                [self::PARENT_ATTRIBUTE_SET_ID]
            )
            ->where(AbstractEntityInterface::ATTRIBUTE_SET_ID . ' = ?', $id);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $id
     * @return string
     */
    public function getIsParentRequired($id)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable(),
                [self::IS_PARENT_REQUIRED]
            )
            ->where(AbstractEntityInterface::ATTRIBUTE_SET_ID . ' = ?', $id);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getAttributeSetIdsByParent($id)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable(),
                [AbstractEntityInterface::ATTRIBUTE_SET_ID]
            )
            ->where(self::PARENT_ATTRIBUTE_SET_ID . ' = ?', $id);

        return $this->getConnection()->fetchCol($select);
    }
}
