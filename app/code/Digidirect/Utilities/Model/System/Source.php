<?php

namespace Digidirect\Utilities\Model\System;

/**
 * Class Source
 * @package Digidirect\Utilities\Model\System
 */
class Source implements \Magento\Framework\Option\ArrayInterface
{
    const TEXT_TYPE = 1;
    const BLOCK_TYPE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TEXT_TYPE, 'label' => __('Text')],
            ['value' => self::BLOCK_TYPE, 'label' => __('Block')]
        ];
    }
}
