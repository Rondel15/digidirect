<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\SalesRule\Api\Data\ConditionInterface;
use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Model\Rule\Condition\Combine;

/**
 * Class AbstractRestrictAddToCartPlugin
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model
 */
abstract class AbstractRestrictAddToCart
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var Combine
     */
    protected $condition;

    /**
     * @var array
     */
    protected $conditionLabels = [];

    /**
     * QuoteRepository constructor.
     *
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @param MessageManagerInterface $messageManager
     * @param Combine $condition
     */
    public function __construct(
        ExtendedCartPriceRule $extendedCartPriceRule,
        MessageManagerInterface $messageManager,
        Combine $condition
    ) {
        $this->extendedCartPriceRule = $extendedCartPriceRule;
        $this->messageManager = $messageManager;
        $this->condition = $condition;
    }

    /**
     * @param array $restrictRules
     * @throws LocalizedException
     * @return void
     */
    protected function addWarningMessages(array $restrictRules = [])
    {
        $warnings = $this->getAllWarningMessages($restrictRules);
        foreach ($warnings as $warning) {
            $this->addWarningMessage($warning);
        }
    }

    /**
     * @param array $restrictRules
     * @return array
     * @throws LocalizedException
     */
    protected function getAllWarningMessages(array $restrictRules = [])
    {
        if (empty($restrictRules)) {
            return [];
        }

        $rules = $this->extendedCartPriceRule->getRulesById(array_keys($restrictRules));
        if ($rules->getTotalCount() < 0) {
            throw new LocalizedException(__('Invalid Cart Price Rules'));
        }
        $rules = $rules->getItems();

        $warnings = [];
        foreach ($rules as $rule) {
            $warnings[] = $this->getRuleAddToCartRestrictionMessage($rule, $restrictRules[$rule->getRuleId()]);
        }
        return $warnings;
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function addWarningMessage($msg)
    {
        $this->messageManager->addWarningMessage($msg);
    }

    /**
     * @param Rule $rule
     * @param string $actionMsg
     * @return string
     */
    protected function getRuleAddToCartRestrictionMessage(Rule $rule, $actionMsg)
    {
        $conditions = $this->getConditionTextParts($rule->getCondition()->getConditions());
        $conditions = implode(' | ', $conditions);

        return "{$actionMsg} {$conditions}";
    }

    /**
     * @param array $conditions
     * @return array
     */
    protected function getConditionTextParts($conditions)
    {
        $conditionText = [];
        foreach ($conditions as $condition) {
            /** @var ConditionInterface $condition */
            if ($combinedConditions = $condition->getConditions()) {
                $conditionText = array_merge($conditionText, $this->getConditionTextParts($combinedConditions));
            } else {
                $value = $condition->getValue();
                $operator = $this->getLabelForConditionOperator($condition->getOperator());
                $attributeName = $condition->getAttributeName();
                $attribute = $this->getLabelForConditionAttribute($condition, $attributeName);

                $conditionText[] = "{$attribute} {$operator} {$value}";
            }
        }
        return $conditionText;
    }

    /**
     * @param string $operator
     * @return string
     */
    public function getLabelForConditionOperator($operator)
    {
        $operators = $this->condition->getDefaultOperatorOptions();
        if (empty($operators[$operator])) {
            return $operator;
        }
        $phrase = $operators[$operator];
        return ($phrase instanceof Phrase) ? $phrase->getText() : $operator;
    }

    /**
     * @param ConditionInterface $condition
     * @param string $attributeName
     * @return string
     */
    protected function getLabelForConditionAttribute(ConditionInterface $condition, $attributeName)
    {
        $labelKey = $condition->getConditionType() . '|' . $attributeName;
        $labels = $this->getConditionLabels();
        return empty($labels[$labelKey]) ? $attributeName : $labels[$labelKey];
    }

    /**
     * Example: ['Magento\SalesRule\Model\Rule\Condition\Address|base_subtotal' => 'Subtotal', ... ]
     * @return array
     */
    protected function getConditionLabels()
    {
        if (empty($this->conditionLabels)) {
            $options = $this->condition->getNewChildSelectOptions();
            $this->conditionLabels = $this->getLabelsForConditionTypes($options);
        }
        return $this->conditionLabels;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getLabelsForConditionTypes($options)
    {
        $labels = [];
        foreach ($options as $option) {
            if (empty($option['value']) || empty($option['label'])) {
                continue;
            }
            if (is_array($option['value'])) {
                $labels = array_merge($labels, $this->getLabelsForConditionTypes($option['value']));
            } else {
                $labels[$option['value']] = $this->getTextFromLabel($option['label']);
            }
        }
        return $labels;
    }

    /**
     * @param  Phrase|string $label
     * @return string
     */
    protected function getTextFromLabel($label)
    {
        if ($label instanceof Phrase) {
            return $label->getText();
        }
        return $label;
    }
}
