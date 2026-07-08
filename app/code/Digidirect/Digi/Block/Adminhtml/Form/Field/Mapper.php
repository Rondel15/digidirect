<?php

namespace Digidirect\Digi\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Mapper
 * @package Digidirect\Digi\Block\Adminhtml\Form\Field
 */
class Mapper extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('request_path', ['label' => __('Request path'), 'class' => 'required-entry']);
        $this->addColumn('canonical_link', ['label' => __('Canonical Path'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}