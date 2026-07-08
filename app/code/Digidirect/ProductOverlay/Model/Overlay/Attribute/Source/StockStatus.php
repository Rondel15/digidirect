<?php

namespace Digidirect\ProductOverlay\Model\Overlay\Attribute\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class StockStatus
 * @package Digidirect\ProductOverlay\Model\Overlay\Attribute\Source
 */
class StockStatus implements ArrayInterface
{
    const DOES_NOT_MATTER = 0;
    const OUT_OF_STOCK = 1;
    const IN_STOCK = 2;
    const CUSTOM_STOCK = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DOES_NOT_MATTER, 'label' => __('Does not matter')],
            ['value' => self::OUT_OF_STOCK, 'label' => __('Out of Stock')],
            ['value' => self::IN_STOCK, 'label' => __('In Stock')],
            ['value' => self::CUSTOM_STOCK, 'label' => __('Custom Stock')]
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
            self::OUT_OF_STOCK    => __('Out of Stock'),
            self::IN_STOCK        => __('In Stock'),
            self::CUSTOM_STOCK    => __('Custom Stock')
        ];
    }
}
