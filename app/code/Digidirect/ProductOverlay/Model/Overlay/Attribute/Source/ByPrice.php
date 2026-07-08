<?php

namespace Digidirect\ProductOverlay\Model\Overlay\Attribute\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ByPrice
 * @package Digidirect\ProductOverlay\Model\Overlay\Attribute\Source
 */
class ByPrice implements ArrayInterface
{
    const BASE_PRICE = 0;
    const SPECIAL_PRICE = 1;
    const FINAL_PRICE = 2;
    const FINAL_PRICE_INCL_TAX = 3;
    const STARTING_FROM_PRICE = 4;
    const STARTING_TO_PRICE = 5;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BASE_PRICE, 'label' => __('Base Price')],
            ['value' => self::SPECIAL_PRICE, 'label' => __('Special Price')],
            ['value' => self::FINAL_PRICE, 'label' => __('Final Price')],
            ['value' => self::FINAL_PRICE_INCL_TAX, 'label' => __('Final Price Incl Tax')],
            ['value' => self::STARTING_FROM_PRICE, 'label' => __('Starting from Price')],
            ['value' => self::STARTING_TO_PRICE, 'label' => __('Starting to Price')],
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
            self::BASE_PRICE           => __('Base Price'),
            self::SPECIAL_PRICE        => __('Special Price'),
            self::FINAL_PRICE          => __('Final Price'),
            self::FINAL_PRICE_INCL_TAX => __('Final Price Incl Tax'),
            self::STARTING_FROM_PRICE  => __('Starting from Price'),
            self::STARTING_TO_PRICE    => __('Starting to Price'),
        ];
    }
}
