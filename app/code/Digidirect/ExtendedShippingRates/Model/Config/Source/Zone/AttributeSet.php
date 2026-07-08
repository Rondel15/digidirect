<?php

namespace Digidirect\ExtendedShippingRates\Model\Config\Source\Zone;

/**
 * Class AttributeSet
 * @package Digidirect\ExtendedShippingRates\Model\Config\Source\Zone
 */
class AttributeSet implements \Magento\Framework\Option\ArrayInterface
{
    const EXTENDED = 0;
    const SIMPLE = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EXTENDED, 'label' => __('Extended')],
            ['value' => self::SIMPLE, 'label' => __('Simple')],
        ];
    }
}
