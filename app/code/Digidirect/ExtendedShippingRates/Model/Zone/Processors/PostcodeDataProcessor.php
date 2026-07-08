<?php

namespace Digidirect\ExtendedShippingRates\Model\Zone\Processors;

use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;

/**
 * Class PostcodeDataProcessor
 * @package Digidirect\ExtendedShippingRates\Model\Zone\Processors
 */
class PostcodeDataProcessor extends AbstractDataProcessor
{
    const ATTRIBUTE = ZoneInterface::POSTCODE;
    const OPERATOR = '()';

    /**
     * @param array $data
     * @return array
     */
    public function prepareData(&$data)
    {
        $config = $this->getDefaultConfig();
        if (!empty($data[ZoneInterface::POSTCODE])) {
            $post = explode(',', $data[ZoneInterface::POSTCODE]);
            $range = $codes = [];
            foreach ($post as $key => $value) {
                $value = trim($value);
                $codes[$key] = $value;
                if (strpos($value, '-') !== false) {
                    list($min, $max) = explode('-', $value);
                    $range = array_merge($range, range($min, $max));
                    unset($codes[$key]);
                }
            }
            $codes = array_unique(array_merge($codes, $range));
            sort($codes);
            $value = implode(',', $codes);
            $config['value'] = $value;
            $data[ZoneInterface::POSTCODE] = $value;
        }

        return $config;
    }
}
