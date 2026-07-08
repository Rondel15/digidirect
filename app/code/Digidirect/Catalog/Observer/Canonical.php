<?php

namespace Digidirect\Catalog\Observer;

class Canonical implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    protected $_urlInterFace;
    
    protected $redirect;
    
    protected $logger;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $_urlInterFace,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Psr\Log\LoggerInterface $logger  
    ) {
        $this->request = $request;
        $this->_urlInterFace = $_urlInterFace;
        $this->redirect = $redirect;
        $this->_categoryHelper = $categoryHelper;
        $this->_coreRegistry = $registry;
        $this->logger = $logger;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $url = $this->_urlInterFace->getCurrentUrl();
        
        $category = $this->_coreRegistry->registry('current_category');
        
        if ($category && $category->getId()) {
            $this->logger->info('catalog/category/view: ' . $category->getUrl());
        }
    }
}
