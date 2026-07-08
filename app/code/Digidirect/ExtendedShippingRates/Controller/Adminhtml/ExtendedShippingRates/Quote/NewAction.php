<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote;

class NewAction extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Quote
{
    /**
     * New shipping rule action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
