<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Popup\Condition;

use Magento\Rule\Model\Condition\Combine as CombineCondition;
use Magento\Rule\Model\Condition\Context;

class Combine extends CombineCondition
{
    protected $_conditionGeneral;
    protected $_conditionCustomer;
    protected $_conditionCart;
    protected $_conditionProduct;

    public function __construct(
        Context $context,
        General $conditionGeneral,
        Customer $conditionCustomer,
        Cart $conditionCart,
        Product $conditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(\Plumrocket\Newsletterpopup\Model\Popup\Condition\Combine::class);

        $this->_conditionGeneral = $conditionGeneral;
        $this->_conditionCustomer = $conditionCustomer;
        $this->_conditionCart = $conditionCart;
        $this->_conditionProduct = $conditionProduct;
    }

    public function getNewChildSelectOptions()
    {
        $generalAttributes = $this->_conditionGeneral->loadAttributeOptions()->getAttributeOption();
        $general = [];
        foreach ($generalAttributes as $code => $label) {
            $general[] = [
                'value' => 'Plumrocket\Newsletterpopup\Model\Popup\Condition\General|' . $code,
                'label' => $label
            ];
        }

        $customerAttributes = $this->_conditionCustomer->loadAttributeOptions()->getAttributeOption();
        $customer = [];
        foreach ($customerAttributes as $code => $label) {
            $customer[] = [
                'value' => 'Plumrocket\Newsletterpopup\Model\Popup\Condition\Customer|' . $code,
                'label' => $label
            ];
        }

        $cartAttributes = $this->_conditionCart->loadAttributeOptions()->getAttributeOption();
        $cart = [];
        foreach ($cartAttributes as $code => $label) {
            $cart[] = ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Cart|'.$code, 'label'=>$label];
        }

        $productAttributes = $this->_conditionProduct->loadAttributeOptions()->getAttributeOption();
        $product = [];
        foreach ($productAttributes as $code => $label) {
            $product[] = ['value'=>'Plumrocket\Newsletterpopup\Model\Popup\Condition\Product|'.$code, 'label'=>$label];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, [
            ['value' => Combine::class, 'label' => __('Conditions combination')],
            ['value' => Found::class, 'label' => __('Product attribute combination in shopping cart')],
            ['value' => $general, 'label' => __('General')],
            ['value' => $customer, 'label' => __('Customer Attribute')],
            ['value' => $cart, 'label' => __('Cart Attribute')],
            ['value' => $product, 'label' => __('Current Product Page')],
        ]);

        return $conditions;
    }
}
