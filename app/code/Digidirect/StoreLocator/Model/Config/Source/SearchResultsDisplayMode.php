<?php
namespace Digidirect\StoreLocator\Model\Config\Source;

/**
 * Class SearchResultsDisplayMode
 * @package Digidirect\StoreLocator\Model\Config\Source
 */
class SearchResultsDisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_USING_ATTRIBUTES = 'DEFAULT';
    const MODE_USING_RADIUS = 'RADIUS';
    const LABEL_MODE_USING_ATTRIBUTES = 'Using Attributes';
    const LABEL_MODE_USING_RADIUS = 'Using Radius';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MODE_USING_ATTRIBUTES, 'label' => __(self::LABEL_MODE_USING_ATTRIBUTES)],
            ['value' => self::MODE_USING_RADIUS, 'label' => __(self::LABEL_MODE_USING_RADIUS)]
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
            self::MODE_USING_ATTRIBUTES => __(self::LABEL_MODE_USING_ATTRIBUTES),
            self::MODE_USING_RADIUS => __(self::LABEL_MODE_USING_RADIUS)
        ];
    }
}
