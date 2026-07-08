<?php

namespace Digidirect\CollectAbstractEntity\Model\ResourceModel;

class AbstractEntity extends \Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity
{
    /**
     * @param int $attributeSetId
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeInAttributeSet($attributeSetId, $attributeCode)
    {
        $statusAttribute = $this->getAttribute($attributeCode);
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getTable('eav_entity_attribute'), ['entity_attribute_id'])
            ->where('attribute_id = ?', $statusAttribute->getId())
            ->where('attribute_set_id = ?', $attributeSetId)
            ->where('entity_type_id = ?', $this->getEntityType()->getEntityTypeId());
        return $adapter->fetchOne($select) > 0;
    }
}
