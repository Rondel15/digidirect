<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Block\Adminhtml\Product\Form;


class Content  extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_category;
    protected $helperData;
    protected $groupList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Itoris\PriceMatch\Model\Source\GroupList $groupList,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Itoris\PriceMatch\Helper\Data $helperData,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->groupList = $groupList;
        $this->helperData = $helperData;
        parent::__construct($context, $registry, $formFactory,  $data);
    }

    protected function _prepareForm()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $productId = $this->_request->getParam('id');
        $data = $this->helperData->getSettingsFinal($productId, $storeId);

        $form = $this->_formFactory->create([
            'data'=>[
                'id'      => 'iitoris-price-match-form',
                'action' => $this->getData('action'),
                'method'  => 'post',
            ]
        ]
        );

        $fieldset = $form->addFieldset( 'itoris-price-match-fieldset', ['legend'=>false, 'style'=>'padding-top: 0px;'] );

        $showLink = isset($data['show_link']) ? $data['show_link'] : 1;
        $fieldset->addField('itoris_pricematch_show_link', 'select', [
            'name'     => 'itoris_pricematch_show_link',
            'label'    => __('Show the Price Match link'),
            'data-form-part'=> 'product_form',
            'title'    => __('Show the Price Match link'),
            'required' => false,
  //          'disabled' => $data['show_link_check'],
            'multiple' => false,
            'value'    => $showLink,
            'values' => [
                0=> __('No'),
                1 => __('Yes')
            ]
        ]);

        $groupList = $this->groupList->toOptionArray();
        $checked = $data['group_list_check']?'checked':'';
        $fieldset->addField('itoris_pricematch_group_list', 'multiselect', [
            'name'     => 'itoris_pricematch_group_list',
            'label'    => __('Allowed Customer Groups'),
            'data-form-part'=> 'product_form',
            'title'    => __('Allowed Customer Groups'),
            'value'    => $data['group_list'],
            'disabled' => $data['group_list_check'],
            'required' => false,
            'values' =>  $groupList,
            'multiple' => true,
        ])->setAfterElementHtml("
          <br><label for='itoris_pricematch_group_list_check' class='choice use-default'>
            <input type='checkbox' name='itoris_pricematch_group_list_check' class='use-default-control' id='itoris_pricematch_group_list_check'  
            onclick='toggleValueElements(this, this.parentNode.parentNode.parentNode)' data-form-part='product_form' ".$checked." >
            <span class='use-default-label'>".($storeId ? __("Use Default") : __("Use System"))."</span>
        </label>
         ");

        $fieldset->addField('itoris_pricematch_group_list_hidden', 'hidden', [
            'name'     => 'itoris_pricematch_group_list_hidden',
            'data-form-part'=> 'product_form',
            'required' => false,
            'value' => $data['group_list'],
        ]);

        $checked = $data['link_text_check']?'checked':'';
        $fieldset->addField('itoris_pricematch_link_text', 'text', [
            'name'     => 'itoris_pricematch_link_text',
            'label'    => __('Link Text'),
            'data-form-part'=> 'product_form',
            'title'    => __('Link Text'),
            'value'    => $data['link_text'],
            'disabled' => $data['link_text_check'],
            'required' => false,

        ])->setAfterElementHtml("
            <br><label for='itoris_pricematch_link_text_check' class='choice use-default'>
            <input type='checkbox' name='itoris_pricematch_link_text_check' class='use-default-control' id='itoris_pricematch_link_text_check'  
            onclick='toggleValueElements(this, this.parentNode.parentNode.parentNode)' data-form-part='product_form' ".$checked." >
            <span class='use-default-label'>".($storeId ? __("Use Default") :__("Use System"))."</span>
        </label>" );

        $checked = $data['comment_popup_check']?'checked':'';
        $fieldset->addField('itoris_pricematch_comment_popup', 'textarea', [
            'name'     => 'itoris_pricematch_comment_popup',
            'label'    => __('Comment in Popup'),
            'data-form-part'=> 'product_form',
            'title'    => __('Comment in Popup'),
            'value'    => $data['comment_popup'],
            'disabled' => $data['comment_popup_check'],
            'required' => false,
        ])->setAfterElementHtml("
          <label for='itoris_pricematch_comment_popup_check' class='choice use-default'>
            <input type='checkbox' name='itoris_pricematch_comment_popup_check' class='use-default-control' id='itoris_pricematch_comment_popup_check'  
            onclick='toggleValueElements(this, this.parentNode.parentNode.parentNode)' data-form-part='product_form' ".$checked." >
            <span class='use-default-label'>".($storeId ? __("Use Default") :__("Use System"))."</span>
        </label>
         ");

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}