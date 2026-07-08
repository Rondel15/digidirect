<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Popup\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\SalesRule\Model\Rule\Condition\Product\Found as FoundCondition;

class Found extends FoundCondition
{
    public function __construct(
        Context $context,
        ProductCondition $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(\Plumrocket\Newsletterpopup\Model\Popup\Condition\Found::class);
    }

    public function validate(AbstractModel $model)
    {
        return parent::validate($model->getQuote());
    }

    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        array_walk_recursive($conditions, function (&$value, $key) {
            $value = str_replace(
                \Magento\SalesRule\Model\Rule\Condition\Product::class,
                \Plumrocket\Newsletterpopup\Model\Popup\Condition\Product::class,
                $value
            );
        });

        return $conditions;
    }
}
