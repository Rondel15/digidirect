<?php

namespace Digidirect\Catalog\Plugin\Result;

use Magento\Framework\App\ResponseInterface;

class Page {
    
    private $context;
    
    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->context = $context;
        $this->_registry = $registry;
    }

    public function beforeRenderResult(
        \Magento\Framework\View\Result\Page $subject,
        ResponseInterface $response
    ){
        if($this->context->getRequest()->getFullActionName() == 'catalog_product_view'){
           
            $product = $this->_registry->registry('current_product');
            $categories = $product->getCategoryIds();
            
            $digiprint = 2881;
            
            if (in_array($digiprint, $categories)) {
                $subject->getConfig()->addBodyClass('digiprint-product');
            }
            
        }
        return [$response];
    }
    
}