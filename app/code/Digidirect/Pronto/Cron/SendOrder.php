<?php
namespace Digidirect\Pronto\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\Pronto\Helper\Order;

class SendOrder
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Order
     */
    protected $helper;

    public function __construct(
            LoggerInterface $logger,
            Order $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    public function execute()
    {
        //$this->helper->orderPost();
        exit;

    }

    public function sendOrder()
    {
        exit;
        $this->helper->orderPost();

    }

    public function sendProcessingOrder()
    {
        exit;
        $this->helper->orderProcessing();

    }
    //redeploy
}
