<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product as ProductCondition;

class Subselect extends \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Combine
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
        $this->setType('Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Subselect')->setValue(null);
    }

    /**
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     *
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml =
            '<attribute>' .
                $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
                $this->getOperator() .
            '</operator>' .
            parent::asXml($containerKey, $itemKey);

        return $xml;
    }

    /**
     * Load array
     *
     * @param array $array
     * @param string $key
     *
     * @return $this
     */
    public function loadArray($array, $key = 'conditions')
    {
        $this->setAttribute($array['attribute']);
        $this->setOperator($array['operator']);

        parent::loadArray($array, $key);

        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'qty' => __('total quantity'),
                'base_row_total' => __('total amount')
            ]
        );

        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $operatorOptions = [
            '==' => __('is'),
            '!=' => __('is not'),
            '>' => __('greater than'),
            '<' => __('less than'),
            '>=' => __('equals or greater than'),
            '<=' => __('equals or less than'),
            '()' => __('is one of'),
            '!()' => __('is not one of'),
        ];

        $this->setOperatorOption($operatorOptions);

        return $this;
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
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $typeElementHtml = $this->getTypeElement()->getHtml();
        $label = "If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:";
        $html = $typeElementHtml . __(
            $label,
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Validate
     *
     * @param AbstractModel $abstractModel
     * @return bool
     */
    public function validate(AbstractModel $abstractModel)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attribute = $this->getAttribute();
        $total = 0;
        foreach ($abstractModel->getQuote()->getAllVisibleItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attribute);
            }
        }

        return $this->validateAttribute($total);
    }
}
