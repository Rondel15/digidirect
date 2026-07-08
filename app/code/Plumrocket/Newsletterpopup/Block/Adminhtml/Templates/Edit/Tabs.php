<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\Templates\Edit;

use Magento\Backend\Block\Widget\Tabs as TabsWidget;

class Tabs extends TabsWidget
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Edit Theme'));
    }
}
