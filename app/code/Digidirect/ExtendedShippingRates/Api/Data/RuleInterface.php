<?php
namespace Digidirect\ExtendedShippingRates\Api\Data;

interface RuleInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RULE_ID = 'rule_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const FROM_DATE = 'from_date';
    const TO_DATE = 'to_date';
    const DAYS_OF_WEEK = 'days_of_week';
    const IS_ACTIVE = 'is_active';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED = 'actions_serialized';
    const STOP_RULES_PROCESSING = 'stop_rules_processing';
    const SHIPPING_METHODS = 'shipping_methods';
    const DISABLED_SHIPPING_METHODS = 'disabled_shipping_methods';
    const ENABLED_SHIPPING_METHODS = 'enabled_shipping_methods';
    const SORT_ORDER = 'sort_order';
    const ACTION_TYPE = 'action_type';
    const ACTION_TYPE_OPTION = 'action_type_option';
    const SIMPLE_ACTION = 'simple_action';
    const AMOUNT = 'amount';
    const TIME_FROM = 'time_from';
    const TIME_TO = 'time_to';
    const USE_TIME = 'use_time';
    const TIME_ENABLED = 'time_enabled';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const STORE_IDS = 'store_ids';
    const SHIFT_SHIPPING_AVAILABILITY_CHECK = 'shift_shipping_availability_check';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get from date
     *
     * @return string|null
     */
    public function getFromDate();

    /**
     * Get to date
     *
     * @return string|null
     */
    public function getToDate();

    /**
     * Get days of week
     *
     * @return string|null
     */
    public function getDaysOfWeek();

    /**
     * Get conditions serialized
     *
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Get actions serialized
     *
     * @return string|null
     */
    public function getActionsSerialized();

    /**
     * Get stop rules processing
     *
     * @return string|null
     */
    public function getStopRulesProcessing();

    /**
     * Get shipping methods
     *
     * @return string|null
     */
    public function getShippingMethods();

    /**
     * Get disabled shipping methods
     *
     * @return string|null
     */
    public function getDisabledShippingMethods();

    /**
     * Get enabled shipping methods
     *
     * @return string|null
     */
    public function getEnabledShippingMethods();

    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Get action type
     *
     * @return array|null
     */
    public function getActionType();

    /**
     * Get action type option
     *
     * @return int
     */
    public function getActionTypeOption();

    /**
     * Get simple action
     *
     * @return string|null
     */
    public function getSimpleAction();

    /**
     * Get amount
     *
     * @return array|null
     */
    public function getAmount();

    /**
     * Get time from
     *
     * @return string|null
     */
    public function getTimeFrom();

    /**
     * Get time to
     *
     * @return string|null
     */
    public function getTimeTo();

    /**
     * Get use time
     *
     * @return string|null
     */
    public function getUseTime();

    /**
     * Get time enabled
     *
     * @return string|null
     */
    public function getTimeEnabled();

    /**
     * Get customer group ids
     *
     * @return array|null
     */
    public function getCustomerGroupIds();

    /**
     * Get store ids
     *
     * @return array|null
     */
    public function getStoreIds();

    /**
     * Get Shift Shipping Availability Check
     *
     * @return int|null
     */
    public function getShiftShippingAvailabilityCheck();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setId($id);

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setName($name);

    /**
     * Set description
     *
     * @param string $description
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setDescription($description);

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setFromDate($fromDate);

    /**
     * Set to date
     *
     * @param string $toDate
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setToDate($toDate);

    /**
     * Set days of week
     *
     * @param string $daysOfWeek
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setDaysOfWeek($daysOfWeek);

    /**
     * Set is active
     *
     * @param string $isActive
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setIsActive($isActive);

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Set actions serialized
     *
     * @param string $actionsSerialized
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setActionsSerialized($actionsSerialized);

    /**
     * Set stop rules processing
     *
     * @param string $stopRulesProcessing
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setStopRulesProcessing($stopRulesProcessing);

    /**
     * Set shipping methods
     *
     * @param string $shippingMethods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setShippingMethods($shippingMethods);

    /**
     * Set disabled shipping methods
     *
     * @param string $disabledShippingMethods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setDisabledShippingMethods($disabledShippingMethods);

    /**
     * Set enabled shipping methods
     *
     * @param string $enabledShippingMethods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setEnabledShippingMethods($enabledShippingMethods);

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Set action type
     *
     * @param array $actionType
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setActionType($actionType);

    /**
     * Set action type option
     *
     * @param int $option
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setActionTypeOption($option);

    /**
     * Set simple action
     *
     * @param string $simpleAction
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setSimpleAction($simpleAction);

    /**
     * Set amount
     *
     * @param array $amount
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setAmount($amount);

    /**
     * Set time from
     *
     * @param string $timeFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setTimeFrom($timeFrom);

    /**
     * Set time to
     *
     * @param string $timeTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setTimeTo($timeTo);

    /**
     * Set use time
     *
     * @param string $useTime
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setUseTime($useTime);

    /**
     * Set time enabled
     *
     * @param string $timeEnabled
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setTimeEnabled($timeEnabled);

    /**
     * Set customer group ids
     *
     * @param array $customerGroupIds
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setStoreIds($storeIds);

    /**
     * Set Shift Shipping Availability Check
     *
     * @param int|null $shift
     *
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setShiftShippingAvailabilityCheck($shift);
}
