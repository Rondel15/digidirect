<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Archive implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray($empty = false)
    {
        $result = [];

        if ($empty) {
            $result[] = [
                'label' => __('Disabled'),
                'value' => '',
            ];
        }

        $result[] = [
            'label' => __('ZIP (*.zip)'),
            'value' => 'zip',
        ];

        return $result;
    }
}
