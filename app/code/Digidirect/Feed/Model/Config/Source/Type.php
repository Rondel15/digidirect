<?php

namespace Digidirect\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Type implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'CSV',
                'value' => 'csv',
            ],
            [
                'label' => 'TXT',
                'value' => 'txt',
            ],
            [
                'label' => 'XML',
                'value' => 'xml',
            ],
        ];
    }
}
