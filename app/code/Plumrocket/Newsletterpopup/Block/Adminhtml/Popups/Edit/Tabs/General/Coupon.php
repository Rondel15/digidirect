<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs\General;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Helper\Coupon as CouponHelper;

class Coupon extends Generic
{
    protected $_couponHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CouponHelper $couponHelper,
        array $data = []
    ) {
        $this->_couponHelper = $couponHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');
        $disabled = $model->getCouponCode() == 0;

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('coupon_fieldset', ['legend' => __('Coupon Settings')]);

        $fieldset->addType('extended_label', 'Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Renderer\Label');

        $fieldset->addField('just_label', 'extended_label', [
            'hidden' => !$disabled
        ]);
        $model->setData('just_label', __('Coupon code is not selected in the General section above. Coupon Settings section is disabled.'));

        $fieldset->addField('code_length', 'text', [
            'name'     => 'code_length',
            'label'    => __('Code Length'),
            'required' => true,
            'note'     => __('Excluding prefix, suffix and separators.'),
            'value'    => $this->_couponHelper->getDefaultLength(),
            'class'    => 'validate-digits validate-greater-than-zero',
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_format', 'select', [
            'label'    => __('Code Format'),
            'name'     => 'code_format',
            'options'  => $this->_couponHelper->getFormatsList(),
            'required' => true,
            'value'    => $this->_couponHelper->getDefaultFormat(),
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_prefix', 'text', [
            'name'  => 'code_prefix',
            'label' => __('Code Prefix'),
            'value' => $this->_couponHelper->getDefaultPrefix(),
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_suffix', 'text', [
            'name'  => 'code_suffix',
            'label' => __('Code Suffix'),
            'value' => $this->_couponHelper->getDefaultSuffix(),
            'disabled' => $disabled,
        ]);

        $fieldset->addField('code_dash', 'text', [
            'name'  => 'code_dash',
            'label' => __('Dash Every X Characters'),
            'note'  => __('If empty no separation.'),
            'value' => $this->_couponHelper->getDefaultDashInterval(),
            'class' => 'validate-digits',
            'disabled' => $disabled,
        ]);

        /**
         * Set Extended Time Field for coupon_expiration_time
         * Custom Extended Time Format must be defined in Data_Helper
         */
        $fieldset->addType(
            'extended_time',
            'Plumrocket\Newsletterpopup\Block\Adminhtml\Renderer\Time'
        );

        $fieldset->addField('coupon_expiration_time', 'extended_time', [
            'name' => 'coupon_expiration_time',
            'label' => __('Coupon Expiration Time'),
            'note' => __('Set coupon dynamic expiration time. Select "00" in all fields for coupon code to never expire.'),
            'disabled' => $disabled,
        ]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
