<?php
namespace Digidirect\ExtendedShippingRates\Model\Config\Source;

class WeightType implements \Magento\Framework\Option\ArrayInterface
{
    const EMPTY_CODE = 'empty';
    const FIXED_CODE = 'fixed';
    const PERCENTAGE_CODE = 'percentage';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EMPTY_CODE, 'label' => __('Select Value')],
            ['value' => self::FIXED_CODE, 'label' => __('Fixed Value')],
            ['value' => self::PERCENTAGE_CODE, 'label' => __('Percentage')]
        ];
    }
}
