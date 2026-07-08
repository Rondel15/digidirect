<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier;

class NewAction extends \Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Carrier
{
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
