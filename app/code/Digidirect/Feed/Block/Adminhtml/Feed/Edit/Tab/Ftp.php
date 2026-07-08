<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Config\Model\Config\Source\Yesno as SourceYesNo;
use Digidirect\Feed\Model\Config\Source\FtpProtocol as SourceFtpProtocol;

class Ftp extends Form
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
     * @var SourceFtpProtocol
     */
    protected $sourceFtpProtocol;

    /**
     * Ftp constructor.
     * @param Context $context
     * @param FormFactory $formFactory
     * @param Registry $registry
     * @param SourceYesNo $sourceYesNo
     * @param SourceFtpProtocol $sourceFtpProtocol
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Registry $registry,
        SourceYesNo $sourceYesNo,
        SourceFtpProtocol $sourceFtpProtocol,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->sourceYesNo = $sourceYesNo;
        $this->sourceFtpProtocol = $sourceFtpProtocol;

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

        $general = $form->addFieldset('general', ['legend' => __('FTP Settings')]);

        $general->addField('ftp', 'select', [
            'name' => 'ftp',
            'label' => __('Enabled'),
            'required' => false,
            'values' => $this->sourceYesNo->toArray(),
            'value' => $model->getFtp(),
        ]);

        $general->addField('ftp_protocol', 'select', [
            'name' => 'ftp_protocol',
            'label' => __('Protocol'),
            'required' => true,
            'values' => $this->sourceFtpProtocol->toOptionArray(),
            'value' => $model->getFtpProtocol(),
        ]);

        $general->addField('ftp_host', 'text', [
            'name' => 'ftp_host',
            'label' => __('Host Name'),
            'required' => true,
            'value' => $model->getFtpHost(),
        ]);

        $general->addField('ftp_user', 'text', [
            'name' => 'ftp_user',
            'label' => __('User Name'),
            'required' => false,
            'value' => $model->getFtpUser(),
        ]);

        $general->addField('ftp_password', 'password', [
            'name' => 'ftp_password',
            'label' => __('Password'),
            'required' => false,
            'value' => $model->getFtpPassword(),
        ]);

        $general->addField('ftp_path', 'text', [
            'name' => 'ftp_path',
            'label' => __('Path'),
            'required' => false,
            'value' => $model->getFtpPath(),
        ]);

        $general->addField('ftp_passive_mode', 'select', [
            'name' => 'ftp_passive_mode',
            'label' => __('Passive mode'),
            'required' => false,
            'values' => $this->sourceYesNo->toArray(),
            'value' => $model->getFtpPassiveMode(),
        ]);

        $this->_addTestConnectionFields($general);

        $this->_addDependenceBlock();

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $fieldSet
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addTestConnectionFields(\Magento\Framework\Data\Form\Element\AbstractElement $fieldSet)
    {
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label' => '<i class="fa fa-exchange fa-fw"></i> ' . __('Test Connection'),
            'title' => __('Test Connection'),
            'class' => 'secondary',
            'data_attribute' => [
                'mage-init' => [
                    'ftpValidator' => [
                        'url' => $this->getUrl('digidirect_feed/feed/validateFtp')
                    ],
                ]
            ]
        ]);

        $fieldSet->addField('ftp_check_connection', 'note', [
            'name' => 'ftp_check_connection',
            'text' => $button->toHtml(),
        ]);

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addDependenceBlock()
    {
        /**
         * @var $dependenceBlock \Magento\Backend\Block\Widget\Form\Element\Dependence
         */
        $dependenceBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');

        $dependenceBlock->addFieldMap('ftp', 'ftp')
            ->addFieldMap('ftp_protocol', 'ftp_protocol')
            ->addFieldMap('ftp_host', 'ftp_host')
            ->addFieldMap('ftp_user', 'ftp_user')
            ->addFieldMap('ftp_password', 'ftp_password')
            ->addFieldMap('ftp_path', 'ftp_path')
            ->addFieldMap('ftp_passive_mode', 'ftp_passive_mode')
            ->addFieldMap('ftp_check_connection', 'ftp_check_connection')
            ->addFieldDependence('ftp_protocol', 'ftp', '1')
            ->addFieldDependence('ftp_host', 'ftp', '1')
            ->addFieldDependence('ftp_user', 'ftp', '1')
            ->addFieldDependence('ftp_password', 'ftp', '1')
            ->addFieldDependence('ftp_path', 'ftp', '1')
            ->addFieldDependence('ftp_passive_mode', 'ftp', '1')
            ->addFieldDependence('ftp_check_connection', 'ftp', '1');

        $this->setChild('form_after', $dependenceBlock);

        return $this;
    }
}
