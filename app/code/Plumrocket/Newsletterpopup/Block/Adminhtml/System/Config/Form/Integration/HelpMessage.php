<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration;

class HelpMessage extends \Magento\Config\Block\System\Config\Form\Field
{
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

        return $element->getElementHtml() . "<script>
            require(['Plumrocket_Newsletterpopup/js/integration/help-messages'], function (helpMessage) {
                helpMessage.init('{$serviceId}')
            });
        </script>";
    }
}
