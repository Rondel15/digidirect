<?php

namespace Digidirect\DigiDeals\Controller\Test;

class Log extends \Magento\Framework\App\Action\Action
{
    
    protected $logger;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->logger = $logger;
        return parent::__construct($context);
    }

    public function execute()
    {
        //$this->logger->info("Test controller is working!"); 
    }
    
}