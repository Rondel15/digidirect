<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\ProductEntHelper;

class WiserData
{


    /**
     * @var Inventory
     */
    protected $helper;

    public function __construct(
        ProductEntHelper $helper)
    {
        $this->helper = $helper;
    }
    public function execute()
    {
        exit;
        $this->helper->productData();
    }

    public function customFinalP()
    {
        exit;
        $this->helper->customFinalPrice();
    }


}
