<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Rule\Metadata;

use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as Subject;

/**
 * Class ValueProvider
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Rule\Metadata
 */
class ValueProvider
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @var array
     */
    protected $actionsAdditionalFields = [];

    /**
     * @var array
     */
    protected $actionsHideFields = [];

    /***
     * @var array
     */
    protected $_metaDataValuesConfig;

    /**
     * ValueProvider constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param array $actions
     * @param array $actionsAdditionalFields
     * @param array $actionsHideFields
     * @param array $metaDataValuesConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        $actions = [],
        $actionsAdditionalFields = [],
        $actionsHideFields = [],
        $metaDataValuesConfig = []
    ) {
        $this->_coreRegistry = $registry;
        $this->actions = $actions;
        $this->_metaDataValuesConfig = $metaDataValuesConfig;
        $this->actionsAdditionalFields = $actionsAdditionalFields;
        $this->actionsHideFields = $actionsHideFields;
    }

    /**
     * Do actions around getMetadataValues()
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetMetadataValues(
        Subject $subject,
        \Closure $proceed,
        \Magento\SalesRule\Model\Rule $rule
    ) {
        $result = $proceed($rule);
        $salesrule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);
        if (!$salesrule || empty($this->actions)) {
            return $result;
        }

        if (isset($result['actions']['children']['simple_action']['arguments']['data']['config']['options'])) {
            foreach ($this->actions as $action) {
                $result['actions']['children']['simple_action']['arguments']['data']['config']['options'][] = [
                    'label' => $action['label'],
                    'value' => $action['value'],
                ];
            }
        }

        $options = $result['actions']['children']['simple_action']['arguments']['data']['config']['options'] ?? [];

        foreach ($options as $option) {
            $value = $option['value'] ?? '';
            $enabledFields = [];
            $hideFields = [];

            foreach ($this->actions as $action) {
                if ($value == $action['value']) {
                    if (isset($action['show_fields']) && is_array($action['show_fields'])) {
                        $enabledFields = $action['show_fields'];
                    }
                    if (isset($action['hide_fields']) && is_array($action['hide_fields'])) {
                        $hideFields = $action['hide_fields'];
                    }
                    break;
                }
            }
            foreach ($this->actionsAdditionalFields as $fieldName) {
                $show = in_array($fieldName, $enabledFields);
                $this->addSwitcherConfig($value, $fieldName, $show, $result);
            }

            foreach ($this->actionsHideFields as $hideField) {
                $hide = in_array($hideField, $hideFields);
                $this->addSwitcherConfig($value, $hideField, !$hide, $result);
            }
        }

        foreach ($this->actionsAdditionalFields as $fieldName) {
            $value = $salesrule->getData($fieldName);
            if ($fieldName == 'action_message' && empty($value)) {
                $value = 'The rule was applied:';
            }

            $result['actions']['children'][$fieldName]['arguments']['data']['config']['default'] = $value ?? '';
        }

        return $result;
    }

    /**
     * Add switcher config
     *
     * @param string $clickAction
     * @param string $fieldName
     * @param bool $show
     * @param array $result
     * @return void
     */
    protected function addSwitcherConfig(
        $clickAction,
        $fieldName,
        $show,
        &$result = []
    ) {
        $callback = $show ? 'show' : 'hide';
        $action = $show ? 'enable' : 'disable';
        $i = 0;
        $target = "sales_rule_form.sales_rule_form.actions.{$fieldName}";
        $result['actions']['children']['simple_action']['arguments']['data']['config']['switcherConfig']['rules'][] =
            [
                'value' => $clickAction,
                'actions' => [
                    $i => [
                        'target' => $target,
                        'callback' => $callback,
                    ],
                    $i + 1 => [
                        'target' => $target,
                        'callback' => $action,
                    ],
                    $i + 2 => [
                        'target' => $target,
                        'callback' => 'reset',
                    ],
                ],
            ];
        $result['actions']['children']['simple_action']['arguments']['data']['config']['switcherConfig']
        ['enabled'] = true;
    }
}
