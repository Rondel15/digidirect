<?php
namespace Digidirect\ExtendedShippingRates\Model\Zone\Condition;

/**
 * Class Address
 *
 * @method Address setAttributeOption(array $array)
 * @method string getAttribute()
 * @method array getAttributeOption()
 */
class Address extends \Magento\SalesRule\Model\Rule\Condition\Address
{
    /**
     * Load attribute options
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Zone\Condition\Address
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
                return 'multiselect';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
                return 'multiselect';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray(true);
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray(true);
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        /** @var \Magento\Quote\Model\Quote\Address|\Magento\Framework\Model\AbstractModel $address */
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        return parent::validate($address);
    }

    /**
     * @return array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getValueName()
    {
        $value = $this->getValue();
        if ($value === null || '' === $value) {
            return '...';
        }

        $options = $this->getValueSelectOptions();
        $valueArr = [];
        if (!empty($options)) {
            foreach ($options as $option) {
                if (is_array($value) && is_array($option['value'])) {
                    foreach ($option['value'] as $subOption) {
                        if (in_array($subOption['value'], $value)) {
                            $valueArr[] = $subOption['label'];
                        }
                    }
                } elseif (is_array($value)) {
                    if (in_array($option['value'], $value)) {
                        $valueArr[] = $option['label'];
                    }
                } elseif (isset($option['value'])) {
                    if (is_array($option['value'])) {
                        foreach ($option['value'] as $optionValue) {
                            if ($optionValue['value'] == $value) {
                                return $optionValue['label'];
                            }
                        }
                    }
                    if ($option['value'] == $value) {
                        return $option['label'];
                    }
                }
            }
        }
        if (!empty($valueArr)) {
            $value = implode(', ', $valueArr);
        }
        return $value;
    }

    /**
     * Retrieve parsed value
     *
     * @return array|string|int|float
     */
    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            if (is_array($value) && isset($value[0]) && is_string($value[0])) {
                $this->setValueParsed($value);
                $this->setData('is_value_parsed', true);
                return $value;
            }
            if ($this->isArrayOperatorType() && $value) {
                $value = preg_split('#\s*[,;]\s*#', $value, null, PREG_SPLIT_NO_EMPTY);
            }
            $this->setValueParsed($value);
        }
        return $this->getData('value_parsed');
    }
}
