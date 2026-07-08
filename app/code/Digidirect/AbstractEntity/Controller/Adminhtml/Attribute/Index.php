<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Attribute;

use Digidirect\AbstractEntity\Controller\Adminhtml\Attribute as AttributeController;

class Index extends AttributeController
{
    /**
     * Attributes grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Abstract Entity Attributes'));
        $this->_view->renderLayout();
    }
}
