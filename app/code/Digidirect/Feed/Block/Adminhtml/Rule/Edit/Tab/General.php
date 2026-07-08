<?php

namespace Digidirect\Feed\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;
use Digidirect\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

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
     * @var SourceYesNo
     */
    protected $sourceYesNo;

    /**
     * @var FeedCollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * General constructor.
     * @param Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param SourceYesNo $sourceYesNo
     * @param FeedCollectionFactory $feedCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        SourceYesNo $sourceYesNo,
        FeedCollectionFactory $feedCollectionFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->sourceYesNo = $sourceYesNo;
        $this->feedCollectionFactory = $feedCollectionFactory;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Digidirect\Feed\Model\Rule $model */
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('data');
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $general->addField('rule_id', 'hidden', [
                'name' => 'rule_id',
                'value' => $model->getId(),
            ]);
        }

        $general->addField('name', 'text', [
            'label' => __('Name'),
            'required' => true,
            'name' => 'name',
            'value' => $model->getName(),
        ]);

        $general->addField('feeds', 'checkboxes', [
            'label' => __('Feeds'),
            'required' => false,
            'name' => 'feed_ids[]',
            'values' => $this->feedCollectionFactory->create()->toOptionArray(),
            'checked' => $model->getFeedIds(),
        ]);

        return parent::_prepareForm();
    }
}
