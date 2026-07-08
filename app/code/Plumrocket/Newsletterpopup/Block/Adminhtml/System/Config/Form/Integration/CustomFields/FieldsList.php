<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration\CustomFields;

/**
 * @method string getServiceId()
 * @method $this  setServiceId($serviceId)
 */
class FieldsList extends \Magento\Backend\Block\Template
{
    /**
     * @return false|string
     */
    public function getJsLayout()
    {
        $layout = json_decode(parent::getJsLayout(), true);
        $layout['components'][$this->getComponentName()] = $this->getJsComponentConfig();
        return json_encode($layout);
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return 'pr-integration-' . $this->getServiceId() . '-custom-fields';
    }

    /**
     * @return array
     */
    private function getJsComponentConfig()
    {
        return [
            'component' => 'Plumrocket_Newsletterpopup/js/view/customFields',
            'url' => $this->getUrl('prnewsletterpopup/integration/customFields_' . $this->getServiceId()),
        ];
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml() // @codingStandardsIgnoreLine
    {
        $this->setTemplate('Plumrocket_Newsletterpopup::integration/custom_fields.phtml');
        return parent::_beforeToHtml();
    }
}
