<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Popups\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Rate extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        if ($row->getViewsCount()) {
            $percent = ($row->getSubscribersCount() / $row->getViewsCount()) * 100;
            return (string)number_format((float)$percent, 2, '.', '') . '%';
        }
        return '0%';
    }
}
