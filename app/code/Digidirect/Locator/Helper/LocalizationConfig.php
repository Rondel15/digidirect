<?php

namespace Digidirect\Locator\Helper;

use Digidirect\Localization\Helper\Data;
use Digidirect\Localization\Model\UnitsConverter;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class LocalizationConfig
 */
class LocalizationConfig extends AbstractHelper
{
    /**
     *
     */
    const KILOMETER_SHORT_NAME = 'km';

    /**
     * @var Data
     */
    protected $localizationHelper;

    /**
     * @var UnitsConverter
     */
    protected $unitsConverter;

    /**
     * LocalizationConfig constructor.
     * @param Context $context
     * @param Data $localizationHelper
     * @param UnitsConverter $unitsConverter
     */
    public function __construct(
        Context $context,
        Data $localizationHelper,
        UnitsConverter $unitsConverter
    ) {
        parent::__construct($context);
        $this->localizationHelper = $localizationHelper;
        $this->unitsConverter = $unitsConverter;
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getLengthUnit($scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->localizationHelper->getLengthUnit($scope);
    }

    /**
     * @param $lenght
     * @param int $precision
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function convertUnitToKilometres($lenght, $precision = 2)
    {
        $lenghtUnit = $this->getLengthUnit();
        if ($lenghtUnit === self::KILOMETER_SHORT_NAME) {
            return $lenght;
        }
        return $this->unitsConverter->convertMilesToUnit($lenght, self::KILOMETER_SHORT_NAME, $precision);
    }
}
