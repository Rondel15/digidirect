<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\AdvancedSalesRule\Model\Rule\Condition;

use Magento\AdvancedSalesRule\Model\Rule\Condition\Address;

class AddressPlugin
{
    const IS_DEFINED_OPERATOR = 'is_defined';

    /**
     * @param Address $subject
     * @param bool $result
     * @param mixed ...$args
     * @return mixed
     */
    public function afterValidateAttribute(Address $subject, $result, ...$args)
    {
        $validatedValue = reset($args);
        $option = $subject->getOperatorForValidate();
        if (self::IS_DEFINED_OPERATOR === $option) {
            $value = $subject->getValueParsed();
            $result = empty($value) ? !empty($validatedValue) : $value === $validatedValue;
        }
        return $result;
    }

    /**
     * @param Address $subject
     * @param array $result
     * @return array
     */
    public function afterGetDefaultOperatorOptions(Address $subject, $result)
    {
        $result[self::IS_DEFINED_OPERATOR] = __('is defined');
        return $result;
    }

    /**
     * @param Address $subject
     * @param array $result
     * @return array
     */
    public function afterGetDefaultOperatorInputByType(Address $subject, $result)
    {
        $result['select'][] = self::IS_DEFINED_OPERATOR;
        return $result;
    }
}
