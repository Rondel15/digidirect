<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\Attribute\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize edit tabs
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('digidirect_abstractentity_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Information'));
    }
}
