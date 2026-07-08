<?php

namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Model\Config\Source\Rule\ActionTypeOptions;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateResult\Method;

/**
 * Class RulesApplier
 *
 * @package Digidirect\ExtendedShippingRates\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RulesApplier
{

    const SORT_MULTIPLIER = 1000;

    /** @var Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /** @var Utility */
    protected $validatorUtility;

    /** @var Rule\Action\RateFactory */
    protected $rateFactory;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var array
     */
    protected $shippingMethods = [];

    /**
     * @var array
     */
    protected $enabledShippingMethods = [];

    /**
     * @var mixed
     */
    protected $commonActionTypeOption;

    /**
     * @param Rule\Action\RateFactory $rateFactory
     * @param Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Framework\App\State $state
     * @param Utility $utility
     */
    public function __construct(
        Rule\Action\RateFactory $rateFactory,
        Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Framework\App\State $state,
        Utility $utility
    ) {
        $this->rateFactory = $rateFactory;
        $this->validatorUtility = $utility;
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
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
     * Apply rules to current order item
     *
     * @param Method $rate
     * @param array|ResourceModel\Rule\Collection $rules
     * @param array $conditionalRules
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules(Method $rate, array $rules, array $conditionalRules = [])
    {
        /** @var string $currentRate */
        $currentRate = Rule::getMethodCode($rate);

        /** @var \Digidirect\ExtendedShippingRates\Model\Rule $rule */
        foreach ($rules as $rule) {
            // Do not apply one rule more then one time to the one rate
            $ruleId = $rule->getId();
            $rateAppliedRules = is_array($rate->getAppliedRules()) ? $rate->getAppliedRules() : [];
            if (in_array($ruleId, $rateAppliedRules)) {
                continue;
            }

            $conditionalRule = $conditionalRules[$ruleId] ?? [];

            // Process rules actions
            $actionTypes = $rule->getActionType();
            $actionTypes = is_string($actionTypes) ? json_decode($actionTypes) : $actionTypes;
            foreach ($actionTypes as $actionType) {
                switch ($actionType) {
                    case Rule::ACTION_OVERWRITE_COST:
                        if (!in_array($currentRate, $rule->getShippingMethods())) {
                            break;
                        }
                        $this->overwriteCost($rule, $rate);
                        break;

                    case Rule::ACTION_DISABLE_SM:
                        if ($this->validateRulesActionTypeOption($rules, $actionType, $conditionalRules)
                            && is_array($rule->getDisabledShippingMethods())
                            && in_array($currentRate, $rule->getDisabledShippingMethods())) {
                            $this->disableOrEnableShippingMethod($rule, $currentRate, $rate, $conditionalRule);
                        }
                        break;

                    case Rule::ACTION_USE_ALT_TITLE:
                        if (is_array($rule->getUsedAltTitleShippingMethods())
                            && in_array($currentRate, $rule->getUsedAltTitleShippingMethods())) {
                            $this->useAltTitleShippingMethod($rate);
                        }
                        break;

                    case Rule::ACTION_USE_ALT_CODE:
                        if (is_array($rule->getUsedAltCodeShippingMethods())
                            && in_array($currentRate, $rule->getUsedAltCodeShippingMethods())) {
                            $this->useAltCodeShippingMethod($rate);
                        }
                        break;
                }
            }

            // Update applied rules in the shipping method
            $appliedRules = array_merge($rateAppliedRules, [$ruleId]);
            $rate->setAppliedRules($appliedRules);
        }
        $this->updateShippingMethodsAvailability($rate);

        return $rate;
    }

    /**
     * Rules with different action type options can't be applied
     *
     * @param array $rules
     * @param mixed $actionType
     * @param array $conditionalRules
     * @return bool
     */
    public function validateRulesActionTypeOption($rules, $actionType, array $conditionalRules = [])
    {
        $result = [];
        if ($actionType == Rule::ACTION_DISABLE_SM) {
            foreach ($rules as $rule) {
                $result[] = isset($conditionalRules[$rule->getId()][Validator::CONDITION_NEED_HIDE])
                    ? ActionTypeOptions::ACTION_TYPE_DISABLE_OPTION
                    : $rule->getActionTypeOption();
            }

            if (count(array_unique($result)) == 1) {
                $this->commonActionTypeOption = array_shift($result);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $rule
     * @param Method $currentRate
     * @param Method $rate
     * @param array $conditionalRule
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function disableOrEnableShippingMethod($rule, $currentRate, $rate, array $conditionalRule)
    {
        $actionTypeOption = $rule->getActionTypeOption();
        if (isset($conditionalRule[Validator::CONDITION_NEED_HIDE])) {
            $actionTypeOption = ActionTypeOptions::ACTION_TYPE_DISABLE_OPTION;
        }

        switch ($actionTypeOption) {
            case ActionTypeOptions::ACTION_TYPE_ENABLE_OPTION:
                $this->enableShippingMethod($rate);
                break;
            case ActionTypeOptions::ACTION_TYPE_DISABLE_OPTION:
                $this->disableShippingMethod($rate);
                break;
        }

        return $this;
    }

    /**
     * Change rate title
     *
     * @param Method $rate
     * @return $this
     */
    public function useAltTitleShippingMethod($rate)
    {
        if ($rate->getAlternativeTitle()) {
            $rate->setMethodTitle($rate->getAlternativeTitle());
        }

        return $this;
    }

    /**
     * Change rate code
     *
     * @param Method $rate
     * @return $this
     */
    public function useAltCodeShippingMethod($rate)
    {
        if ($rate->getAlternativeCode()) {
            $rate->setMethod($rate->getAlternativeCode());
        }

        return $this;
    }

    /**
     * Overwrite shipping method cost & price
     *
     * @param Rule $rule
     * @param Method $rate
     * @return Method
     */
    protected function overwriteCost(Rule $rule, Method $rate)
    {
        // Check what action is used in rule
        $actionsCommaSeparated = $rule->getSimpleAction();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getQuote();

        if (!$actionsCommaSeparated) {
            return $rate;
        }

        $actions = explode(',', $actionsCommaSeparated);
        $sortedActions = $this->sortActions($actions, $rule);

        foreach ($sortedActions as $action) {
            // Do not change price for the free shipping method
            $code = Rule::getMethodCode($rate);
            if ($code === Rule::FREE_SHIPPING_CODE) {
                return $this;
            }

            // Create calculator for actual action & Calculate result
            $calculator = $this->rateFactory->create($action);
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $result */
            $rate = $calculator->calculate($rule, $rate, $quote);
        }

        return $rate;
    }

    /**
     * Sort the rule actions
     *
     * @param array $actions
     * @param Rule $rule
     * @return array
     */
    protected function sortActions(array $actions, Rule $rule)
    {
        $amounts = $rule->getAmount();
        $sortedActions = [];

        foreach ($actions as $action) {
            // Do not sort not existing actions
            if (empty($amounts[$action])) {
                continue;
            }

            // Get original sort order
            $sortOrder = (int)$amounts[$action]['sort'];

            /**
             * Update the sort order to prevent overwriting.
             * It's possible that exists more than one rule with the same sort order)
             */
            $updatedSort = $sortOrder * self::SORT_MULTIPLIER;
            while (isset($sortedActions[$updatedSort])) {
                $updatedSort++;
            }

            // Save the action with the new sort order (numeric array key)
            $sortedActions[$updatedSort] = $action;
        }

        ksort($sortedActions);

        return $sortedActions;
    }

    /**
     * Add current shipping method to array of disabled shipping methods
     *
     * @param Method $rate
     * @return $this
     */
    public function disableShippingMethod(Method $rate)
    {
        $code = Rule::getMethodCode($rate);

        $this->shippingMethods[$code] = Rule::DISABLED;

        return $this;
    }

    /**
     * Add current shipping method to array of enabled shipping methods
     *
     * @param Method $rate
     * @return $this
     */
    public function enableShippingMethod(Method $rate)
    {
        $code = Rule::getMethodCode($rate);
        $this->enabledShippingMethods[$code] = Rule::ENABLED;

        return $this;
    }

    /**
     * Save shipping methods availability in the checkout session
     *
     * @param Method $rate
     * @return $this
     */
    protected function updateShippingMethodsAvailability($rate)
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $this->getQuote()->getShippingAddress();
        $existingMethods = $address->getShippingRulesMethods() ?: [];

        if ($this->commonActionTypeOption == ActionTypeOptions::ACTION_TYPE_DISABLE_OPTION) {
            $result = array_merge($this->enabledShippingMethods, $this->shippingMethods);
        } else {
            $result = array_merge($this->shippingMethods, $this->enabledShippingMethods);
        }

        if (!empty($result)) {
            foreach ($result as $code => $availability) {
                if ($code == Rule::getMethodCode($rate) && $availability == 0) {
                    $rate->setIsDisabled(true);
                } elseif ($availability == 1) {
                    unset($result[$code]);
                }
            }
        }
        $allMethods = array_merge($existingMethods, $result);
        $address->setShippingRulesMethods($allMethods);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param int[] $appliedRuleIds
     * @return $this
     */
    public function setAppliedShippingRuleIds($address, array $appliedRuleIds)
    {
        $address->setAppliedShippingRuleIds(
            $this->validatorUtility->mergeIds(
                $address->getAppliedShippingRuleIds(),
                $appliedRuleIds
            )
        );

        return $this;
    }
}
