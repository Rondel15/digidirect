<?php

namespace Digidirect\ExtendedShippingRates\Model\Config\Source\Rule;

/**
 * Class ActionTypeOptions
 * @package Digidirect\ExtendedShippingRates\Model\Config\Source\Rule
 */
class ActionTypeOptions implements \Magento\Framework\Option\ArrayInterface
{
    const ACTION_TYPE_DISABLE_OPTION = 0;
    const ACTION_TYPE_ENABLE_OPTION = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => self::ACTION_TYPE_DISABLE_OPTION, 'label' => __('Hide')],
                ['value' => self::ACTION_TYPE_ENABLE_OPTION, 'label' => __('Show')]
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
            self::ACTION_TYPE_DISABLE_OPTION => __('Hide'),
            self::ACTION_TYPE_ENABLE_OPTION => __('Show')
        ];
    }
}
