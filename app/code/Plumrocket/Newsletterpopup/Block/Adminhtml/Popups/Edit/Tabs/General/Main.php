<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\General;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\RadiosWithImage;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Coupon;
use Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Type as PopupTypeSource;
use Plumrocket\Newsletterpopup\Model\Config\Source\Redirectto;
use Plumrocket\Newsletterpopup\Model\Config\Source\Status;
use Plumrocket\Newsletterpopup\Model\Source\Email\Template;
use Magento\Backend\Block\Widget\Form\Element\Dependence;

class Main extends Generic
{
    private $_adminhtmlHelper;
    private $_sourceRedirectto;
    private $_sourceStatus;
    private $_sourceEmailTemplate;
    private $_sourceYesno;

    /**
     * @var PopupTypeSource
     */
    private $popupTypeSource;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Config\Source\Popup\Coupon
     */
    private $popupRuleSource;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Adminhtml $adminhtmlHelper,
        Redirectto $sourceRedirectto,
        Status $sourceStatus,
        Template $sourceEmailTemplate,
        Yesno $sourceYesno,
        PopupTypeSource $popupTypeSource,
        Coupon $popupRuleSource,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->_sourceRedirectto = $sourceRedirectto;
        $this->_sourceStatus = $sourceStatus;
        $this->_sourceEmailTemplate = $sourceEmailTemplate;
        $this->_sourceYesno = $sourceYesno;
        $this->popupTypeSource = $popupTypeSource;
        $this->popupRuleSource = $popupRuleSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /** @var \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $model */
        $model = $this->_coreRegistry->registry('current_model');
        $model = $this->convertDateToUTC($model);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('General')]);

        $fieldset->addField('name', 'text', [
            'name'      => 'name',
            'label'     => __('Popup Name'),
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $fieldset->addField('status', 'select', [
            'name'      => 'status',
            'label'     => __('Status'),
            'values'    => $this->_sourceStatus->toOptionHash(),
        ]);

        $fieldset->addType('radios_with_image', RadiosWithImage::class);
        $fieldset->addField('type', 'radios_with_image', [
            'name'      => 'type',
            'label'     => __('Type'),
            'values'    => $this->popupTypeSource->toOptionArray(),
            'note'      => 'To display this widget template, create a new "Newsletter Popup Form" Widget.',
        ]);

        $fieldset->addField('coupon_code', 'select', [
            'name'      => 'coupon_code',
            'label'     => __('Use Coupon Code'),
            'values'    => $this->popupRuleSource->toOptionArray(),
            'note'      => 'Select Shopping Cart Price Rule that should be used to award users who opted-in for email newsletter. ' .
                'You can only use rules that have the "Use Auto Generation" checkbox selected and do not have the expiration date specified.',
        ]);

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::MEDIUM
        );
        // $timeFormat = $this->_localeDate->getTimeFormat(
        //     \IntlDateFormatter::SHORT
        // );
        $fieldset->addField('start_date', 'date', [
            'name'          => 'start_date',
            'label'         => __('Start Date'),
            'input_format'  => DateTime::DATE_INTERNAL_FORMAT,
            'date_format'   => $dateFormat,
            'disabled'      => !$model->isModal(),
            'note'          => $this->_adminhtmlHelper->getNoteForDisabledByTypeField($model),
        ]);

        $fieldset->addField('end_date', 'date', [
            'name'          => 'end_date',
            'label'         => __('End date'),
            'image'         => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format'  => DateTime::DATE_INTERNAL_FORMAT,
            'date_format'   => $dateFormat,
            'note'          => $this->_adminhtmlHelper->getNoteForDisabledByTypeField(
                $model,
                'Period when newsletter popup is active. Dates will be automatically ' .
                'loaded from selected Shopping Cart Price Rule but can be manually changed.'
            ),
            'disabled'      => !$model->isModal(),
        ]);

        $successPage = $fieldset->addField('success_page', 'select', [
            'name'      => 'success_page',
            'label'     => __('Subscription Success Page'),
            'values'    => $this->_sourceRedirectto->toOptionHash(),
        ]);

        $customSuccessPage = $fieldset->addField('custom_success_page', 'text', [
            'name'      => 'custom_success_page',
            'label'     => __('Custom Success Page URL'),
            'note'      => 'Please enter the full URL of the page, including the domain name, to which you will be redirecting.',
        ]);

        $sendEmail = $fieldset->addField('send_email', 'select', [
            'name'      => 'send_email',
            'label'     => __('Send Autoresponder Email'),
            'values'    => $this->_sourceYesno->toOptionArray(),
            'note'      => 'Send email when user successfully subscribed to your email newsletter.',
        ]);

        $emailTemplate = $fieldset->addField('email_template', 'select', [
            'name'      => 'email_template',
            'label'     => __('Autoresponder Email Template'),
            'values'    => $this->_sourceEmailTemplate->toOptionArray(),
            'note'      => 'Magento will send this email after user successfully subscribed to your email newsletter.',
        ]);

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                Dependence::class
            )->addFieldMap(
                $successPage->getHtmlId(),
                $successPage->getName()
            )->addFieldMap(
                $customSuccessPage->getHtmlId(),
                $customSuccessPage->getName()
            )->addFieldDependence(
                $customSuccessPage->getName(),
                $successPage->getName(),
                '__custom__'
            )
            ->addFieldMap(
                $sendEmail->getHtmlId(),
                $sendEmail->getName()
            )->addFieldMap(
                $emailTemplate->getHtmlId(),
                $emailTemplate->getName()
            )->addFieldDependence(
                $emailTemplate->getName(),
                $sendEmail->getName(),
                '1'
            )
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface|\Plumrocket\Newsletterpopup\Model\Popup $model
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    protected function convertDateToUTC($model)
    {
        foreach (['start_date', 'end_date'] as $field) {
            $value = !$model->getData($field)
                ? null
                : $this->_localeDate->convertConfigTimeToUtc($model->getData($field));

            $model->setData($field, $value);
        }

        return $model;
    }
}
