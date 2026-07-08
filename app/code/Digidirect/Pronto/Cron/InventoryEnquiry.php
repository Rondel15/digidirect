<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\Inventory;

class InventoryEnquiry
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
            Inventory $helper)
    {

        $this->helper = $helper;
    }

    public function execute()
    {
        //comment to redeploy
        exit;
        $this->helper->enquireInventory();

    }
}
