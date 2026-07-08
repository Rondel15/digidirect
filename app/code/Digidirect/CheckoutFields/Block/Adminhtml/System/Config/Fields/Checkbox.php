<?php

namespace Digidirect\CheckoutFields\Block\Adminhtml\System\Config\Fields;

use \Magento\Framework\View\Element\AbstractBlock;

/**
 * Class Checkbox
 * @package Digidirect\CheckoutFields\Block\Adminhtml\System\Config\Fields
 */
class Checkbox extends AbstractBlock
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $elId = $this->getInputId();
        $elName = $this->getInputName();
        $column = $this->getColumn();

        return '<input type="checkbox" id="' . $elId . '"' .
        ' name="' . $elName . '"' .
        ($column['size'] ? 'size="' . $column['size'] . '"' : '') .
        ' class="' .
        (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
        (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }
}
