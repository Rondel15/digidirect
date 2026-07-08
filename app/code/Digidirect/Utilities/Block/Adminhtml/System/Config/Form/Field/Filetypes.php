<?php

namespace Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Field;

use Digidirect\Utilities\Helper\WysiwygAllowedTypeSettings;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Filetypes
 * @package Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Field
 */
class Filetypes extends AbstractFieldArray
{
    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            WysiwygAllowedTypeSettings::ALLOWED_FILE_TYPES_KEY_EXTENSION,
            [
                'label' => __('File type'),
            ]
        );
        $this->addColumn(
            WysiwygAllowedTypeSettings::ALLOWED_FILE_TYPES_KEY_MIME,
            [
                'label' => __('Mime-type'),
            ]
        );
        $this->_addAfter = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $row->addData(array_merge($this->getRowSkeleton(), $row->getData()));
    }

    /**
     * @return array
     */
    protected function getRowSkeleton()
    {
        return [
            WysiwygAllowedTypeSettings::ALLOWED_FILE_TYPES_KEY_EXTENSION => '',
            WysiwygAllowedTypeSettings::ALLOWED_FILE_TYPES_KEY_MIME => '',
        ];
    }
}
