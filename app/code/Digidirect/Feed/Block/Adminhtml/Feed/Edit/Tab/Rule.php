<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

class Rule extends Form
{
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Rule constructor.
     * @param Context $context
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleCollectionFactory $ruleCollectionFactory,
        FormFactory $formFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);

        $productFieldset = $form->addFieldset('feed_tab_rule_product', ['legend' => __('Product Filters')]);

        $collection = $this->ruleCollectionFactory->create();
        foreach ($collection as $rule) {
            $this->addRuleToFieldset($rule, $productFieldset, $model);
        }

        return parent::_prepareForm();
    }

    /**
     * Add rule output to fieldset
     *
     * @param \Digidirect\Feed\Model\Rule $rule
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Digidirect\Feed\Model\Feed $feed
     * @return $this
     */
    protected function addRuleToFieldset($rule, $fieldset, $feed)
    {
        $fieldset->addField('rule' . $rule->getId(), 'checkbox', [
            'label' => $rule->getName(),
            'name' => 'rule_ids[' . $rule->getId() . ']',
            'checked' => in_array($rule->getId(), $feed->getRuleIds()),
            'required' => false,
            'note' => $rule->toString(),
        ]);

        return $this;
    }
}
