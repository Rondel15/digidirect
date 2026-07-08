<?php

namespace Digidirect\ExtendedShippingRates\Model\Zone\Processors;

use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;

/**
 * Class CountryIdDataProcessor
 * @package Digidirect\ExtendedShippingRates\Model\Zone\Processors
 */
class CountryIdDataProcessor extends AbstractDataProcessor
{
    /**
     * @param array $data
     * @return array
     */
    public function prepareData(&$data)
    {
        $config = $this->getDefaultConfig();
        if (!empty($data[ZoneInterface::COUNTRY_ID])) {
            $config['value'] = explode(',', $data[ZoneInterface::COUNTRY_ID]);
        }
        return $config;
    }
}
