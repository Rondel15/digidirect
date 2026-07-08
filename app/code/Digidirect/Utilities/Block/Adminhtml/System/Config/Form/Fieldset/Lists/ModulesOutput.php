<?php

namespace Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Fieldset\Lists;

/**
 * Class ModulesOutput
 *
 * @package Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Fieldset\Modules
 */
class ModulesOutput extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Digidirect\Utilities\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * ModulesOutput constructor.
     *
     * @param \Magento\Backend\Block\Context                $context
     * @param \Magento\Backend\Model\Auth\Session           $authSession
     * @param \Magento\Framework\View\Helper\Js             $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Digidirect\Utilities\Helper\Data                  $helper
     * @param array                                         $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Digidirect\Utilities\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_moduleList = $moduleList;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $modules = $this->_moduleList->getNames();

        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $this->_eventManager->dispatch(
            'adminhtml_system_config_advanced_moduleversions_render_before',
            ['modules' => $dispatchResult]
        );
        $modules = $dispatchResult->toArray();

        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName === 'Magento_Backend') {
                continue;
            }
            $html .= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton(
                'Magento\Config\Block\System\Config\Form\Field'
            );
        }
        return $this->_fieldRenderer;
    }

    /**
     * @param string                                        $moduleName
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string                                        $typeField
     * @param array                                         $fieldAttr
     *
     * @return mixed
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
     * @param string                                        $moduleName
     *
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $field = $this->renderField(
            $moduleName,
            $fieldset,
            'label',
            [
                'name'  => 'groups[module_versions][fields][' . $moduleName . '][value]',
                'label' => $moduleName,
                'value' => 'v' . $this->_helper->getModuleVersion($moduleName),
            ]
        );
        return $field->toHtml();
    }
}
