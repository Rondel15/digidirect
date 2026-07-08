<?php

namespace Digidirect\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeAddToCart implements ObserverInterface
{
    protected $logger;

    protected $checkoutSession;
    
    protected $_messageManager;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->logger = $logger;
        $this->_messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $product = $observer->getRequest()->getParam('product');
        $qty = $observer->getRequest()->getParam('qty');
        //$this->logger->info('qty: ' . $observer->getRequest()->getParam('qty'));
        
        foreach($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            if ($item->getData('product_id') == $product) {
                $this->logger->info($item->getData('product_id') . ', ' . $product);
                $item->setData('qty', $item->getQty() + $qty);
                //$item->setQty(1);
                $item->save();
            }
        }
        
        $productExist = $this->checkoutSession->getQuote()->hasProductId($product);
        
        if ($productExist) {
            //$this->_messageManager->addError(__('your custom message'));
            //set false if you not want to add product to cart
            $observer->getRequest()->setParam('product', false);
            return $this;
        }
 
        return $this;
        
    }  
}
