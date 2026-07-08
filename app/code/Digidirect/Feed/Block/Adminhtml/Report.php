<?php

namespace Digidirect\Feed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Report extends Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_report';
        $this->_blockGroup = 'Digidirect_Feed';
        $this->_headerText = __('Feed Reports');
        parent::_construct();
        $this->removeButton('add');
    }
}
