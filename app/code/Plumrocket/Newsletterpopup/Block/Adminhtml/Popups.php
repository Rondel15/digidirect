<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Popups extends Container
{
    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_popups';
        $this->_blockGroup = 'Plumrocket_Newsletterpopup';
        $this->_headerText = __('Manage Newsletter Popups');

        $this->updateButton('add', 'label', 'Add Popup');
    }
}
