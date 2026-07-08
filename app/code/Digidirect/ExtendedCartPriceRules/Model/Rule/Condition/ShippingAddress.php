<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Condition;

use Magento\Quote\Model\Quote;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class ShippingAddress
 */
class ShippingAddress extends AbstractCondition
{
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'digidirect_shipping_address' => __('Shipping Address'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = [
                'string' => ['==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'],
                'numeric' => ['==', '!=', '>=', '>', '<=', '<', '()', '!()'],
                'date' => ['==', '>=', '<='],
                'select' => ['==', '!='],
                'boolean' => ['==', '!='],
                'multiselect' => ['{}', '!{}', '()', '!()'],
                'grid' => ['()', '!()'],
                'contains' => ['{}', '!{}']
            ];
            $this->_arrayInputTypes = ['multiselect', 'grid'];
        }
        return $this->_defaultOperatorInputByType;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        return 'contains';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $quote = $model;
        if (!$quote instanceof Quote) {
            $quote = $model->getQuote();
        }

        if (!$shippingAdress = $quote->getShippingAddress()) {
            return false;
        }

        $model->setData('digidirect_shipping_address', trim($shippingAdress->getStreetFull()));

        return parent::validate($model);
    }
}
