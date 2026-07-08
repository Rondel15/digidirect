<?php

namespace Digidirect\Feed\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as RuleConditions;

class Rule extends Form
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Fieldset
     */
    protected $fieldset;

    /**
     * @var RuleConditions
     */
    protected $conditions;

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
     * @param Fieldset $fieldset
     * @param RuleConditions $conditions
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Fieldset $fieldset,
        RuleConditions $conditions,
        FormFactory $formFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->context = $context;
        $this->fieldset = $fieldset;
        $this->conditions = $conditions;
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

        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->fieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl(
                '*/rule/newConditionHtml/form/rule_conditions_fieldset',
                ['rule_type' => $model->getType()]
            ));

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Filters (leave blank for select all products)')]
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', [
            'name' => 'conditions',
            'label' => __('Filters'),
            'title' => __('Filters'),
            'required' => true,
        ])->setRule($model)
            ->setRenderer($this->conditions);

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
