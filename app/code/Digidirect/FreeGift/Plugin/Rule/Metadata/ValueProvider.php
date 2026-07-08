<?php

namespace Digidirect\FreeGift\Plugin\Rule\Metadata;

use Digidirect\FreeGift\Api\RuleRepositoryInterface;
use Digidirect\FreeGift\Api\Data\RuleInterface;

class ValueProvider
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var array
     */
    protected $_freeGiftActions = [];

    /**
     * @var array
     */
    protected $_freeGiftRuleTypeOptions = [];

    /***
     * @var array
     */
    protected $_metaDataValuesConfig;

    /**
     * ValueProvider constructor.
     *
     * @param RuleRepositoryInterface $ruleRepository
     * @param \Magento\Framework\Registry $registry
     * @param array $freeGiftActions
     * @param array $freeGiftRuleTypeOptions
     * @param array $metaDataValuesConfig
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\Registry $registry,
        array $freeGiftActions = [],
        array $freeGiftRuleTypeOptions = [],
        array $metaDataValuesConfig = []
    ) {
        $this->_ruleRepository = $ruleRepository;
        $this->_coreRegistry = $registry;
        $this->_freeGiftActions = $freeGiftActions;
        $this->_freeGiftRuleTypeOptions = $freeGiftRuleTypeOptions;
        $this->_metaDataValuesConfig = $metaDataValuesConfig;
    }

    /**
     * Do actions around getMetadataValues()
     *
     * @param \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject
     * @param \Closure $proceed
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetMetadataValues(
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject,
        \Closure $proceed,
        \Magento\SalesRule\Model\Rule $rule
    ) {
        $result = $proceed($rule);
        $salesrule = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        if (!$salesrule || empty($this->_freeGiftActions)) {
            return $result;
        }

        if (isset($result['actions']['children']['simple_action'])) {
            $result['actions']['children']['simple_action']['arguments']['data']['config']['options'] = array_merge(
                $result['actions']['children']['simple_action']['arguments']['data']['config']['options'],
                $this->_freeGiftActions
            );
        }

        $result['actions']['children']['freegiftrule[type]']['arguments']['data']['config']['options'] =
            $this->_freeGiftRuleTypeOptions;

        $freeGiftRule = $this->_ruleRepository->loadBySalesrule($salesrule);
        if ($freeGiftRule->getId()) {
            foreach ($this->_metaDataValuesConfig as $from => $to) {
                $methodName = $this->convertFieldNameToMethodName($from);
                $value = $freeGiftRule->$methodName();
                $result['actions']['children']['freegiftrule[' . $to . ']']['arguments']['data']['config']['default'] =
                    $value;
                $result['actions']['children']['freegiftrule[' . $to . ']']['arguments']['data']['config']['value'] =
                    $value;
            }
        }

        return $result;
    }

    /**
     * Convert fieldName to method name
     *
     * @param string $fieldName
     * @return string
     */
    protected function convertFieldNameToMethodName($fieldName)
    {
        switch ($fieldName) {
            case RuleInterface::FIELD_ENABLE_ON_PDP:
                return 'isEnabledOnPdp';
            case RuleInterface::FIELD_IS_HIDDEN_FOR_CUSTOMER:
                $prefix = '';
                break;
            default:
                $prefix = 'get';
        }
        return $prefix . str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
    }
}
