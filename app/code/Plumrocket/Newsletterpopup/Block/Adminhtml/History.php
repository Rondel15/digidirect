<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class History extends Container
{
    protected function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_history';
        $this->_blockGroup = 'Plumrocket_Newsletterpopup';
        $this->_headerText = __('Newsletter Popup History');

        $this->removeButton('add');
    }

    /*protected function _prepareLayout()
       {
        $this->setChild('grid',
            $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
            $this->_controller . '.grid')->setSaveParametersInSession(true) );
        return parent::_prepareLayout();
       }*/
}
