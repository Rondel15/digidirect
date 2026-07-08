<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;
use Digidirect\Feed\Api\CronHelperInterface;
use Digidirect\Feed\Model\Config\Source\Day as SourceDay;
use Digidirect\Feed\Model\Config\Source\Time as SourceTime;

class Cron extends Form
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
     * @var SourceDay
     */
    protected $sourceDay;

    /**
     * @var SourceTime
     */
    protected $sourceTime;

    /**
     * @var CronHelperInterface
     */
    protected $cronHelper;

    /**
     * Cron constructor.
     * @param Context $context
     * @param SourceYesNo $sourceYesNo
     * @param SourceDay $sourceDay
     * @param SourceTime $sourceTime
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param CronHelperInterface $cronHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        SourceYesNo $sourceYesNo,
        SourceDay $sourceDay,
        SourceTime $sourceTime,
        CronHelperInterface $cronHelper,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->sourceYesNo = $sourceYesNo;
        $this->sourceDay = $sourceDay;
        $this->sourceTime = $sourceTime;
        $this->cronHelper = $cronHelper;

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

        $general = $form->addFieldset('general', ['legend' => __('Scheduled Task')]);

        $general->addField('cron', 'select', [
            'name' => 'cron',
            'label' => __('Enabled'),
            'value' => $model->getCron(),
            'values' => $this->sourceYesNo->toArray(),
            'note' => __(
                'If enabled, extension will generate feed by schedule.'
                . '<br/>To generate feed by schedule, magento cron must be configured.'
                . '<br/>* Please, pay attention that the time has to be specified in UTC-0 format.'
            )
        ]);

        $general->addField('cron_day', 'multiselect', [
            'label' => __('Days of the week'),
            'required' => false,
            'name' => 'cron_day',
            'values' => $this->sourceDay->toOptionArray(),
            'value' => $model->getCronDay(),
        ]);

        $general->addField('cron_time', 'multiselect', [
            'label' => __('Time of the day'),
            'required' => false,
            'name' => 'cron_time',
            'values' => $this->sourceTime->toOptionArray(),
            'value' => $model->getCronTime(),
        ]);

        list($status, $message) = $this->cronHelper->checkCronStatus(false, false);

        if (!$status) {
            $general->addField('cron_job_status', 'note', [
                'label' => __('Cronjob status'),
                'required' => false,
                'name' => 'cron_day',
                'note' => $message,
            ]);
        }

        /**
         * @var $dependenceBlock \Magento\Backend\Block\Widget\Form\Element\Dependence
         */
        $dependenceBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');

        $dependenceBlock->addFieldMap('cron', 'cron')
            ->addFieldMap('cron_day', 'cron_day')
            ->addFieldMap('cron_time', 'cron_time')
            ->addFieldMap('cron_job_status', 'cron_job_status')
            ->addFieldDependence('cron_day', 'cron', '1')
            ->addFieldDependence('cron_time', 'cron', '1')
            ->addFieldDependence('cron_job_status', 'cron', '1');

        $this->setChild('form_after', $dependenceBlock);

        return parent::_prepareForm();
    }
}
