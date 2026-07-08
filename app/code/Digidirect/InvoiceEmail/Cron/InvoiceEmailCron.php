<?php
namespace Digidirect\InvoiceEmail\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\InvoiceEmail\Helper\InvoiceEmail;

class InvoiceEmailCron
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
        InvoiceEmail $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }
    public function execute()
    {
        $test = 1;
        $this->helper->sendInvoiceEmail($test);
   
    }
}
