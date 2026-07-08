<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Action extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $html = sprintf(
            '<a href="%s"><span>%s</span></a>',
            $this->getUrl('*/*/edit', ['id' => $row->getId()]),
            __('Edit')
        );

        if ($row->isBase()) {
            $html .= sprintf(
                '<div><img src="%s" title="%s" style="height: 18px;" /></div>',
                $this->getViewFileUrl('Plumrocket_Newsletterpopup::images/lock.png'),
                __('This theme is one of the default Newsletter popup themes. It cannot be edited or deleted. Instead, you can duplicate it and then edit.')
            );
        }

        return $html;
    }
}
