<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method;

use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Method;

class Index extends Method
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Methods'));
        $this->_view->renderLayout('root');
    }
}
