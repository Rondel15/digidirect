<?php

namespace Digidirect\ParticularAudienceAPI\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory; 

class Product extends Action {
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    
    protected $_productRepository;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }   

    public function execute() {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $product = $this->_productRepository->getById($id);
            $result->setData($product);
            return $result;
        }
    }

}
