<?php
namespace Digidirect\Localization\Model\Convert;

use Digidirect\Localization\Api\Data\LengthUnitInterface;

/**
 * Class Kilometer
 * @package Digidirect\Localization\Model\Convert
 */
class Kilometer implements LengthUnitInterface
{
    const KILOMETERS_IN_MILE = 1.609344;

    /**
     * @param float $miles
     * @param int $precision
     * @return float
     */
    public function convertMilesToUnit($miles, $precision)
    {
        return round($miles * self::KILOMETERS_IN_MILE, $precision);
    }
}
