<?php

namespace Digidirect\CheckoutFields\Model\Condition;

use Digidirect\Utilities\Helper\Attribute as AttributeHelper;

/**
 * Class OrderFieldValue
 *
 * @package Digidirect\CheckoutFields\Model
 */
class ProductAttribute implements ConditionInterface
{
    /**
     * @var AttributeHelper
     */
    protected $attributeHelper;

    /**
     * @var array
     */
    protected $validateFields;

    /**
     * Attribute constructor.
     * @param AttributeHelper $attributeHelper
     * @param array $validateFields
     */
    public function __construct(
        AttributeHelper $attributeHelper,
        array $validateFields = []
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->validateFields = $validateFields;
    }

    /**
     * @param string $code
     * @param array $options
     * @return bool
     */
    public function isValid($code, $options = [])
    {
        $isValid = true;
        if (isset($this->validateFields[$code])) {
            $isValid = false;
            foreach ($this->validateFields[$code] as $attrCode => $value) {
                if ($this->attributeHelper->hasCartProductAttributeValue($attrCode, $value)) {
                    $isValid = true;
                    break;
                }
            }
        }
        return $isValid;
    }
}
