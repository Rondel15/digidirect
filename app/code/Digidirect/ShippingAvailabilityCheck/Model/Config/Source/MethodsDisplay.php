<?php

namespace Digidirect\ShippingAvailabilityCheck\Model\Config\Source;

/**
 * Class MethodsDisplay
 * @package Digidirect\ShippingAvailabilityCheck\Model\Config\Source
 */
class MethodsDisplay implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get "Shipping Methods To Display" options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Only Available')],
            ['value' => 1, 'label' => __('All Methods')]
        ];
    }
}
