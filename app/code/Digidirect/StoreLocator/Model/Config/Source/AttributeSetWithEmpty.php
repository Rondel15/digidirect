<?php

namespace Digidirect\StoreLocator\Model\Config\Source;

/**
 * @since 1.16.1
 * Introduced as we need empty option if we do not rename default entity
 */
class AttributeSetWithEmpty extends AttributeSet
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = parent::toOptionArray();
        array_unshift(
            $result,
            ['value' => null, 'label' => '-']
        );

        return $result;
    }
}
