<?php
namespace Digidirect\AbstractEntity\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class FulltextSearchPattern implements ArrayInterface
{
    const PATTERN_ASTERISK_ASTERISK = '**';
    const PATTERN_PLUS_ASTERISK = '+*';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PATTERN_ASTERISK_ASTERISK,
                'label' => __('*SearchTerm*'),
            ],
            [
                'value' => self::PATTERN_PLUS_ASTERISK,
                'label' => __('+SearchTerm*'),
            ],
        ];
    }
}
