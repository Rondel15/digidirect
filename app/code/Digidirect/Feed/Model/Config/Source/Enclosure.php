<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Enclosure implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('none'),
                'value' => '',
            ],
            [
                'label' => __('"'),
                'value' => '"',
            ],
            [
                'label' => __('\''),
                'value' => '\'',
            ],
        ];
    }
}
