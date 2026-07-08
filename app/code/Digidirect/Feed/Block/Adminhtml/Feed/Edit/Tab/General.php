<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;
use Magento\Store\Model\System\Store as SystemStore;
use Digidirect\Feed\Model\Config\Source\Type as SourceType;

class General extends Form
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var SourceYesNo
     */
    protected $sourceYesNo;

    /**
     * @var SourceType
     */
    protected $sourceType;

    /**
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * General constructor.
     * @param Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param SourceYesNo $sourceYesNo
     * @param SourceType $sourceType
     * @param SystemStore $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        SourceYesNo $sourceYesNo,
        SourceType $sourceType,
        SystemStore $systemStore,
        array $data = []
    ) {
        $this->context = $context;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->sourceYesNo = $sourceYesNo;
        $this->sourceType = $sourceType;
        $this->systemStore = $systemStore;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Digidirect\Feed\Model\Feed $model */
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $general->addField('feed_id', 'hidden', [
                'name' => 'feed_id',
                'value' => $model->getId(),
            ]);
        }

        $general->addField('name', 'text', [
            'label' => __('Name'),
            'required' => true,
            'name' => 'name',
            'value' => $model->getData('name')
        ]);

        $general->addField('filename', 'text', [
            'label' => __('Filename'),
            'required' => true,
            'name' => 'filename',
            'value' => $model->getData('filename')
        ]);

        $general->addField('type', 'select', [
            'label' => __('File Type'),
            'required' => true,
            'name' => 'type',
            'value' => $model->getData('type'),
            'values' => $this->sourceType->toOptionArray(),
            'onchange' => 'feedMapping.changeFormat(this);',
            'disabled' => $model->getName() ? true : false,
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $general->addField('store_id', 'select', [
                'label' => __('Store View'),
                'required' => true,
                'name' => 'store_id',
                'value' => $model->getData('store_id'),
                'values' => $this->systemStore->getStoreValuesForForm(),
            ]);
        } else {
            $general->addField('store_id', 'hidden', [
                'name' => 'store_id',
                'value' => $this->context->getStoreManager()->getStore(true)->getId(),
            ]);
        }

        $general->addField('is_active', 'select', [
            'label' => __('Is Active'),
            'required' => true,
            'name' => 'is_active',
            'value' => $model->getData('is_active'),
            'values' => $this->sourceYesNo->toArray(),
        ]);

        if ($model->getUrl()) {
            $general->addField('generation_info', 'note', [
                'text' => $this->getLayout()->createBlock('\Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\General\Info')
                    ->toHtml(),
            ]);
        }

        return parent::_prepareForm();
    }
}
