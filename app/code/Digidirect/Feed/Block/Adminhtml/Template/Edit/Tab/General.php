<?php

namespace Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\Config\Source\Type as SourceType;

class General extends Form
{

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var SourceType
     */
    protected $sourceType;

    /**
     * General constructor.
     * @param Context $context
     * @param SourceType $sourceType
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        SourceType $sourceType,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->sourceType = $sourceType;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $general->addField('template_id', 'hidden', [
                'name' => 'template_id',
                'value' => $model->getId(),
            ]);
        }

        $general->addField('name', 'text', [
            'label' => __('Name'),
            'required' => true,
            'name' => 'name',
            'value' => $model->getName(),
        ]);

        $general->addField('type', 'select', [
            'label' => __('File Type'),
            'required' => true,
            'name' => 'type',
            'value' => $model->getType(),
            'values' => $this->sourceType->toOptionArray(),
        ]);

        return parent::_prepareForm();
    }
}
