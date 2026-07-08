<?php

namespace Digidirect\Digi\Setup;

use Digidirect\AbstractEntity\Setup\AbstractEntitySetup;

/**
 * Class KeyFeatureEntitySetup
 * @package Digidirect\Digi\Setup
 */
class KeyFeatureEntitySetup extends AbstractEntitySetup
{
    const ENTITY_NAME = 'Key Feature';

    protected $_attributeSetData = [
        'general' => [
            'attributes' => [
                'name',
                'status',
                'image'
            ],
            'order' => null
        ],
    ];

    /**
     * @param null $entities
     * @return void
     * @throws \Exception
     */
    public function installEntities($entities = null)
    {
        $newEntity = $this->getOrCreateAttributeSet(self::ENTITY_NAME);

        parent::installEntities($entities);

        $attributeSetId = $newEntity->getId();
        if ($attributeSetId) {

            //Add new group to set
            foreach ($this->_attributeSetData as $groupCode => $attributeData) {
                $this->addAttributeGroup(
                    \Digidirect\AbstractEntity\Model\AbstractEntity::ENTITY_TYPE,
                    $attributeSetId,
                    $groupCode,
                    !empty($attributeData['order']) ? $attributeData['order'] : null
                );
            }

            //Add attributes to attribute groups
            $storeGroupsCollection = $this->getAttributeGroupCollectionFactory()->setAttributeSetFilter($attributeSetId);
            $groupsIds = [];

            foreach ($storeGroupsCollection as $groups) {
                $groupsIds[$groups->getAttributeGroupCode()] = $groups->getAttributeGroupId();
            }

            foreach ($this->_attributeSetData as $groupCode => $attributeSetData) {
                foreach ($attributeSetData['attributes'] as $attributeCode) {
                    $this->addAttributeToGroup(
                        \Digidirect\AbstractEntity\Model\AbstractEntity::ENTITY_TYPE,
                        $attributeSetId,
                        $groupsIds[$groupCode],
                        $attributeCode
                    );
                }
            }
        }
    }
}