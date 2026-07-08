<?php
namespace Digidirect\Localization\Helper;

use Digidirect\Localization\Model\UnitsConverter;

/**
 * Class Units
 * @package Digidirect\Localization\Helper
 */
class Units
{
    /**
     * @var UnitsConverter
     */
    protected $unitsConverter;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Units constructor.
     * @param UnitsConverter $unitsConverter
     * @param Data $dataHelper
     */
    public function __construct(
        UnitsConverter $unitsConverter,
        Data $dataHelper
    ) {
        $this->unitsConverter = $unitsConverter;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param float $miles
     * @param int $precision
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convertMilesToStoreLengthUnit($miles, $precision = 2)
    {
        $unit = $this->dataHelper->getLengthUnit();
        return $this->unitsConverter->convertMilesToUnit($miles, $unit, $precision);
    }
}
