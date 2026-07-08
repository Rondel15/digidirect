<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Delimiter implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Comma'),
                'value' => ',',
            ],
            [
                'label' => __('Tab'),
                'value' => 'tab',
            ],
            [
                'label' => __('Colon'),
                'value' => ':',
            ],
            [
                'label' => __('Space'),
                'value' => ' ',
            ],
            [
                'label' => __('Vertical pipe'),
                'value' => '|',
            ],
            [
                'label' => __('Semi-colon'),
                'value' => ';',
            ],
        ];
    }
}
