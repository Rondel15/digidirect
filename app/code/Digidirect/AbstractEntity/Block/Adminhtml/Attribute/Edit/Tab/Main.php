<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class Main extends AbstractMain implements TabInterface
{
    /**
     * Initialize form fileds values
     *
     * @return \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
     */
    protected function _initFormValues()
    {
        $attribute = $this->getAttributeObject();
        if ($attribute->getId() && $attribute->getValidateRules()) {
            $this->getForm()->addValues($attribute->getValidateRules());
        }
        $result = parent::_initFormValues();

        // get data using methods to apply scope
        $formValues = $this->getAttributeObject()->getData();
        foreach (array_keys($formValues) as $idx) {
            $formValues[$idx] = $this->getAttributeObject()->getDataUsingMethod($idx);
        }
        $this->getForm()->addValues($formValues);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldSet = $form->getElement('base_fieldset');
        $yesNo = $this->_yesnoFactory->create()->toOptionArray();

        $inputTypes = $this->_inputTypeFactory->create()->toOptionArray();
        $inputTypes[] = ['value' => 'image', 'label' => __('Media Image')];

        $frontEndInput = $form->getElement('frontend_input');
        $frontEndInput
            ->setData('label', __('Input Type'))
            ->setData('title', __('Input Type'))
            ->setData('values', $inputTypes);

        $fieldSet->removeField('is_unique')->addField(
            'is_unique',
            'select',
            [
                'name' => 'is_unique',
                'label' => __('Unique Value'),
                'title' => __('Unique Value (not shared with other products)'),
                'note' => __('Not shared with other options.'),
                'values' => $yesNo
            ]
        );

        $fieldSet->addField(
            'is_global',
            'select',
            [
                'name' => 'is_global',
                'label' => __('Scope'),
                'title' => __('Scope'),
                'values' => [
                    ScopedAttributeInterface::SCOPE_STORE => __('Store View'),
                    ScopedAttributeInterface::SCOPE_WEBSITE => __('Website'),
                    ScopedAttributeInterface::SCOPE_GLOBAL => __('Global'),
                ],
                'note' => __('Declare attribute value saving scope.'),
            ]
        );

        $fieldSet->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'note' => __('Attribute\'s position.'),
            ]
        );

        $fieldSet->addField(
            'note',
            'text',
            [
                'name' => 'note',
                'label' => __('Note'),
                'title' => __('Note'),
                'note' => __('Attribute\'s description.'),
            ]
        );

        $fieldSet->addField(
            'is_html_allowed_on_front',
            'select',
            [
                'name' => 'is_html_allowed_on_front',
                'label' => __('Is HTML Allowed on Front'),
                'title' => __('Is HTML Allowed on Front'),
                'values' => $yesNo,
            ]
        );

        $fieldSet->addField(
            'is_wysiwyg_enabled',
            'select',
            [
                'name' => 'is_wysiwyg_enabled',
                'label' => __('Is WYSIWYG Enabled'),
                'title' => __('Is WYSIWYG Enabled'),
                'values' => $yesNo,
            ]
        );

        $fieldSet->addField(
            'is_used_in_grid',
            'select',
            [
                'name' => 'is_used_in_grid',
                'label' => __('Add to Column Options'),
                'title' => __('Add to Column Options'),
                'values' => $yesNo,
                'note' => __('Select "Yes" to add this attribute to the list of column options in the grid.'),
            ]
        );

        $fieldSet->addField(
            'is_filterable_in_grid',
            'select',
            [
                'name' => 'is_filterable_in_grid',
                'label' => __('Use in Filter Options'),
                'title' => __('Use in Filter Options'),
                'values' => $yesNo,
                'note' => __('Select "Yes" to add this attribute to the list of filter options in the grid.'),
            ]
        );

        $fieldSet->addField(
            'use_in_index_table',
            'select',
            [
                'name' => 'use_in_index_table',
                'label' => __('Use in Index Table'),
                'title' => __('Use in Index Table'),
                'values' => $yesNo,
            ]
        );

        $fieldSet->addField(
            'source_model',
            'text',
            [
                'name' => 'source_model',
                'label' => __('Source Model'),
                'title' => __('Source Model'),
                'note' => __('Use "Digidirect\AbstractEntity\Model\Source\AbstractEntity" model for using Source Entity Type'),
            ]
        );

        $fieldSet->addField(
            'source_entity_type',
            'text',
            [
                'name' => 'source_entity_type',
                'label' => __('Source Entity Type'),
                'title' => __('Source Entity Type'),
                'note' => __('Abstract Entity Name for Dropdown Options.'),
            ]
        );

        if ($attributeObject->getId()) {
            $form->getElement('is_global')->setDisabled(1);
            $form->getElement('source_model')->setDisabled(1);
            $form->getElement('source_entity_type')->setDisabled(1);
        }

        return $this;
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Properties');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Properties');
    }

    /**
     * Can show tab in tabs
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
