<?php
namespace Digidirect\Pronto\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\Pronto\Helper\ProntoOrder;

class RetailStoreProntoOrders
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
        ProntoOrder $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }
    public function execute()
    {
        $status = 80;
        $this->helper->GetProntoOrders($status);

    }
}
