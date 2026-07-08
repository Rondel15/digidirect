<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\SalesRule\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Combine as ConditionCombine;

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
            'label' => __('Previous Customer Orders'),
            'value' => \Digidirect\ExtendedCartPriceRules\Model\Rule\Condition\OrdersCount::class,
        ];
        $result[] = [
            'label' => __('Previous Customer Order Status'),
            'value' => \Digidirect\ExtendedCartPriceRules\Model\Rule\Condition\LastOrderStatus::class,
        ];
        $result[] = [
            'label' => __('Shipping Address'),
            'value' => \Digidirect\ExtendedCartPriceRules\Model\Rule\Condition\ShippingAddress::class,
        ];
        $result[] = [
            'label' => __('Product was bought in'),
            'value' => \Digidirect\ExtendedCartPriceRules\Model\Rule\Condition\ProductWasBoughtIn::class,
        ];
        $result[] = [
            'label' => __('Product sum volume'),
            'value' => \Digidirect\ExtendedCartPriceRules\Model\Rule\Condition\ProductSumVolume::class,
        ];

        return $result;
    }
}
