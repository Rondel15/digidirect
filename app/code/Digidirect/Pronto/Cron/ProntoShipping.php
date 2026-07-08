<?php
namespace Digidirect\Pronto\Cron;

use Psr\Log\LoggerInterface;
use Digidirect\Pronto\Helper\Shipping;

class ProntoShipping
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
            Shipping $helper)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    public function execute()
    {
        $this->helper->getShipping();

    }

}
