<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product as ProductCondition;

class Found extends \Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Combine
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
        $this->setType('Digidirect\ExtendedShippingRates\Model\Rule\Condition\Product\Found');
    }

    /**
     * Validate
     *
     * @param AbstractModel $abstractModel
     * @return bool
     */
    public function validate(AbstractModel $abstractModel)
    {
        $found = false;
        $true = (bool)$this->getValue();
        $isAll = $this->getAggregator() === 'all';

        foreach ($abstractModel->getAllItems() as $item) {
            $found = $isAll;
            $conditions = $this->getConditions();

            /** @var \Magento\Rule\Model\Condition\AbstractCondition $condition */
            foreach ($conditions as $condition) {
                $validated = $condition->validate($item);
                if ($isAll && !$validated || !$isAll && $validated) {
                    $found = $validated;
                    break;
                }
            }

            if ($found) {
                break;
            }
        }

        if ($found && $true) {
            return true;
        } elseif (!$found && !$true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $typeElementHtml = $this->getTypeElement()->getHtml();
        $valueElementHtml = $this->getValueElement()->getHtml();
        $aggregatorElementHtml = $this->getAggregatorElement()->getHtml();
        $label = "If an item is %1 in the cart with %2 of these conditions true:";

        $html = $typeElementHtml . __(
            $label,
            $valueElementHtml,
            $aggregatorElementHtml
        );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption(
            [
                1 => __('FOUND'),
                0 => __('NOT FOUND')
            ]
        );

        return $this;
    }
}
