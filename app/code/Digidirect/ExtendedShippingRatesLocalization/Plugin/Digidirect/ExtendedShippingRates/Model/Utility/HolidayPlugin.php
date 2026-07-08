<?php

namespace Digidirect\ExtendedShippingRatesLocalization\Plugin\Digidirect\ExtendedShippingRates\Model\Utility;

use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\ExtendedShippingRates\Model\Utility as Subject;
use Digidirect\Localization\Helper\Data as LocalizationHelper;

class HolidayPlugin
{
    /**
     * @var LocalizationHelper
     */
    protected $localizationHelper;

    /**
     * @var array
     */
    protected $holidays;

    /**
     * UtilityPlugin constructor.
     *
     * @param LocalizationHelper $localizationHelper
     */
    public function __construct(
        LocalizationHelper $localizationHelper
    ) {
        $this->localizationHelper = $localizationHelper;
    }

    /**
     * @param Subject $utility
     * @param \Closure $closure
     * @param Rule $rule
     * @param \Zend_Date $date
     *
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundValidateDate(
        Subject $utility,
        \Closure $closure,
        Rule $rule,
        \Zend_Date $date
    ) {
        $result = $closure($rule, $date);

        if ($result && $rule->getUnavailableOnHolidays() && $rule->getZoneCountry()) {
            $holidays = $this->getHolidays(
                $rule->getZoneCountry(),
                (int)$rule->getZoneStateId() ?: $rule->getZoneState()
            );

            if (!empty($holidays) && $this->checkHoliday($date, $holidays)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $country
     * @param $state
     *
     * @return array
     */
    protected function getHolidays($country, $state): array
    {
        if (!isset($this->holidays[$country][$state])) {
            $holidays = $this->localizationHelper->getHolidays($country, $state) ?: [];
            $this->holidays[$country][$state] = array_combine($holidays, $holidays);
        }

        return $this->holidays[$country][$state];
    }

    /**
     * @param \Zend_Date $date
     * @param array $holidays
     *
     * @return bool
     */
    protected function checkHoliday(\Zend_Date $date, array $holidays): bool
    {
        return isset($holidays[$date->toString('d/M/Y')]);
    }
}
