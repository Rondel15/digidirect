<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Plumrocket\Newsletterpopup\Helper\Adminhtml;

class Labels extends Generic implements TabInterface
{
    protected $_adminhtmlHelper;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @param \Magento\Backend\Block\Template\Context      $context
     * @param \Magento\Framework\Registry                  $registry
     * @param \Magento\Framework\Data\FormFactory          $formFactory
     * @param \Plumrocket\Newsletterpopup\Helper\Adminhtml $adminhtmlHelper
     * @param \Magento\Cms\Model\Wysiwyg\Config            $wysiwygConfig
     * @param \Magento\Backend\Model\UrlInterface          $backendUrl
     * @param array                                        $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Adminhtml $adminhtmlHelper,
        Config $wysiwygConfig,
        UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->_adminhtmlHelper = $adminhtmlHelper;
        $this->wysiwygConfig    = $wysiwygConfig;
        $this->backendUrl       = $backendUrl;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /** @var \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $model */
        $model = $this->_coreRegistry->registry('current_model');

        $form = $this->_formFactory->create()
            ->setHtmlIdPrefix('popup_');

        $fieldset = $form->addFieldset('general_fieldset', ['legend' => __('Texts & Labels')]);

        $fieldset->addField('text_title', 'text', [
            'name'      => 'text_title',
            'label'     => __('Title'),
            'required'  => true
        ]);

        $wysiwygConfig = $this->_loadWysiwygConfig();

        $fieldset->addField('text_description', 'editor', [
            'name'      => 'text_description',
            'label'     => __('Description'),
            'title'     => __('Description'),
            'config'    => $wysiwygConfig,
        ]);

        $fieldset->addField('text_success', 'editor', [
            'name'      => 'text_success',
            'label'     => __('Success Message'),
            'title'     => __('Success Message'),
            'config'    => $wysiwygConfig,
            'note'      => 'Use variables: {{coupon_code}} and {{coupon_expiration_date}} ' .
                'to display the “coupon code” and the “coupon expiration date” instantly in the newsletter popup.',
        ]);

        $fieldset->addField('text_submit', 'text', [
            'name'      => 'text_submit',
            'label'     => __('Submit Button'),
            'required'  => true
        ]);

        $fieldset->addField('text_cancel', 'text', [
            'name'      => 'text_cancel',
            'label'     => __('Cancel Button'),
            'required'  => true,
            'disabled'  => !$model->isModal(),
            'note'      => $this->_adminhtmlHelper->getNoteForDisabledByTypeField($model),
        ]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Texts & Labels');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Texts & Labels');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    private function _loadWysiwygConfig()
    {
        return $this->wysiwygConfig->getConfig([
            'directives_url' => $this->backendUrl->getUrl('cms/wysiwyg/directive'),
            'files_browser_window_url' => $this->backendUrl->getUrl('cms/wysiwyg_images/index'),
        ]);
    }
}
