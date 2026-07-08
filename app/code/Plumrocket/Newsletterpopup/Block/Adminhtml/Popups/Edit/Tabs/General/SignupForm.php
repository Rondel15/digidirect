<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\General;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Config\Source\SignupMethod;

class SignupForm extends Generic
{
    protected $_dataHelper;
    protected $_sourceSignupMethod;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $dataHelper,
        SignupMethod $sourceSignupMethod,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_sourceSignupMethod = $sourceSignupMethod;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('signup_fieldset', ['legend' => __('Signup Form')]);

        $fieldset->addField('signup_method', 'select', [
            'name'      => 'signup_method',
            'label'     => __('User Sign-Up Method'),
            'values'    => $this->_sourceSignupMethod->toOptionHash(),
            'note'      => 'Please note: if customer registration is selected and password field is not enabled below, then passwords will be generated automatically for each user and sent by email'
        ]);

        $fieldset->addField('signup_fields', 'text', [
            'name'      => 'signup_fields',
            'label'     => __('Enable Form Fields'),
            'note'      => 'Selected fields will be displayed on sign-up form. Please note, "Email" is required field.'
        ]);

        $form->getElement('signup_fields')
        ->setRenderer(
            $this->getLayout()->createBlock('Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\InputTable')
        )
        ->getRenderer()
            ->setContainerFieldId('signup_fields')
            ->setRowKey('name')
            ->addColumn('enable', [
                'header'    => __('Enable'),
                'index'     => 'enable',
                'type'      => 'checkbox',
                'value'     => '1',
                'width'     => '5%',
            ])
            ->addColumn('orig_label', [
                'header'    => __('Field'),
                'index'     => 'orig_label',
                'type'      => 'label',
                'width'     => '40%',
            ])
            ->addColumn('label', [
                'header'    => __('Displayed Name'),
                'index'     => 'label',
                'type'      => 'input',
                'renderer'  => \Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer\Input::class,
                'width'     => '40%',
            ])
            ->addColumn('sort_order', [
                'header'    => __('Sort Order'),
                'index'     => 'sort_order',
                'type'      => 'input',
                'width'     => '40px',
                'width'     => '15%',
            ])
            ->setArray($this->_getFields($model->getId()));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getFields($popup_id)
    {
        $labels = [
            'email'             => __('Your Email Address'),
            'confirm_email'     => __('Confirm Your Email Address'),
        ];

        $systemItems = $this->_dataHelper->getPopupFormFields(0, false);
        $popupItems = $this->_dataHelper->getPopupFormFields($popup_id, false);

        $result = [];
        $rowId = 1;
        foreach ($systemItems as $name => $_systemItem) {
            if (array_key_exists($name, $popupItems)) {
                $data = $popupItems[$name]->getData();
                $orig_label = $_systemItem['label'];
            } else {
                $data = $systemItems[$name]->getData();
                $orig_label = $data['label'];
                if (array_key_exists($name, $labels)) {
                    $data['label'] = $labels[$name];
                }
            }

            // Agreement row has different origin label and label in textarea
            if ('agreement' === $_systemItem['name']) {
                $orig_label = __('Consent Checkbox');
            }

            $data['orig_label'] = $orig_label;
            $data['id'] = 'signup_fields_' . $rowId;
            $result[] = $data;

            $rowId++;
        }

        uasort($result, function ($a, $b) {
            return $a['sort_order'] > $b['sort_order'] ? 1 : 0;
        });

        return $result;
    }
}
