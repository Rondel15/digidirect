<?php

namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\Data\PostProcessingInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeCodeInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleZoneInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Rule\Model\AbstractModel;

/**
 * Class Rule
 *
 * @package Digidirect\ExtendedShippingRates\Model
 */
class Rule extends AbstractModel implements
    RuleInterface,
    RuleZoneInterface,
    PostProcessingInterface,
    RuleAlternativeTitleInterface,
    RuleAlternativeCodeInterface
{
    /**
     * Tables
     */
    const RULE_TABLE_NAME = 'digidirect_extendedshippingrates';
    const STORE_TABLE_NAME = 'digidirect_extendedshippingrates_store';
    const CUSTOMER_GROUP_TABLE_NAME = 'digidirect_extendedshippingrates_customer_group';

    /**
     * Rule possible actions
     */
    const ACTION_OVERWRITE_COST = 'overwrite';

    /**
     * Disable or enable action type
     */
    const ACTION_DISABLE_SM = 'disable';

    /**
     * Use alternative title for shipping methods action type
     */
    const ACTION_USE_ALT_TITLE = 'use_alt_title';

    /**
     * Use alternative code for shipping methods action type
     */
    const ACTION_USE_ALT_CODE = 'use_alt_code';

    // Matrix
    const ACTION_CALCULATION_FIXED = 'fixed';
    const ACTION_CALCULATION_PERCENT = 'percent';

    const ACTION_METHOD_OVERWRITE = 'overwrite';
    const ACTION_METHOD_SURCHARGE = 'surcharge';
    const ACTION_METHOD_DISCOUNT = 'discount';

    const ACTION_TYPE_AMOUNT = 'amount';
    const ACTION_TYPE_PER_QTY_OF_ITEM = 'product'; // per Qty of Item
    const ACTION_TYPE_PER_ITEM = 'item';
    const ACTION_TYPE_PER_WEIGHT_UNIT = 'weight';

    const FREE_SHIPPING_CODE = 'freeshipping_freeshipping';

    const ENABLED = 1;
    const DISABLED = 0;

    const CURRENT_PROMO_QUOTE_RULE = 'current_promo_quote_rule';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_extendedshippingrates_rule';

    /**
     * Parameter name in event
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $validatedAddresses = [];

    /** @var \Digidirect\ExtendedShippingRates\Model\Rule\Condition\CombineFactory */
    protected $condCombineFactory;

    /** @var \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\CombineFactory */
    protected $condProdCombineF;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /**
     * @return array
     */
    public static function getActionCalculations()
    {
        return [
            self::ACTION_CALCULATION_FIXED,
            self::ACTION_CALCULATION_PERCENT
        ];
    }

    /**
     * @return array
     */
    public static function getActionMethods()
    {
        return [
            self::ACTION_METHOD_OVERWRITE,
            self::ACTION_METHOD_SURCHARGE,
            self::ACTION_METHOD_DISCOUNT
        ];
    }

    /**
     * @return array
     */
    public static function getActionTypes()
    {
        return [
            self::ACTION_TYPE_AMOUNT,
            self::ACTION_TYPE_PER_QTY_OF_ITEM,
            self::ACTION_TYPE_PER_ITEM,
            self::ACTION_TYPE_PER_WEIGHT_UNIT,
        ];
    }

    /**
     * Get calculation matrix as array
     *
     * @return array
     */
    public static function getCalculationMatrix()
    {
        $calculations = self::getActionCalculations();
        $methods = self::getActionMethods();
        $types = self::getActionTypes();

        $matrix = [];

        foreach ($calculations as $calculation) {
            foreach ($methods as $method) {
                foreach ($types as $type) {
                    $key = implode('_', [$calculation, $method, $type]);
                    $matrix[$key] = $key;
                }
            }
        }

        return $matrix;
    }

    /**
     * @param Method $rate
     * @return string
     */
    public static function getMethodCode(Method $rate)
    {
        /** @var string $methodCode */
        $methodCode = $rate->getCarrier() . '_' . $rate->getMethod();

        return $methodCode;
    }

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Digidirect\ExtendedShippingRates\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Digidirect\ExtendedShippingRates\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get from date
     *
     * @return string
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * Get to date
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * Get days of week
     *
     * @return string
     */
    public function getDaysOfWeek()
    {
        return $this->getData(self::DAYS_OF_WEEK);
    }

    /**
     * Get conditions serialized
     *
     * @return string
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Get actions serialized
     *
     * @return string
     */
    public function getActionsSerialized()
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    /**
     * Get stop rules processing
     *
     * @return string
     */
    public function getStopRulesProcessing()
    {
        return $this->getData(self::STOP_RULES_PROCESSING);
    }

    /**
     * Get shipping methods
     *
     * @return string
     */
    public function getShippingMethods()
    {
        return $this->getData(self::SHIPPING_METHODS);
    }

    /**
     * Get disabled shipping methods
     *
     * @return string
     */
    public function getDisabledShippingMethods()
    {
        return $this->getData(self::DISABLED_SHIPPING_METHODS);
    }

    /**
     * Get enabled shipping methods
     *
     * @return string
     */
    public function getEnabledShippingMethods()
    {
        return $this->getData(self::ENABLED_SHIPPING_METHODS);
    }

    /**
     * Get sort order
     *
     * @return string
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Get action type
     *
     * @return array
     */
    public function getActionType()
    {
        return $this->getData(self::ACTION_TYPE);
    }

    /**
     * Get action type option
     *
     * @return int
     */
    public function getActionTypeOption()
    {
        return $this->getData(self::ACTION_TYPE_OPTION);
    }

    /**
     * Get simple action
     *
     * @return string
     */
    public function getSimpleAction()
    {
        return $this->getData(self::SIMPLE_ACTION);
    }

    /**
     * Get amount
     *
     * @return array
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Get time from
     *
     * @return string
     */
    public function getTimeFrom()
    {
        return $this->getData(self::TIME_FROM);
    }

    /**
     * Get time to
     *
     * @return string
     */
    public function getTimeTo()
    {
        return $this->getData(self::TIME_TO);
    }

    /**
     * Get use time
     *
     * @return string
     */
    public function getUseTime()
    {
        return $this->getData(self::USE_TIME);
    }

    /**
     * Get time enabled
     *
     * @return string
     */
    public function getTimeEnabled()
    {
        return $this->getData(self::TIME_ENABLED);
    }

    /**
     * Get used alternative title shipping methods
     *
     * @return string|null
     */
    public function getUsedAltTitleShippingMethods()
    {
        return $this->getData(self::USED_ALT_TITLE_SHIPPING_METHODS);
    }

    /**
     * Get action offset begins
     *
     * @return int|null
     */
    public function getActionOffsetBegins()
    {
        return $this->getData(self::ACTION_OFFSET_BEGINS);
    }

    /**
     * Get action offset ends
     *
     * @return int|null
     */
    public function getActionOffsetEnds()
    {
        return $this->getData(self::ACTION_OFFSET_ENDS);
    }

    /**
     * Get used alternative code shipping methods
     *
     * @return string|null
     */
    public function getUsedAltCodeShippingMethods()
    {
        return $this->getData(self::USED_ALT_CODE_SHIPPING_METHODS);
    }

    /**
     * Get action offset begins
     *
     * @return int|null
     */
    public function getAltCodeActionOffsetBegins()
    {
        return $this->getData(self::ALT_CODE_ACTION_OFFSET_BEGINS);
    }

    /**
     * Get action offset ends
     *
     * @return int|null
     */
    public function getAltCodeActionOffsetEnds()
    {
        return $this->getData(self::ALT_CODE_ACTION_OFFSET_ENDS);
    }

    /**
     * Get sales rule customer group Ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData(self::CUSTOMER_GROUP_IDS, (array)$customerGroupIds);
        }

        return $this->_getData(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * Get rule associated store Ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $storeIds = $this->_getResource()->getStoreIds($this->getId());
            $this->setData(self::STORE_IDS, (array)$storeIds);
        }

        return $this->getData(self::STORE_IDS);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setId($id)
    {
        return $this->setData(self::RULE_ID, $id);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set from date
     *
     * @param string $fromDate
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * Set to date
     *
     * @param string $toDate
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * Set days of week
     *
     * @param string $daysOfWeek
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setDaysOfWeek($daysOfWeek)
    {
        return $this->setData(self::DAYS_OF_WEEK, $daysOfWeek);
    }

    /**
     * Set is active
     *
     * @param string $isActive
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Set actions serialized
     *
     * @param string $actionsSerialized
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setActionsSerialized($actionsSerialized)
    {
        return $this->setData(self::ACTIONS_SERIALIZED, $actionsSerialized);
    }

    /**
     * Set stop rules processing
     *
     * @param string $stopRulesProcessing
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setStopRulesProcessing($stopRulesProcessing)
    {
        return $this->setData(self::STOP_RULES_PROCESSING, $stopRulesProcessing);
    }

    /**
     * Set shipping methods
     *
     * @param string $shippingMethods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setShippingMethods($shippingMethods)
    {
        return $this->setData(self::SHIPPING_METHODS, $shippingMethods);
    }

    /**
     * Set disabled shipping methods
     *
     * @param string $disabledShippingMethods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setDisabledShippingMethods($disabledShippingMethods)
    {
        return $this->setData(self::DISABLED_SHIPPING_METHODS, $disabledShippingMethods);
    }

    /**
     * Set enabled shipping methods
     *
     * @param string $enabledShippingMethods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setEnabledShippingMethods($enabledShippingMethods)
    {
        return $this->setData(self::ENABLED_SHIPPING_METHODS, $enabledShippingMethods);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set action type
     *
     * @param array $actionType
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setActionType($actionType)
    {
        return $this->setData(self::ACTION_TYPE, $actionType);
    }

    /**
     * Set action type option
     *
     * @param int $option
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setActionTypeOption($option)
    {
        return $this->setData(self::ACTION_TYPE_OPTION, $option);
    }

    /**
     * Set simple action
     *
     * @param string $simpleAction
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setSimpleAction($simpleAction)
    {
        return $this->setData(self::SIMPLE_ACTION, $simpleAction);
    }

    /**
     * Set amount
     *
     * @param array $amount
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Set time from
     *
     * @param string $timeFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setTimeFrom($timeFrom)
    {
        return $this->setData(self::TIME_FROM, $timeFrom);
    }

    /**
     * Set time to
     *
     * @param string $timeTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setTimeTo($timeTo)
    {
        return $this->setData(self::TIME_TO, $timeTo);
    }

    /**
     * Set use time
     *
     * @param string $useTime
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setUseTime($useTime)
    {
        return $this->setData(self::USE_TIME, $useTime);
    }

    /**
     * Set time enabled
     *
     * @param string $timeEnabled
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setTimeEnabled($timeEnabled)
    {
        return $this->setData(self::TIME_ENABLED, $timeEnabled);
    }

    /**
     * Set customer group ids
     *
     * @param array $customerGroupIds
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setCustomerGroupIds($customerGroupIds)
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customerGroupIds);
    }

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleInterface
     */
    public function setStoreIds($storeIds)
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        $result = $this->condProdCombineF->create();

        return $result;
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;

        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);

        return $this->validatedAddresses[$addressId] ?? false;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }

        return $address;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * Get all shipping methods affected by rule (from the change price & disable sections both)
     *
     * @return array
     */
    public function getAffectedShippingMethods()
    {
        $shippingMethods = !empty($this->getShippingMethods()) ? $this->getShippingMethods() : [];
        $disabledShippingMethods = !empty($this->getDisabledShippingMethods()) ?
            $this->getDisabledShippingMethods() :
            [];
        $affectShippingMethods = array_merge($shippingMethods, $disabledShippingMethods);

        return $affectShippingMethods;
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        if (!$this->getData('skip_resource_after_load')) {
            parent::afterLoad();

            return $this;
        }
        $this->_afterLoad();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getZoneCountry()
    {
        return $this->getData(self::ZONE_COUNTRY);
    }

    /**
     * {@inheritdoc}
     */
    public function getZoneState()
    {
        return $this->getData(self::ZONE_STATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getZoneStateId()
    {
        return $this->getData(self::ZONE_STATE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setZoneCountry($countryId)
    {
        return $this->getData(self::ZONE_COUNTRY, $countryId);
    }

    /**
     * {@inheritdoc}
     */
    public function setZoneState($state)
    {
        return $this->getData(self::ZONE_STATE, $state);
    }

    /**
     * {@inheritdoc}
     */
    public function setZoneStateId($stateId)
    {
        return $this->getData(self::ZONE_STATE_ID, $stateId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostProcessing()
    {
        return $this->getData(self::POST_PROCESSING);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostProcessing($isPostProcessingRule)
    {
        return $this->setData(self::POST_PROCESSING, $isPostProcessingRule);
    }

    /**
     * Set used alternative title shipping methods
     *
     * @param string $methods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setUsedAltTitleShippingMethods($methods)
    {
        return $this->setData(self::USED_ALT_TITLE_SHIPPING_METHODS, $methods);
    }

    /**
     * Set action offset begins
     *
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setActionOffsetBegins($offset)
    {
        return $this->setData(self::ACTION_OFFSET_BEGINS, $offset);
    }

    /**
     * Set action offset ends
     *
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setActionOffsetEnds($offset)
    {
        return $this->setData(self::ACTION_OFFSET_ENDS, $offset);
    }

    /**
     * Set used alternative code shipping methods
     *
     * @param string $methods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setUsedAltCodeShippingMethods($methods)
    {
        return $this->setData(self::USED_ALT_CODE_SHIPPING_METHODS, $methods);
    }

    /**
     * Set action offset begins
     *
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setAltCodeActionOffsetBegins($offset)
    {
        return $this->setData(self::ALT_CODE_ACTION_OFFSET_BEGINS, $offset);
    }

    /**
     * Set action offset ends
     *
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setAltCodeActionOffsetEnds($offset)
    {
        return $this->setData(self::ALT_CODE_ACTION_OFFSET_ENDS, $offset);
    }

    /**
     * Get Shift Shipping Availability Check
     *
     * @return int|null
     */
    public function getShiftShippingAvailabilityCheck()
    {
        return ($shift = $this->getData(self::SHIFT_SHIPPING_AVAILABILITY_CHECK)) ? intval($shift) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setShiftShippingAvailabilityCheck($shift)
    {
        return $this->setData(self::SHIFT_SHIPPING_AVAILABILITY_CHECK, $shift);
    }
}
