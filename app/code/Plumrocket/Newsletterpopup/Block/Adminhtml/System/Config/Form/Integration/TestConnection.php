<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration;

use Plumrocket\Newsletterpopup\Model\Integration\ActiveCampaign;

/**
 * Class TestConnection is base frontend model for Test Connection button
 */
class TestConnection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * List of services that requiring to using contact lists
     *
     * @var array
     */
    private $serviceWithRequiredLists = [
        ActiveCampaign::INTEGRATION_ID,
    ];

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(// @codingStandardsIgnoreLine we need to extend parent method
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $serviceId = '';
        /** @var \Magento\Framework\Data\Form\Element\Fieldset $container */
        $container = $element->getData('container');

        if ($container
            && ($group = $container->getData('group'))
            && (is_array($group) && ! empty($group['id']))
        ) {
            $serviceId = (string)$group['id'];
        }

        return $this->getButtonHtml($element, $serviceId);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param $serviceId
     * @return string
     */
    private function getButtonHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element,
        $serviceId
    ) {
        $url = $this->getUrl('prnewsletterpopup/integration/' . $serviceId);
        $disabled = $this->isDisabled();
        $listsUrl = $this->isServiceWithRequiredLists($serviceId)
            ? $this->getUrl('prnewsletterpopup/integration/lists_' . $serviceId)
            : false;
        $onClick = sprintf('javascript:window.testApiConnection("%s", "%s", "%s");', $url, $serviceId, $listsUrl);
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        $button->setData([
            'id' => (string)$element->getHtmlId(),
            'label' => __('Test Connection'),
            'class'     => 'integration-test-connection',
            'onclick' => $onClick,
            'disabled' => $disabled,
        ]);

        return $button->toHtml();
    }

    /**
     * @param $serviceId
     * @return bool
     */
    public function isServiceWithRequiredLists($serviceId)
    {
        return in_array($serviceId, $this->serviceWithRequiredLists);
    }

    /**
     * Determine if button is disabled
     *
     * @return boolean
     */
    protected function isDisabled()
    {
        return false;
    }
}
