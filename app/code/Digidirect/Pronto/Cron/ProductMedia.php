<?php
namespace Digidirect\Pronto\Cron;

use Digidirect\Pronto\Helper\ProductEntHelper;

class ProductMedia
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
        $this->helper->getProductImage();

    }

    public function wiserdata()
    {
        $this->helper->productData();
    }
}
