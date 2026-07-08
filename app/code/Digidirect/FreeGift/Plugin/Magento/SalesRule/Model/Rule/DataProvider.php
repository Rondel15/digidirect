<?php

namespace Digidirect\FreeGift\Plugin\Magento\SalesRule\Model\Rule;

use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Model\Rule\DataProvider as MagentoSalesRuleDataProvider;

/**
 * Class DataProvider
 *
 * @package Digidirect\FreeGift\Plugin\Magento\SalesRule\Model\Rule
 */
class DataProvider
{
    /**
     * @var array
     */
    protected $_fieldsToActions;

    /**
     * DataProvider constructor.
     *
     * @param array $fieldsToActions
     */
    public function __construct(array $fieldsToActions = [])
    {
        $this->_fieldsToActions = $fieldsToActions;
    }

    /**
     * After get meta add switcher config
     *
     * @param MagentoSalesRuleDataProvider $dataProvider
     * @param [] $result
     * @return []
     */
    public function afterGetMeta(MagentoSalesRuleDataProvider $dataProvider, $result)
    {
        if (empty($this->_fieldsToActions) || empty($result['actions']['children'][Rule::KEY_SIMPLE_ACTION])) {
            return $result;
        }

        $config = $result['actions']['children'][Rule::KEY_SIMPLE_ACTION]['arguments']['data']['config'];
        $options = $config['options'] ?? [];
        if (empty($options)) {
            return $result;
        }

        $additionalRulesSwitcherConfigData = [];
        foreach ($options as $option) {
            $actionCode = $option['value'] ?? null;
            if (!$actionCode) {
                continue;
            }

            $additionalRulesSwitcherConfigData[] = [
                'value' => $actionCode,
                'actions' => $this->_getActionSwitcherConfigActions($actionCode)
            ];
        }

        if ($additionalRulesSwitcherConfigData) {
            $switcherConfig = !empty($config['switcherConfig']) ? $config['switcherConfig'] : [];
            $switcherConfig = array_merge_recursive(
                $switcherConfig,
                ['rules' => $additionalRulesSwitcherConfigData]
            );
            $switcherConfig['enabled'] = true;
            $result['actions']['children'][Rule::KEY_SIMPLE_ACTION]['arguments']['data']['config']['switcherConfig'] =
                $switcherConfig;
        }

        return $result;
    }

    /**
     * @param string $actionCode
     * @return bool
     */
    protected function _isFreeGiftAction($actionCode)
    {
        switch ($actionCode) {
            case 'freegift_items':
            case 'freegift_cart':
            case 'freegift_product':
            case 'freegift_spent':
                return true;
        }
        return false;
    }

    /**
     * @param string $actionCode
     * @return array
     */
    protected function _getActionSwitcherConfigActions($actionCode)
    {
        $actions = [];
        $i = 0;
        foreach ($this->_fieldsToActions as $field => $actionCodes) {
            if (strpos($field, 'freegiftrule[') === false && !$this->_isFreeGiftAction($actionCode)) {
                $enabledForAction = true;
            } else {
                $enabledForAction = in_array($actionCode, $actionCodes);
            }
            $callback = $enabledForAction ? 'show' : 'hide';
            $action = $enabledForAction ? 'enable' : 'disable';
            $actions[$i++] = [
                'target' => 'sales_rule_form.sales_rule_form.actions.' . $field,
                'callback' => $callback,
            ];
            $actions[$i++] = [
                'target' => 'sales_rule_form.sales_rule_form.actions.' . $field,
                'callback' => $action,
            ];
            $actions[$i++] = [
                'target' => 'sales_rule_form.sales_rule_form.actions.' . $field,
                'callback' => 'reset',
            ];
        }
        return $actions;
    }
}
