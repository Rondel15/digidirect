<?php
namespace Digidirect\AbstractAttributes\Model\ResourceModel\Entity;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;

/**
 * Class Attribute
 * @package Digidirect\AbstractAttributes\Model\ResourceModel\Entity
 */
class Attribute extends \Magento\Eav\Model\ResourceModel\Entity\Attribute
{
    const ID_PREFIX = 'id_';

    /**
     * {@inheritdoc}
     */
    protected function _updateAttributeOption($object, $optionId, $option)
    {
        $optionId = str_replace(self::ID_PREFIX, '', $optionId);
        $intOptionId = parent::_updateAttributeOption($object, $optionId, $option);

        /** @see \Digidirect\AbstractAttributes\Model\OptionRepository::save */
        if ($object->getSaveAdvancedOption()) {
            $object->setAdvancedOptionId($intOptionId);
        }

        return $intOptionId;
    }
}
