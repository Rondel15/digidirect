<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Combine
{
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Address $conditionAddress
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Address $conditionAddress,
        array $data = []
    ) {
        parent::__construct($context, $eventManager, $conditionAddress, $data);
        $this->setType('Digidirect\ExtendedShippingRates\Model\Rule\Condition\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes = $this->_conditionAddress->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Address|' . $code,
                'label' => $label,
            ];
        }

        $conditions = [
            [
                'value' => '',
                'label' => __('Please choose a condition to add.'),
            ],
            [
                'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Found',
                'label' => __('Product attribute combination'),
            ],
            [
                'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Subselect',
                'label' => __('Products subselection'),
            ],
            [
                'value' => 'Digidirect\ExtendedShippingRates\Model\Rule\Condition\Combine',
                'label' => __('Conditions combination'),
            ],
            [
                'value' => $attributes,
                'label' => __('Cart Attribute'),
            ],
        ];

        $additional = new \Magento\Framework\DataObject();

        $this->_eventManager->dispatch(
            'shipping_rule_condition_combine',
            ['additional' => $additional]
        );

        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive(
                $conditions,
                $additionalConditions
            );
        }

        return $conditions;
    }
}
