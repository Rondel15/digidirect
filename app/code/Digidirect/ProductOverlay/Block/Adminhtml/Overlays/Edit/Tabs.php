<?php

namespace Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit;

/**
 * Class Tabs
 * @package Digidirect\ProductOverlay\Block\Adminhtml\Overlays\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('digidirect_productoverlay_overlays_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Overlay Options'));
    }
}
