<?php

namespace Digidirect\ParticularAudienceAPI\Block;

class Events extends \Magento\Framework\View\Element\Template
{
    protected $logger;
    
    protected $_registry;
    
    protected $_urlInterface;
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $urlInterface, 
        array $data = []
    ) {        
        $this->logger = $logger;
        $this->_registry = $registry;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $data);
    }
    
    public function getCurrentUrl() {
        return rtrim($this->_urlInterface->getCurrentUrl(), '/');
    }
}