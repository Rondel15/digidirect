<?php

namespace Digidirect\ExtendedShippingRates\Model\Config\Source;

use Digidirect\ExtendedShippingRates\Api\CustomConditionInterface;

/**
 * Class AvailableCustomConditions
 * @package Digidirect\ExtendedShippingRates\Model\Config\Source
 */
class AvailableCustomConditions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $customConditionList;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $customConditionList
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $customConditionList = []
    ) {
        $this->_objectManager = $objectManager;
        $this->customConditionList = $this->prepareConditionList($customConditionList);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        if (!empty($this->getCustomConditionsArray())) {
            foreach ($this->getCustomConditionsArray() as $key => $condition) {
                $options[] = ['value' => $key, 'label' => $condition->getLabel()];
            }
        }
        return $options;
    }

    /**
     * @param array $customConditionList
     * @return array
     */
    public function prepareConditionList($customConditionList)
    {
        $conditionList = [];
        if (!empty($customConditionList)) {
            foreach ($customConditionList as $key => $condition) {
                if (is_string($condition)) {
                    $condition = $this->_objectManager->create($condition);
                }

                if ($condition instanceof CustomConditionInterface) {
                    $conditionList[$key] = $condition;
                }
            }
        }
        return $conditionList;
    }

    /**
     * @return array
     */
    public function getCustomConditionsArray()
    {
        return $this->customConditionList;
    }
}
