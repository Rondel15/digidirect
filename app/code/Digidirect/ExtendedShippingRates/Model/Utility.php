<?php

namespace Digidirect\ExtendedShippingRates\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address;

class Utility
{

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var TimezoneInterface */
    protected $timezone;

    /** @var DateTime */
    protected $datetime;

    /**
     * @var bool|null
     */
    protected $canApplyAltTitleActionOffset;

    /**
     * @var bool|null
     */
    protected $canApplyAltCodeActionOffset;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param DateTime $datetime
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        DateTime $datetime,
        TimezoneInterface $timezone
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->timezone = $timezone;
        $this->datetime = $datetime;
    }

    /**
     * @return bool|null
     */
    public function canApplyActionOffset()
    {
        return $this->canApplyAltTitleActionOffset;
    }

    /**
     * @return bool|null
     */
    public function canApplyAltCodeActionOffset()
    {
        return $this->canApplyAltCodeActionOffset;
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $rule
     * @param Address $address
     * @param string $currentMethod
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canProcessRule(Rule $rule, Address $address, $currentMethod)
    {
        // Validate address
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }

        // Validate store date
        if (!$this->validateStoreDate($rule)) {
            return false;
        }

        // Validate time
        if (!$rule->getShiftShippingAvailabilityCheck() && !$this->validateTime($rule)) {
            return false;
        }

        $rule->afterLoad();

        // quote does not meet rule's conditions
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($address, false);

            return false;
        }

        // passed all validations, remember to be valid
        $rule->setIsValidForAddress($address, true);

        return true;
    }

    /**
     * Validate rule by time (in minutes from 0 to 1440)
     *
     * @param Rule $rule
     * @return bool
     */
    public function validateTime(Rule $rule)
    {
        // Do not validate the rule if it is not using the time restrictions
        if (!$rule->getUseTime()) {
            return true;
        }

        $isRuleEnabledInTimeRange = $rule->getTimeEnabled();
        $ruleTimeFrom = $rule->getTimeFrom();
        $ruleTimeTo = $rule->getTimeTo();

        /** @var \Zend_Date $date */
        $date = $this->getCurrentStoreDateTime($rule);
        $currentHours = (int)$date->toString('H');
        $currentMinutes = (int)$date->toString('m');
        $currentTimeInMinutes = $currentHours * 60 + $currentMinutes;

        if ($isRuleEnabledInTimeRange) {
            // Rule is enabled at this time range
            if ($currentTimeInMinutes >= $ruleTimeFrom && $currentTimeInMinutes <= $ruleTimeTo) {
                return true;
            }
        } else {
            // Rule is disabled at this time range
            if ($currentTimeInMinutes <= $ruleTimeFrom || $currentTimeInMinutes >= $ruleTimeTo) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate rule by current store date
     *
     * @param Rule $rule
     * @return bool
     * @throws \Zend_Date_Exception
     */
    public function validateStoreDate(Rule $rule)
    {
        $date = $this->getCurrentStoreDateTime($rule);
        if ($shift = $rule->getShiftShippingAvailabilityCheck()) {
            for ($i = 1; $i <= $shift; $i++) {
                $date = $date->addDay('1');
                if ($this->validateDate($rule, $date)) {
                    return true;
                }
            }

            return false;
        }

        return $this->validateDate($rule, $date);
    }

    /**
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $rule
     * @param \Zend_Date $date
     * @return bool
     */
    public function validateDate(Rule $rule, \Zend_Date $date)
    {
        return $this->validateDayOfWeek($rule, $date);
    }

    /**
     * Validate rule by day of the week in current store locale
     *
     * @param Rule $rule
     * @param \Zend_Date $date
     * @return bool
     */
    public function validateDayOfWeek(Rule $rule, \Zend_Date $date)
    {
        $daysOfWeek = $rule->getDaysOfWeek();

        // Available for all days (no one option was selected)
        if (!$daysOfWeek) {
            return true;
        }

        $ruleDays = explode(',', $daysOfWeek);

        // Available for all 7 days (all options was selected)
        if (count($ruleDays) === 7) {
            return true;
        }

        $dayOfTheWeek = date('w', $date->getTimestamp());
        if (in_array($dayOfTheWeek, $ruleDays)) {
            return true;
        }

        return false;
    }

    /**
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $rule
     * @return bool
     */
    public function validateAltTitleActionOffset(Rule $rule)
    {
        return $this->validateActionOffset($rule, $rule->getActionOffsetBegins(), $rule->getActionOffsetEnds());
    }

    /**
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $rule
     * @return bool
     */
    public function validateAltCodeActionOffset(Rule $rule)
    {
        return $this->validateActionOffset(
            $rule,
            $rule->getAltCodeActionOffsetBegins(),
            $rule->getAltCodeActionOffsetEnds(),
            Rule::ACTION_USE_ALT_CODE
        );
    }

    /**
     * Validate rule by action offset in current store locale
     *
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $rule
     * @param int $actionOffsetBegins
     * @param int $actionOffsetEnds
     * @param string $actionType
     * @return bool
     */
    public function validateActionOffset(
        Rule $rule,
        $actionOffsetBegins,
        $actionOffsetEnds,
        $actionType = Rule::ACTION_USE_ALT_TITLE
    ) {
        if (in_array($actionType, $rule->getActionType())
            && $actionOffsetEnds
        ) {
            $ruleDays = explode(',', $rule->getDaysOfWeek());
            $date = $this->getCurrentStoreDateTime($rule);
            $currentDayOfTheWeek = $date->toString('eee');

            $timeToRule = $rule->getTimeTo();
            $startTimeActionOffset = $timeToRule + $actionOffsetBegins;

            $timestamp = mktime(0, $startTimeActionOffset);
            if (time() >= $timestamp) {
                $startActionOffsetDate = (new \DateTime())->setTimestamp($timestamp);
                $endActionOffsetDate = $startActionOffsetDate->modify('+' . $actionOffsetEnds . 'minutes');

                $startDate = (new \DateTime())->setTimestamp($timestamp);
                $days = $endActionOffsetDate->diff($startDate)->days;

                for ($i = $currentDayOfTheWeek + 1; $i <= $currentDayOfTheWeek + $days; $i++) {
                    if (in_array($i, $ruleDays)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Merge two sets of ids
     *
     * @param array|string $a1
     * @param array|string $a2
     * @param bool $asString
     * @return array|string
     */
    public function mergeIds($a1, $a2, $asString = true)
    {
        if (!is_array($a1)) {
            $a1 = empty($a1) ? [] : explode(',', $a1);
        }
        if (!is_array($a2)) {
            $a2 = empty($a2) ? [] : explode(',', $a2);
        }
        $a = array_unique(array_merge($a1, $a2));
        if ($asString) {
            $a = implode(',', $a);
        }

        return $a;
    }

    /**
     * Get datetime in current store locale
     *
     * @param Rule $rule
     * @return \Zend_Date
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCurrentStoreDateTime(Rule $rule = null)
    {
        $storeDate = $this->timezone->scopeTimeStamp();
        $zendDate = new \Zend_Date($storeDate);

        return $zendDate;
    }
}
