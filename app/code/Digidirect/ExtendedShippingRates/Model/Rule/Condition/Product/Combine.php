<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product;

use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as OriginalCombine;
use Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product as ProductCondition;

class Combine extends OriginalCombine
{
    /**
     * @param Context $context
     * @param ProductCondition $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductCondition $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_ruleConditionProd
            ->loadAttributeOptions()
            ->getAttributeOption();

        $pAttributes = [];
        $iAttributes = [];

        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = [
                    'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product|' . $code,
                    'label' => $label,
                ];
            } else {
                $pAttributes[] = [
                    'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product|' . $code,
                    'label' => $label,
                ];
            }
        }

        $conditions = [
            [
                'value' => '',
                'label' => __('Please choose a condition to add.'),
            ],
            [
                'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Combine',
                'label' => __('Conditions Combination'),
            ],
            [
                'value' => $iAttributes,
                'label' => __('Cart Item Attribute'),
            ],
            [
                'value' => $pAttributes,
                'label' => __('Product Attribute'),
            ],
        ];

        return $conditions;
    }
}
