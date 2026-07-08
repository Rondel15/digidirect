<?php

namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition;

use Magento\Quote\Model\Quote;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class DiscountCode
 */
class DiscountCode extends AbstractCondition
{
    const ATTRIBUTE_CODE = 'digidirect_discount_code';

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::ATTRIBUTE_CODE => __('Discount code'),
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
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        return 'string';
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
     * Validate Discount Code Rule Condition
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

        if (!$couponeCode = $quote->getCouponCode()) {
            return false;
        }

        $model->setData(self::ATTRIBUTE_CODE, $couponeCode);

        return parent::validate($model);
    }
}
