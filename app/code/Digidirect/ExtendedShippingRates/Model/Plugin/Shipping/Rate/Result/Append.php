<?php
namespace Digidirect\ExtendedShippingRates\Model\Plugin\Shipping\Rate\Result;

use Digidirect\ExtendedShippingRates\Model\Rule\Condition\DiscountCode;
use Digidirect\ExtendedShippingRates\Model\RuleAppliersAggregator;
use Digidirect\ExtendedShippingRates\Model\ValidatorsAggregator;
use Magento\Framework\Registry;

/**
 * Class Append
 *
 * @package Digidirect\ExtendedShippingRates\Model\Plugin\Shipping\Rate\Result
 */
class Append
{
    /**
     * @var ValidatorsAggregator
     */
    protected $validatorsAggregator;

    /**
     * @var RuleAppliersAggregator
     */
    protected $ruleAppliersAggregator;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Append constructor.
     *
     * @param ValidatorsAggregator $validatorsAggregator
     * @param RuleAppliersAggregator $ruleAppliersAggregator
     * @param Registry $registry
     */
    public function __construct(
        ValidatorsAggregator $validatorsAggregator,
        RuleAppliersAggregator $ruleAppliersAggregator,
        Registry $registry
    ) {
        $this->validatorsAggregator = $validatorsAggregator;
        $this->ruleAppliersAggregator = $ruleAppliersAggregator;
        $this->registry = $registry;
    }

    /**
     * Validate shipping methods before append.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult|\Magento\Shipping\Model\Rate\Result $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see \Digidirect\ExtendedShippingRates\Observer\Sales\Quote\Address\CollectTotalsAfter
     * by checking the value of this mark in the rate object.
     * NOTE: If you have some problems with the rules and the shipping methods, start debugging from here.
     */
    public function beforeAppend($subject, $result)
    {
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            return [$result];
        }

//        $quote = $this->validatorsAggregator->getCurrentProcessedQuote();
//        $validator = $this->validatorsAggregator->getValidatorByCurrentProcessedQuote();
//        $rulesApplier = $this->ruleAppliersAggregator->getRulesApplierByCurrentProcessedQuote();
//
//        $storeId = $quote->getStore()->getStoreId();
//        $customerGroup = $quote->getCustomerGroupId();
//
//        $validator->init($storeId, $customerGroup);
//        if ($validator->validate($result)) {
//            $rules = $validator->getAvailableRulesForRate($result);
//
//            /* @var \Digidirect\ExtendedShippingRates\Model\Rule $rule */
//            foreach ($rules as $rule) {
//                $ruleData = $rule->getConditions()->asArray();
//                if (isset($ruleData['conditions'])) {
//                    $conditions = $ruleData['conditions'];
//                    foreach ($conditions as $condition) {
//                        if (isset($condition['attribute']) &&
//                            $condition['attribute'] == DiscountCode::ATTRIBUTE_CODE &&
//                            !$this->registry->registry(DiscountCode::ATTRIBUTE_CODE)
//                        ) {
//                            $this->registry->register(DiscountCode::ATTRIBUTE_CODE, true);
//                        }
//                    }
//                }
//            }
//            $conditionalRules = $validator->getConditionallyAvailableRulesForRate($result);
//            $result = $rulesApplier->applyRules($result, $rules, $conditionalRules);
//        }

        return [$result];
    }
}
