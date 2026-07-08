<?php
namespace Digidirect\AbstractEntity\Model\Config\Source;

class AttributeSetWithEmpty extends AttributeSet
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [[
            'label' => ' ',
            'value' => '',
        ]];
        return array_merge($options, parent::toOptionArray());
    }
}
