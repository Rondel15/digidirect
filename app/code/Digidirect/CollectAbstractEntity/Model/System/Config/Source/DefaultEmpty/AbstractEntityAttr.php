<?php
namespace Digidirect\CollectAbstractEntity\Model\System\Config\Source\DefaultEmpty;

use Digidirect\CollectAbstractEntity\Model\System\Config\Source\AbstractEntityAttr as AbstractEntityAttrParent;

/**
 * Class AbstractEntityAttr
 * @package Digidirect\CollectAbstractEntity\Model\System\Config\Source\DefaultEmpty
 */
class AbstractEntityAttr extends AbstractEntityAttrParent
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $emptyValue = [['value' => '', 'label' => '']];

        return array_merge($emptyValue, parent::toOptionArray());
    }
}
