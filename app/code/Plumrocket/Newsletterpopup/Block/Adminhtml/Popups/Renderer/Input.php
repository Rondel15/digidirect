<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer;

use Magento\Framework\DataObject;

class Input extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * Renders grid column
     *
     * @param   DataObject $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        if ('agreement' == $row->getName()) {
            return $this->renderTextarea($row);
        }

        return parent::render($row);
    }

    /**
     * Renders textarea
     *
     * @param   DataObject $row
     * @return  string
     */
    public function renderTextarea(DataObject $row)
    {
        $html = '<textarea ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'rows="3" ';
        $html .= 'class="input-text input-text-textarea ' . $this->getColumn()->getInlineCss() . '">';
        $html .= $this->escapeHtml($row->getData($this->getColumn()->getIndex()));
        $html .= '</textarea>';
        return $html;
    }
}
