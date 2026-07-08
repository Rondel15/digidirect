<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\Order;

class SendOrderByDay
{

    /**
     * @var Order
     */
    protected $helper;

    public function __construct(
            Order $helper)
    {
        $this->helper = $helper;
    }

    public function execute()
    {
        $this->helper->orderPostByDay();
    }

}
