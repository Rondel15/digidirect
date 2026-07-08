<?php
namespace Digidirect\Pronto\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\Pronto\Helper\PickupEmail;

class PickupCron
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Inventory
     */
    protected $helper;
    
    public function __construct(
            LoggerInterface $logger,
            PickupEmail $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }
    public function execute()
    {
        $test = 0;
        $this->helper->sendReadytoPickupEmail($test);
   
    }
}
