<?php

namespace Digidirect\Utilities\Plugin\Magento\Config\Block\System\Config\Fieldset\Modules;

use Digidirect\Utilities\Helper\Data as Helper;
use Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Fieldset\Modules\DisableOutput;

/**
 * Class DisableOutputPlugin
 * @package Digidirect\Utilities\Block\Adminhtml\System\Config\Form\Fieldset\Modules
 */
class DisableOutputPlugin
{

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param DisableOutput $subject
     * @param string $moduleName
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $typeField
     * @param array $fieldAttr
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRenderField(
        DisableOutput $subject,
        $moduleName,
        $fieldset,
        $typeField,
        array $fieldAttr = []
    ) {
        $fieldAttr['label'] = $moduleName . $this->_getModuleVersion($moduleName);
        return [
            $moduleName,
            $fieldset,
            $typeField,
            $fieldAttr
        ];
    }

    /**
     * Get Digidirect_module name
     *
     * @param string $moduleName
     * @return string
     */
    protected function _getModuleVersion($moduleName)
    {
        $moduleVersion = '';
        if (strpos($moduleName, Helper::DIGIDIRECT_EXTENSION_PREFIX) !== false) {
            $version = $this->_helper->getModuleVersion($moduleName);
            if ($version) {
                $moduleVersion = ' (v' . $version . ')';
            }
        }
        return $moduleVersion;
    }
}
