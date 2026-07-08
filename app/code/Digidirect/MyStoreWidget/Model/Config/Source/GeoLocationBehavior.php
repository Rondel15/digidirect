<?php
namespace Digidirect\MyStoreWidget\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class GeoLocationBehavior
 * @package Digidirect\MyStoreWidget\Model\Config\Source
 */
class GeoLocationBehavior implements ArrayInterface
{
    const KEEP = 'keep';
    const REFRESH = 'refresh';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::REFRESH, 'label' => __('Refresh on each page load')],
            ['value' => self::KEEP, 'label' => __('Keep on Client\'s side')]
        ];
    }
}
