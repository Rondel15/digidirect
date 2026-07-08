<?php

namespace Digidirect\ExtendedShippingRates\Model\Config\Source;

/**
 * Class RegionCollection
 * @package Digidirect\ExtendedShippingRates\Model\Config\Source
 */
class RegionCollection extends \Magento\Directory\Model\ResourceModel\Region\Collection
{
    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        if (count($options) > 0) {
            $options = array_replace(
                $options,
                [['title' => '', 'value' => '', 'label' => __('All')]]
            );
        }
        return $options;
    }
}
