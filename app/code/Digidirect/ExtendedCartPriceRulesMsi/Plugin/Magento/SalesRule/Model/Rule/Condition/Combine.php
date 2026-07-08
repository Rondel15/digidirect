<?php

namespace Digidirect\ExtendedCartPriceRulesMSI\Plugin\Magento\SalesRule\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Combine as ConditionCombine;

/**
 * Class Combine
 *
 * @package Digidirect\ExtendedCartPriceRulesMSI\Plugin\Magento\SalesRule\Model\Rule\Condition
 */
class Combine
{
    /**
     * @param ConditionCombine $conditionCombine
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetNewChildSelectOptions(ConditionCombine $conditionCombine, $result)
    {
        $result[] = [
            'label' => __('Product quantity in Source'),
            'value' => \Digidirect\ExtendedCartPriceRulesMSI\Model\Rule\Condition\ProductQuantityInSource::class,
        ];

        return $result;
    }
}
