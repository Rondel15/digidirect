<?php
namespace Digidirect\Localization\Api\Data;

/**
 * Interface LengthUnitInterface
 * @package Digidirect\Localization\Api\Data
 */
interface LengthUnitInterface
{
    /**
     * @param float $miles
     * @param int $precision
     * @return float
     */
    public function convertMilesToUnit($miles, $precision);
}
