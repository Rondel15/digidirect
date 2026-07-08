<?php

namespace Digidirect\Feed\Block\Adminhtml\Template\Import;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\FormFactory;
use Digidirect\Feed\Model\Config\Source\Template as SourceTemplate;

class Form extends WidgetForm
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
     * Form constructor.
     * @param Context $context
     * @param SourceTemplate $sourceTemplate
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        SourceTemplate $sourceTemplate,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->sourceTemplate = $sourceTemplate;
        $this->formFactory = $formFactory;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $general = $form->addFieldset('general', []);

        $general->addField('import', 'hidden', [
            'name' => 'import',
            'value' => 1,
        ]);

        $general->addField('template', 'multiselect', [
            'name' => 'template',
            'label' => __('Templates'),
            'required' => true,
            'values' => $this->sourceTemplate->toOptionArray(true),
        ]);

        $form->setAction($this->getUrl('*/*/import'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
