<?php

namespace Digidirect\Catalog\Observer;

class RedirectDisabled implements \Magento\Framework\Event\ObserverInterface
{
    protected $registry;

    protected $redirect;
    
    protected $urlInterface;
    
    protected $storeManager;
    
    protected $categoryRepository;
    
    protected $response;
    
    protected $logger;
    
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\App\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->registry = $registry;
        $this->redirect = $redirect;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->response = $response;
        $this->logger = $logger;
    }


    public function execute(\Magento\Framework\Event\Observer $observer){
        
        $product = $this->registry->registry('current_product');
        $controller = $observer->getControllerAction();
        
        if (!$product){
          return $this;
        } else {
            if(!$product->getStatus()){
               
                $categories = $product->getCategoryIds();
                
                /*foreach ($categories as $categoryId) {
                    $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
                }*/
                $category = $this->categoryRepository->get($categories[0], $this->storeManager->getStore()->getId());
                /*$this->redirect->redirect($controller->getResponse(), $category->getUrl());*/
                
                $this->logger->info("category->getUrl(), " . $category->getUrl());
                $this->response->setRedirect($category->getUrl(), 301)->sendResponse();
            }
        }
    }
}