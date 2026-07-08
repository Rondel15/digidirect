<?php

namespace Digidirect\StoreLocator\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CountriesConfig implements ArrayInterface
{
    private $options = [
        [
            'value' => 'default_country',
            'label' => 'Default Country'
        ],
        [
            'value' => 'available_countries',
            'label' => 'Available Countries'
        ]
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->options;
    }
}
