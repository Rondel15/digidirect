<?php

namespace Digidirect\ParticularAudienceAPI\Block;

class TestListing extends \Magento\Framework\View\Element\Template
{
    protected $logger;
    
    protected $_registry;
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {        
        $this->logger = $logger;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }
    
    public function getCurrentCategory(){         
        return $this->_registry->registry('current_category');
    }
}