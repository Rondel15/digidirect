<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Quote\Edit\Button;

use Magento\Framework\Registry;
use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\ExtendedShippingRates\Model\RuleFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Generic implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * Generic constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RuleFactory $ruleFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Get rule: current or empty
     *
     * @return \Digidirect\ExtendedShippingRates\Model\Rule
     */
    public function getRule()
    {
        $rule = $this->registry->registry(Rule::CURRENT_PROMO_QUOTE_RULE);
        if (!$rule) {
            $rule = $this->ruleFactory->create();
        }

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
