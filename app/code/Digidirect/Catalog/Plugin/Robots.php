<?php

namespace Digidirect\Catalog\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;

class Robots 
{
    protected $pageConfig;
    
    protected $request;
    
    protected $logger;
    
    private $urlInterface;
    
    protected $_productRepository;
    
    protected $_registry;
    
    /**
     *
     * @param PageConfig $pageConfig
     * @param Http $request
     */
    public function __construct(
        PageConfig $pageConfig,
        Http $request,
        \Magento\Framework\UrlInterface $urlInterface,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry
        
    ) {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
        $this->_productRepository = $productRepository;
        $this->_registry = $registry;
    }
    /**
     * Before Render function
     *
     * @param Renderer $subject
     */
    public function beforeRenderMetadata(Renderer $subject) {
        $fullActionName = $this->request->getFullActionName();
        if ($fullActionName == 'catalog_product_view') {
            if ($this->getCurrentUrl() != $this->getCurrentProduct()->getProductUrl()) {
                $this->pageConfig->setMetadata('robots', 'NOINDEX,NOFOLLOW');
            }
        } elseif ($fullActionName == 'catalogsearch_result_index') {
            $this->pageConfig->setMetadata('robots', 'NOINDEX,NOFOLLOW');
        }
        if (str_contains($this->getCurrentUrl(), '___store')) {
            $this->pageConfig->setMetadata('robots', 'NOINDEX,NOFOLLOW');
        }
    }
    
    public function getCurrentProduct() {        
        return $this->_registry->registry('current_product');
    }    
    
    public function getCurrentUrl() {
        return $this->urlInterface->getCurrentUrl();
    }
}