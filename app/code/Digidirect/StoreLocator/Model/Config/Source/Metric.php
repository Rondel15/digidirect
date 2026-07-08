<?php

namespace Digidirect\StoreLocator\Model\Config\Source;

// @codingStandardsIgnoreFile
/**
 * Class Metric
 * @package Digidirect\StoreLocator\Model\Config\Source
 */
class Metric implements \Magento\Framework\Option\ArrayInterface
{
    const METRIC_KM = 1;
    const METRIC_MILES = 2;
    const LABEL_KM = 'Km';
    const LABEL_MILES = 'Miles';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::METRIC_KM, 'label' => __(self::LABEL_KM)],
            ['value' => self::METRIC_MILES, 'label' => __(self::LABEL_MILES)]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::METRIC_KM => __(self::LABEL_KM), self::METRIC_MILES => __(self::LABEL_MILES)];
    }

    /**
     * @param int $value
     * @return string
     */
    public function getMetricByValue($value)
    {
        $metric  = __(self::LABEL_KM);
        if ($value == self::METRIC_MILES) {
            $metric  = __(self::LABEL_MILES);
        }

        return $metric;
    }
}
