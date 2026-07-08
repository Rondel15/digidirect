<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate;

use Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate;

class Index extends Rate
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Rates'));
        $this->_view->renderLayout('root');
    }
}
