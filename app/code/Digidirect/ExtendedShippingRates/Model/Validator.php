<?php

namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\Data\PostProcessingInterface;
use Digidirect\ExtendedShippingRates\Model\Config\Source\Rule\ActionTypeOptions;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Extended Shipping Rates Validator Model
 * @method mixed getStoreId()
 * @method Validator setStoreId($id)
 * @method mixed getCustomerGroupId()
 * @method Validator setCustomerGroupId($id)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validator extends \Magento\Framework\Model\AbstractModel
{
    const CONDITION_NEED_HIDE = 'need_hide';

    /**
     * Rule source collection
     *
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\Collection
     */
    protected $rules;

    /**
     * Post Processing Rule source collection
     *
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\Collection
     */
    protected $postProcessingRules;

    /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\CollectionFactory */
    protected $collectionFactory;

    /** @var \Digidirect\ExtendedShippingRates\Model\Utility */
    protected $validatorUtility;

    /** @var \Magento\Checkout\Model\Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /** @var \Magento\SalesRule\Model\Rule\Condition\Product */
    protected $productCondition;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var array
     */
    protected $appliedShippingRuleIds = [];

    /**
     * @var array
     */
    protected $condAppliedShippingRuleIds = [];

    /**
     * @var array
     */
    protected $disabledShippingMethods = [];

    /**
     * @var DataObject[]
     */
    protected $actionMethodFields = [];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     * @param Utility $utility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $productCondition
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $actionMethodFields
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Digidirect\ExtendedShippingRates\Model\Utility $utility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\SalesRule\Model\Rule\Condition\Product $productCondition,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $actionMethodFields = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->validatorUtility = $utility;
        $this->productCondition = $productCondition;
        if ($context->getAppState()->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
        }

        $this->prepareActionMethodFields($actionMethodFields);

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param array $actionMethodFields
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareActionMethodFields(array $actionMethodFields)
    {
        foreach ($actionMethodFields as $actionMethodField) {
            if (empty($actionMethodField['action']) || empty($actionMethodField['fields'])) {
                throw new LocalizedException(__('Wrong action method fields parameter format'));
            }

            $actionMethodField['fields'] = (array)$actionMethodField['fields'];

            $this->actionMethodFields[$actionMethodField['action']] = new DataObject($actionMethodField);
        }
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        if (!$this->quote) {
            $this->setQuote($this->session->getQuote());
        }

        return $this->quote;
    }

    /**
     * Init validator
     * Init process load collection of rules for specific store and
     * customer group
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @return $this
     */
    public function init($storeId, $customerGroupId)
    {
        $this->setStoreId($storeId)->setCustomerGroupId($customerGroupId);

        $key = $storeId . '_' . $customerGroupId;
        if (!isset($this->rules[$key])) {
            $this->rules[$key] =
                $this->_getValidationRulesCollection($storeId, $customerGroupId)->getItems();
            $this->postProcessingRules[$key] =
                $this->_getValidationRulesCollection($storeId, $customerGroupId, true)->getItems();
        }

        return $this;
    }

    /**
     * @param Method $rate
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(Method $rate)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getQuote();
        /** @var array $quoteItems */
        $quoteItems = $quote->getAllItems();
        // Do not process request without items (unusual request)
        if (empty($quoteItems)) {
            return false;
        }
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $quote->getShippingAddress();
        /** @var string $currentMethod */
        $currentMethod = Rule::getMethodCode($rate);

        /* @var Rule $rule */
        foreach ([$this->_getRules(), $this->_getRules(true)] as $rules) {
            foreach ($rules as $rule) {
                if (!$this->canProcessMethod($rule, $currentMethod)) {
                    continue;
                }

                // If rule has been already applied - continue
                if (isset($this->appliedShippingRuleIds[$currentMethod][$rule->getId()]) ||
                    $this->checkAddressAppliedRule($address, $rule, $currentMethod)
                ) {
                    if ($rule->getStopRulesProcessing()) {
                        break;
                    }
                    continue;
                }

                // Validate rule conditions
                if (!$this->validatorUtility->canProcessRule($rule, $address, $currentMethod)) {
                    if ($rule->getActionTypeOption() == ActionTypeOptions::ACTION_TYPE_ENABLE_OPTION
                        && in_array(Rule::ACTION_DISABLE_SM, $rule->getActionType())
                        && $rule->getDisabledShippingMethods()
                        && is_array($rule->getDisabledShippingMethods())
                        && in_array($currentMethod, $rule->getDisabledShippingMethods())
                    ) {
                        $this->condAppliedShippingRuleIds[$currentMethod][$rule->getId()][self::CONDITION_NEED_HIDE]
                            = $rule;
                    } else {
                        continue;
                    }
                }

                $this->appliedShippingRuleIds[$currentMethod][$rule->getId()] = $rule;
                $this->updateAddressAppliedShippingRuleIds($address);

                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }

        $validationResult = !empty($this->appliedShippingRuleIds[$currentMethod]);

        return $validationResult;
    }

    /**
     * @param \Digidirect\ExtendedShippingRates\Model\Rule $rule
     * @param string $currentMethod
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canProcessMethod(Rule $rule, $currentMethod)
    {
        if (!$this->actionMethodFields) {
            return true;
        }

        foreach ($rule->getActionType() as $actionType) {
            if (isset($this->actionMethodFields[$actionType])) {
                foreach ($this->actionMethodFields[$actionType]->getFields() as $field) {
                    if (($ruleMethods = $rule->getData($field))
                        && ($ruleMethods = array_flip($ruleMethods))
                        && isset($ruleMethods[$currentMethod])
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get available stored rules for $rate
     *
     * @param Method $rate
     * @return array
     */
    public function getAvailableRulesForRate(Method $rate)
    {
        /** @var string $currentMethod */
        $currentMethod = Rule::getMethodCode($rate);

        if (!empty($this->appliedShippingRuleIds[$currentMethod])) {
            return $this->appliedShippingRuleIds[$currentMethod];
        }

        return [];
    }

    /**
     * Get available stored rules for $rate
     *
     * @param Method $rate
     * @return array
     */
    public function getConditionallyAvailableRulesForRate(Method $rate)
    {
        /** @var string $currentMethod */
        $currentMethod = Rule::getMethodCode($rate);

        if (!empty($this->condAppliedShippingRuleIds[$currentMethod])) {
            return $this->condAppliedShippingRuleIds[$currentMethod];
        }

        return [];
    }

    /**
     * Update address applied rule ids with new rule id
     *
     * @param Address $address
     * @return Address
     */
    protected function updateAddressAppliedShippingRuleIds(
        Address $address
    ) {
        $addressRuleIds = $address->getAppliedShippingRulesIds();
        if (!$addressRuleIds) {
            $addressRuleIds = [];
        }

        $resultIds = array_merge($addressRuleIds, $this->appliedShippingRuleIds);
        $address->setAppliedShippingRulesIds($resultIds);

        return $address;
    }

    /**
     * If rhe rule already has been applied to the address return true
     * else return false
     *
     * @param Address $address
     * @param Rule $rule
     * @param string $method
     * @return bool
     */
    protected function checkAddressAppliedRule(
        Address $address,
        Rule $rule,
        $method
    ) {
        $appliedRules = $address->getAppliedShippingRulesIds();

        if (!is_array($appliedRules)) {
            return false;
        }

        if (empty($appliedRules[$rule->getId()])) {
            return false;
        }

        if (in_array($method, $appliedRules[$rule->getId()])) {
            return true;
        }

        return false;
    }

    /**
     * Get rules collection for current object state
     *
     * @param bool $postProcessing
     * @return \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\Collection
     */
    protected function _getRules($postProcessing = false)
    {
        $key = $this->getStoreId() . '_' . $this->getCustomerGroupId();
        if ($postProcessing) {
            return $this->postProcessingRules[$key];
        }

        return $this->rules[$key];
    }

    /**
     * Check is item valid for the corresponding rule
     *
     * @param Rule $rule
     * @param QuoteItem $item
     * @return bool
     */
    public function isValidItem(Rule $rule, QuoteItem $item)
    {
        /** @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine $actions */
        $actions = $rule->getActions();
        if (!$actions->validate($item)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $storeId
     * @param int $customerGroupId
     * @param bool $postProcessing
     * @return RuleCollection
     */
    protected function _getValidationRulesCollection($storeId, $customerGroupId, $postProcessing = false)
    {
        /** @var RuleCollection $collection */
        $collection = $this->collectionFactory->create()
            ->setValidationFilter($storeId, $customerGroupId)
            ->addFieldToFilter(Rule::IS_ACTIVE, 1);

        if ($postProcessing) {
            $collection->addFieldToFilter(PostProcessingInterface::POST_PROCESSING, ['eq' => 1]);
        } else {
            $collection->addFieldToFilter(PostProcessingInterface::POST_PROCESSING, [['neq' => 1], ['null' => 1]]);
        }

        return $collection;
    }
}
