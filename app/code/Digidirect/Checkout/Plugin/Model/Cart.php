<?php

namespace Digidirect\Checkout\Plugin\Model;

class Cart
{
    protected $checkoutSession;
    
    protected $logger;
    
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface
    ){
        $this->logger = $loggerInterface;
    }
    
    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        //$this->logger->info('beforeAddProduct()');
        $cartQuantity = $subject->getQuote()->getItemsQty();
        //$this->logger->info('$cartQuantity: ' . $cartQuantity);
        /*foreach ($items as $_item) {
            $this->logger->info(json_encode( $_item->getData()));
            $productList  = $this->productRepository->getById($_item->getProductId(), false, $storeId, true);
            $productTax[] = trim($productList->getTaxClassificationKey());
        }*/
        
        if ($cartQuantity >= 10) { 
            //$this->logger->info('Cart quantity limit reached!');
            throw new \Magento\Framework\Exception\LocalizedException(__('Cart quantity limit reached!'));
            return $this;
        }
        
        return array(
            $productInfo,
            $requestInfo 
        );
    }
}