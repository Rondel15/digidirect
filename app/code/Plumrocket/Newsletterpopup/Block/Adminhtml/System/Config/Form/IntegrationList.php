<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form;

/**
 * Class IntegrationList
 */
class IntegrationList extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->addClass('newsletterpopup_list_readonly');
        $element->setDisabled('disabled');
        $element->setReadonly(true);
        $values = [];

        foreach ($element->getValues() as $item) {
            $values[] = $item['value'];
        }

        $element->setValue(implode(',', $values));

        return parent::_getElementHtml($element);
    }
}
