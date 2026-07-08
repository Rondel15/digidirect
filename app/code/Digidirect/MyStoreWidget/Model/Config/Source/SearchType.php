<?php

namespace Digidirect\MyStoreWidget\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SearchType implements ArrayInterface
{
    const AUTOCOMPLETE_TYPE = 0;
    const TEXT_TYPE = 1;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::AUTOCOMPLETE_TYPE,
                'label' => 'Autocomplete',
            ],
            [
                'value' => self::TEXT_TYPE,
                'label' => 'Text Input',
            ]
        ];
    }
}
