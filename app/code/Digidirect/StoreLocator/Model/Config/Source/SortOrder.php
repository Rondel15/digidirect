<?php

namespace Digidirect\StoreLocator\Model\Config\Source;

// @codingStandardsIgnoreFile
/**
 * Class SortOrder
 * @package Digidirect\StoreLocator\Model\Config\Source
 */
class SortOrder implements \Magento\Framework\Option\ArrayInterface
{
    const SORT_DISTANCE = 'DISTANCE';

    const SORT_PRIORITY = 'PRIORITY';

    const LABEL_DISTANCE = 'Distance';

    const LABEL_PRIORITY = 'Priority';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SORT_DISTANCE, 'label' => __(self::LABEL_DISTANCE)],
            ['value' => self::SORT_PRIORITY, 'label' => __(self::LABEL_PRIORITY)],
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
            self::SORT_DISTANCE => __(self::LABEL_DISTANCE),
            self::SORT_PRIORITY =>__(self::LABEL_PRIORITY)
        ];
    }
}
