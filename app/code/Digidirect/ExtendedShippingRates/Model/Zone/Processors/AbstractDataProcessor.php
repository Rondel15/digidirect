<?php

namespace Digidirect\ExtendedShippingRates\Model\Zone\Processors;

use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;

/**
 * Class AbstractDataProcessor
 * @package Digidirect\ExtendedShippingRates\Model\Zone\Processors
 */
abstract class AbstractDataProcessor
{
    const ATTRIBUTE = ZoneInterface::COUNTRY_ID;
    const OPERATOR = '{}';
    const TYPE = 'Digidirect\ExtendedShippingRates\Model\Zone\Condition\Address';

    /**
     * @param array $data
     * @return mixed
     */
    abstract public function prepareData(&$data);

    /**
     * @return array
     */
    public function getDefaultConfig()
    {
        $config = [];
        $config['attribute'] = static::ATTRIBUTE;
        $config['operator'] = static::OPERATOR;
        $config['type'] = static::TYPE;
        return $config;
    }
}
