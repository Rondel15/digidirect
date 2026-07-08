<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Attribute;

use Digidirect\AbstractEntity\Controller\Adminhtml\Attribute as AttributeController;

class NewAction extends AttributeController
{
    /**
     * Create new attribute action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->addActionLayoutHandles();
        $this->_forward('edit');
    }
}
