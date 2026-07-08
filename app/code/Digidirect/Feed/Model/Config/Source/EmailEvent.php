<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class EmailEvent implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => '',
                'value' => '',
            ],
            [
                'label' => __('Successful Export'),
                'value' => 'export_success',
            ],
            [
                'label' => __('Unsuccessful Export'),
                'value' => 'export_fail',
            ],
            [
                'label' => __('Successful Delivery'),
                'value' => 'delivery_success',
            ],
            [
                'label' => __('Unsuccessful Delivery'),
                'value' => 'delivery_fail',
            ],
        ];
    }
}
