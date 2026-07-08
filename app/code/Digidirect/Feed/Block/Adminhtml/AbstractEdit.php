<?php

namespace Digidirect\Feed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Container;

abstract class AbstractEdit extends Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Digidirect_Feed';

    /**
     * @return $this
     */
    protected function _replaceSaveButtonWithSaveSplitButton()
    {
        $this->_removeSaveButton();
        $this->_addSaveSplitButton();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _removeSaveButton()
    {
        $this->buttonList->remove('save');
        return $this;
    }

    /**
     * @return $this
     */
    protected function _addSaveSplitButton()
    {
        $this->getToolbar()->addChild(
            'save-split-button',
            'Magento\Backend\Block\Widget\Button\SplitButton',
            [
                'id' => 'save-split-button',
                'label' => __('Save'),
                'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'button_class' => 'widget-button-update',
                'options' => [
                    [
                        'id' => 'save-button',
                        'label' => __('Save'),
                        'default' => true,
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                    'event' => 'saveAndContinueEdit',
                                    'target' => '#edit_form'
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'save-continue-button',
                        'label' => __('Save & Close'),
                        'data_attribute' => [
                            'mage-init' => [
                                'button' => [
                                    'event' => 'save',
                                    'target' => '#edit_form'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        return $this;
    }
}
