<?php
namespace Digidirect\Sales\Model\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    protected function prepareTemplate(Order $order)
    {
        //Get Payment Method
        $paymentMethod = $order->getPayment()->getMethod();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $shippingAddress = $cart->getQuote()->getShippingAddress();
        $shippingMethod = $shippingAddress->getShippingMethod();
        
        parent::prepareTemplate($order);

        if ($shippingMethod == 'collect_collect') {
            $this->templateContainer->setTemplateId(69);
        }

    }
}