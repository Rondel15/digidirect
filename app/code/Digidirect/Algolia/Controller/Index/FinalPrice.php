<?php

namespace Digidirect\Algolia\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class FinalPrice extends Action implements HttpPostActionInterface {
    
    protected $_resultJsonFactory;

    protected $logger;

    protected $jsonSerializer;
    
    protected $_productRepository;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }
    

    public function execute() {
        
        $result = $this->_resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');
        
        if ($id) {
            $product = $this->_productRepository->getById($id);
            $sku = $product->getData('sku');
            $finalPrice = $product->getFinalPrice();
            $wiserPrice = $product->getData('wiser_price');
            $basePrice = $product->getPrice();
            $discountWiserPrice = round($basePrice - $wiserPrice, 2);
            
            //$this->logger->info('$finalPrice: ' . $finalPrice);
            //$this->logger->info('$wiserPrice: ' . $wiserPrice);
            //$this->logger->info('$basePrice: ' . $basePrice);
            //$this->logger->info('$discountWiserPrice: ' . $discountWiserPrice);
            
            if ($wiserPrice == 0 || empty($wiserPrice) || $discountWiserPrice < 10) {
                $this->logger->info('1st $id: ' . $id . ', ' . '$sku: ' . $sku . ', ' . '$finalPrice: ' . $finalPrice . ', ' . '$wiserPrice: ' . $wiserPrice . ', ' . '$basePrice: ' . $basePrice . ', ' . '$discountWiserPrice: ' . $discountWiserPrice);
                $result->setData($finalPrice);
            } else {
                if ($wiserPrice < $finalPrice) {
                    $this->logger->info('2nd $id: ' . $id . ', ' . '$sku: ' . $sku . ', ' . '$finalPrice: ' . $finalPrice . ', ' . '$wiserPrice: ' . $wiserPrice . ', ' . '$basePrice: ' . $basePrice . ', ' . '$discountWiserPrice: ' . $discountWiserPrice);
                    $result->setData($wiserPrice);
                } else {
                    $this->logger->info('3rd $id: ' . $id . ', ' . '$sku: ' . $sku . ', ' . '$finalPrice: ' . $finalPrice . ', ' . '$wiserPrice: ' . $wiserPrice . ', ' . '$basePrice: ' . $basePrice . ', ' . '$discountWiserPrice: ' . $discountWiserPrice);
                    $result->setData($finalPrice);
                }
            }
            return $result;
        }
        
    }

}
