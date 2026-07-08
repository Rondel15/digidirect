<?php
namespace Digidirect\ExtendedShippingRatesLocalization\Plugin\Digidirect\ExtendedShippingRates\Model\Utility;

use Digidirect\ExtendedShippingRates\Model\Utility as Subject;
use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\Localization\Helper\Data as LocalizationHelper;

class TimezonePlugin
{
    /**
     * @var LocalizationHelper
     */
    protected $localizationHelper;

    /**
     * UtilityPlugin constructor.
     * @param LocalizationHelper $localizationHelper
     */
    public function __construct(
        LocalizationHelper $localizationHelper
    ) {
        $this->localizationHelper = $localizationHelper;
    }

    /**
     * @param Subject $utility
     * @param \Closure $proceed
     * @param Rule|null $rule
     * @return \Zend_Date
     */
    public function aroundGetCurrentStoreDateTime(
        Subject $utility,
        \Closure $proceed,
        Rule $rule = null
    ) {
        if (null === $rule || !$rule->getUseStateTimezone() || !$rule->getZoneCountry()) {
            return $proceed($rule);
        }

        $timezone = $this->localizationHelper->getTimezone(
            $rule->getZoneCountry(),
            (int)$rule->getZoneStateId() ?: $rule->getZoneState()
        );

        if ($timezone) {
            return $this->getCurrentDate($timezone);
        }

        return $proceed($rule);
    }

    /**
     * @param string $timezone
     * @return \Zend_Date
     */
    protected function getCurrentDate($timezone)
    {
        $date = new \Zend_Date();
        $date->setTimezone($timezone);
        return $date;
    }
}
