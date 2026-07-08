<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates;

class Quote extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'extendedshippingrates_quote';
        $this->_headerText = __('Digidirect Shipping Rules');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
