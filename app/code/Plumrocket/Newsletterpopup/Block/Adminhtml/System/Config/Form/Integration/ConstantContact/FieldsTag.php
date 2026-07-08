<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration\ConstantContact;

use Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag\InputTable;

/**
 * Class FieldsTag
 */
class FieldsTag extends \Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\FieldsTag
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var InputTable $inputTableBlock */
        $inputTableBlock = $this->getLayout()->createBlock(InputTable::class);

        $inputTableBlock->setContainerFieldId($element->getName())
            ->setRowKey('name')
            ->addColumn(
                'orig_label',
                [
                    'header'    => __('Newsletter Popup Field'),
                    'index'     => 'orig_label',
                    'type'      => 'label',
                    'width'     => '36%',
                    'class'     => 'test',
                ]
            )->addColumn(
                'label',
                [
                    'header'    => __('Integration Field ID'),
                    'index'     => 'label',
                    'type'      => 'input',
                    'width'     => '28%',
                ]
            )->setArray($this->getPreparedValue($element->getValue()));

        return $inputTableBlock->toHtml();
    }
}
