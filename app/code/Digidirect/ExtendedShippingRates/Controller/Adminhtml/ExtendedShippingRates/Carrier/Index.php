<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier;

use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier;

class Index extends Carrier
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Carriers'));
        $this->_view->renderLayout('root');
    }
}
