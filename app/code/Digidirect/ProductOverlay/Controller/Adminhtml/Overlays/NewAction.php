<?php

namespace Digidirect\ProductOverlay\Controller\Adminhtml\Overlays;

/**
 * Class NewAction
 * @package Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
 */
class NewAction extends \Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
{
    /**
     * Index Action.
     * Forward to Edit Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
