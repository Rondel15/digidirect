<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\History\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Name extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        if ($row->getCustomerId()) {
            $res = $row->getCid()? $row->getCustomerName(): 'DELETED';
        } else {
            $res = 'Guest';
        }
        return $res;
    }
}
