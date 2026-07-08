<?php

namespace Digidirect\Feed\Block\Adminhtml\Rule\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Conditions extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $html = '<small>' . $row->toString() . '</small>';

        return $html;
    }
}
