<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\Product;

class ProductEnquiry
{

    /**
     * @var Product
     */
    protected $helper;

    public function __construct(
            Product $helper)
    {
        $this->helper = $helper;
    }

    public function executeSync()
    {
        exit;
        $this->helper->productSync();
    }
    public function execute()
    {
        return;
        //$this->helper->productSync();
    }

}
