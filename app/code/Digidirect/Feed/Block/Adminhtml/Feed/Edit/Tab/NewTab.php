<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\Config\Source\Template as SourceTemplate;

class NewTab extends Form
{
    /**
     * @var SourceTemplate
     */
    protected $sourceTemplate;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * NewTab constructor.
     * @param Context $context
     * @param SourceTemplate $sourceTemplate
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        SourceTemplate $sourceTemplate,
        FormFactory $formFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->sourceTemplate = $sourceTemplate;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'continue_button',
            $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
                ->setData([
                    'label' => __('Continue'),
                    'class' => 'primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndContinueEdit',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['auto_apply' => 1]]],
                            ],
                        ],
                    ],
                ])
        );

        return parent::_prepareLayout();
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

        $general = $form->addFieldset('general', ['legend' => __('Settings')]);

        $general->addField('template_id', 'select', [
            'label' => __('Template'),
            'required' => false,
            'name' => 'template_id',
            'value' => $model->getType(),
            'values' => $this->sourceTemplate->toOptionArray(),
        ]);

        $general->addField('continue_button', 'note', [
            'text' => $this->getChildHtml('continue_button'),
        ]);

        return parent::_prepareForm();
    }
}
