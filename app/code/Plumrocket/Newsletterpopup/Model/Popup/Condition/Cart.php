<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Popup\Condition;

use Magento\Rule\Model\Condition\AbstractCondition;

class Cart extends AbstractCondition
{
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $attributes = [
            'cart_base_subtotal' => __('Cart Subtotal'),
            'cart_total_qty' => __('Cart Total Items Quantity'),
            'cart_total_items' => __('Cart Total Items Rows'),
        ];
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();

            $this->_defaultOperatorInputByType['numeric'] = ['==', '!=', '>=', '>', '<=', '<'];
        }
        return $this->_defaultOperatorInputByType;
    }
}
