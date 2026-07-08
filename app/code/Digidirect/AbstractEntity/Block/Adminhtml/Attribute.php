<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml;

class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize rma item management page
     *
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_attribute';
        $this->_blockGroup = 'Digidirect_AbstractEntity';
        $this->_headerText = __('Abstract Entity Attribute');
        $this->_addButtonLabel = __('Add New Attribute');
        parent::_construct();
    }
}
