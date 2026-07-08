<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;
use Digidirect\Feed\Model\Config\Source\EmailEvent as SourceEmailEvent;

class Additional extends Form
{
    /**
     * @var SourceYesNo
     */
    protected $sourceYesNo;

    /**
     * @var SourceEmailEvent
     */
    protected $sourceEmailEvent;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Additional constructor.
     * @param Context $context
     * @param SourceYesNo $sourceYesNo
     * @param SourceEmailEvent $sourceEmailEvent
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        SourceYesNo $sourceYesNo,
        SourceEmailEvent $sourceEmailEvent,
        FormFactory $formFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->sourceYesNo = $sourceYesNo;
        $this->sourceEmailEvent = $sourceEmailEvent;
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

        $email = $form->addFieldset('email_fieldset', ['legend' => __('Email Notifications')]);

        $email->addField('notification_emails', 'text', [
            'name' => 'notification_emails',
            'label' => __('Email'),
            'value' => $model->getNotificationEmails(),
            'note' => __('Separate emails by commas')
        ]);

        $email->addField('notification_events', 'multiselect', [
            'name' => 'notification_events',
            'label' => __('Notification Events'),
            'value' => $model->getNotificationEvents(),
            'values' => $this->sourceEmailEvent->toOptionArray(),
        ]);

        $report = $form->addFieldset('report_fieldset', ['legend' => __('Reports Configuration')]);

        $report->addField('report_enabled', 'select', [
            'name' => 'report_enabled',
            'label' => __('Enable Reports'),
            'required' => false,
            'values' => $this->sourceYesNo->toArray(),
            'value' => $model->getReportEnabled(),
            'note' => __(
                'If enabled, extension append two special arguments (ff=, fp=) to product url for track clicks and orders'
            ),
        ]);

        return parent::_prepareForm();
    }
}
