<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Templates extends Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_templates';
        $this->_blockGroup = 'Plumrocket_Newsletterpopup';
        $this->_headerText = __('Manage Newsletter Popup Themes');
        $this->_addButtonLabel = __('Add New Theme');
        // $this->updateButton('add', 'label', 'Add New Theme');
        parent::_construct();
    }
}
