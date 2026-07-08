<?php

namespace Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Fieldset\Modules;

/**
 * Class DisableOutput
 * @package Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Fieldset\Modules
 */
class DisableOutput extends \Magento\Config\Block\System\Config\Form\Fieldset\Modules\DisableOutput
{

    /**
     * @param string $moduleName
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $typeField
     * @param array $fieldAttr
     * @return \Magento\Framework\Data\Form\Element\Select
     */
    public function renderField($moduleName, $fieldset, $typeField, array $fieldAttr = [])
    {
        return $fieldset->addField(
            $moduleName,
            $typeField,
            $fieldAttr
        )->setRenderer(
            $this->_getFieldRenderer()
        );
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $moduleName
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'advanced/modules_disable_output/' . $moduleName;
        //TODO: move as property of form
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = (int)(string)$this->getForm()->getConfigValue($path);
            $inherit = true;
        }

        $element = $this->_getDummyElement();

        $field = $this->renderField(
            $moduleName,
            $fieldset,
            'select',
            [
                'name' => 'groups[modules_disable_output][fields][' . $moduleName . '][value]',
                'label' => $moduleName,
                'value' => $data,
                'values' => $this->_getValues(),
                'inherit' => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($element),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($element)
            ]
        );
        return $field->toHtml();
    }
}
