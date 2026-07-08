<?php

namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Quote\Model\Quote;
use Magento\Framework\Model\AbstractModel;
use Digidirect\ExtendedShippingRates\Model\Config\Source\AvailableCustomConditions;

/**
 * Class UseCustomCondition
 * @package Digidirect\ExtendedShippingRates\Model\Rule\Condition
 */
class UseCustomCondition extends AbstractCondition
{
    const ATTRIBUTE_NAME = 'use_custom_condition';

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\Config\Source\AvailableCustomConditions
     */
    protected $availableCustomConditions;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param AvailableCustomConditions $availableCustomConditions
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        AvailableCustomConditions $availableCustomConditions,
        array $data = []
    ) {
        $this->availableCustomConditions = $availableCustomConditions;
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::ATTRIBUTE_NAME => __('Use custom condition'),
        ];

        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Get attribute element
     *
     * @return AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData('value_select_options', $this->availableCustomConditions->toOptionArray());
        }
        return $this->getData('value_select_options');
    }

    /**
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = [
                '==' => __('')
            ];
        }
        return $this->_defaultOperatorOptions;
    }

    /**
     * Validate Rule Condition
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if (!$model instanceof Quote) {
            $quote = $model->getQuote();
        } else {
            $quote = $model;
        }

        if ($quote instanceof Quote) {
            $model->setData(self::ATTRIBUTE_NAME, null);
            $selectedCustomCondition = $this->getValue();
            $availableCustomConditions = $this->availableCustomConditions->getCustomConditionsArray();
            if (!empty($availableCustomConditions[$selectedCustomCondition])) {
                $nestedCustomCondition = $availableCustomConditions[$selectedCustomCondition];
                if ($nestedCustomCondition->validate($quote)) {
                    $model->setData(self::ATTRIBUTE_NAME, $selectedCustomCondition);
                }
            }
        }
        return parent::validate($model);
    }
}
