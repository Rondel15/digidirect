<?php
namespace Digidirect\ExtendedShippingRates\Plugin\Magento\SalesRule\Model\Rule\Condition;

use Magento\SalesRule\Model\Rule\Condition\Combine as Subject;
use Digidirect\ExtendedShippingRates\Model\Rule\Condition\UseZoneFromState;
use Digidirect\ExtendedShippingRates\Model\Rule\Condition\UseCustomCondition;
use Digidirect\ExtendedShippingRates\Model\Rule\Condition\DiscountCode;

/**
 * Class Combine
 * @package Digidirect\ExtendedShippingRates\Plugin\Magento\SalesRule\Model\Rule\Condition
 */
class Combine
{
    /**
     * @param \Magento\SalesRule\Model\Rule\Condition\Combine $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetNewChildSelectOptions(Subject $subject, $result)
    {
        $result[] = [
            'label' => __('Use Zones Form State'),
            'value' => UseZoneFromState::class,
        ];

        $result[] = [
            'label' => __('Use custom condition <...>'),
            'value' => UseCustomCondition::class,
        ];

        $result[] = [
            'label' => __('Discount code'),
            'value' => DiscountCode::class,
        ];
        return $result;
    }
}
