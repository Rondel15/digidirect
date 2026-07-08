<?php
namespace Digidirect\Utilities\Plugin\Magento\Config\Block\System\Config;

use Magento\Config\Model\Config\Structure;
use Digidirect\Utilities\Helper\Data as Helper;

class Form
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Form constructor.
     * @param Structure $configStructure
     * @param Helper $helper
     */
    public function __construct(
        Structure $configStructure,
        Helper $helper
    ) {
        $this->_configStructure = $configStructure;
        $this->_helper = $helper;
    }

    /**
     * @param string $sectionCode
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getExtVersionBySectionCode($sectionCode)
    {
        $element = $this->_configStructure->getElement($sectionCode);
        if ($element->getAttribute('tab') == 'digidirect') {
            $resource = $element->getAttribute('resource');
            list($moduleName) = explode('::', $resource);
            return $this->_helper->getModuleVersion($moduleName);
        }
        return false;
    }

    /**
     * @param \Magento\Config\Block\System\Config\Form $subject
     * @param string $result
     * @return string
     */
    public function afterGetFormHtml(
        \Magento\Config\Block\System\Config\Form $subject,
        $result
    ) {
        $versionBlock = '';
        if ($subject->getSectionCode()) {
            $version = $this->getExtVersionBySectionCode($subject->getSectionCode());
            if ($version) {
                $versionBlock = '<div class="page-actions _fixed">' . __('Extension Version ') . $version . '</div>';
            }
        }
        return $versionBlock . $result;
    }
}
