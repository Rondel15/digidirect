<?php

namespace Digidirect\ProductOverlay\Model\Overlay\Attribute\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class IsNew
 * @package Digidirect\ProductOverlay\Model\Overlay\Attribute\Source
 */
class IsNew implements ArrayInterface
{
    const DOES_NOT_MATTER = 0;
    const NO = 1;
    const YES = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DOES_NOT_MATTER, 'label' => __('Does not matter')],
            ['value' => self::NO, 'label' => __('No')],
            ['value' => self::YES, 'label' => __('Yes')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::DOES_NOT_MATTER => __('Does not matter'),
            self::NO              => __('No'),
            self::YES             => __('Yes'),
        ];
    }
}
