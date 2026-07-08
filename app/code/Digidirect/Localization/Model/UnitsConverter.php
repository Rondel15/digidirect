<?php
namespace Digidirect\Localization\Model;

use Digidirect\Localization\Api\Data\LengthUnitInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class UnitsConverter
 * @package Digidirect\Localization\Model
 */
class UnitsConverter
{
    const LENGTH_UNIT_MILE = 'mi';

    /**
     * @var array
     */
    protected $units;

    /**
     * UnitsConverter constructor.
     * @param array $units
     */
    public function __construct(
        array $units = []
    ) {
        $this->units = $units;
    }

    /**
     * @param float $miles
     * @param string $lengthUnit
     * @param int $precision
     * @return float
     * @throws LocalizedException
     */
    public function convertMilesToUnit($miles, $lengthUnit, $precision)
    {
        if ($lengthUnit === self::LENGTH_UNIT_MILE) {
            return $miles;
        }
        foreach ($this->units as $unit => $data) {
            $model = $data['class'] ?? null;
            if (($unit === $lengthUnit) && ($model instanceof LengthUnitInterface)) {
                return $model->convertMilesToUnit($miles, $precision);
            }
        }
        throw new LocalizedException(__('Can\'t find a Model to convert [mi] to [%1]', $lengthUnit));
    }

    /**
     * @return array
     */
    public function getUnits()
    {
        return $this->units;
    }
}
