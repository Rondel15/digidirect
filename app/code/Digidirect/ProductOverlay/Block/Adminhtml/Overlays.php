<?php

namespace Digidirect\ProductOverlay\Block\Adminhtml;

/**
 * Class Overlays
 * @package Digidirect\ProductOverlay\Block\Adminhtml
 */
class Overlays extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_overlays';
        $this->_blockGroup = 'Digidirect_ProductOverlay';
        $this->_headerText = __('Product Overlays');
        parent::_construct();

        if ($this->_isAllowedAction('Digidirect_ProductOverlay::save')) {
            $this->buttonList->update('add', 'label', __('Add New overlay'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
