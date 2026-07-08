<?php

namespace Digidirect\ReadytoPickup\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\ReadytoPickup\Helper\ReadytoPickup;

class SendReadytoPickupEmail {
    
    protected $logger;
    
    protected $helper;
    
    public function __construct (
        LoggerInterface $logger,
        ReadytoPickup $helper
    ){
        $this->logger = $logger;
        $this->helper = $helper;
    }

    public function execute() {
        //$this->logger->info("send_readytopickup_email");
        $this->helper->sendReadytoPickupEmail();
    }
    
}