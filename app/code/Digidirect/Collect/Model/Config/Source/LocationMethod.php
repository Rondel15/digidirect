<?php

namespace Digidirect\Collect\Model\Config\Source;

use Digidirect\Collect\Helper\Data as CollectHelper;

/**
 * Class LocationMethod
 *
 * @package Digidirect\Collect\Model\Config\Source
 */
class LocationMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => CollectHelper::GEO_LOCATION_METHOD_GOOGLE_API, 'label' => __('Google API')],
            ['value' => CollectHelper::GEO_LOCATION_METHOD_POST_CODE, 'label' => __('AUPost post code file')],
        ];
    }
}
