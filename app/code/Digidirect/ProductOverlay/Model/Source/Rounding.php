<?php

namespace Digidirect\ProductOverlay\Model\Source;

class Rounding implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'floor',
                'label' => __('The next lowest integer value')
            ],
            [
                'value' => 'round',
                'label' => __('By rules of mathematical rounding')
            ],
            [
                'value' => 'ceil',
                'label' => __('The next highest integer value')
            ],
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
            'floor' => __('The next lowest integer value'),
            'round' => __('By rules of mathematical rounding'),
            'ceil'  => __('The next highest integer value')
        ];
    }
}
