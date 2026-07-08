<?php
namespace Digidirect\Pronto\Controller\Adminhtml\ProntoInventory;

use \Digidirect\Pronto\Helper;

class Inventory
{
    /**
    * @var Curl
    */
    protected $helper;

    public function __construct(Inventory $helper)
    {
        $this->helper = $helper;
    }
    public function func()
    {
        //disable
        //$this->helper->makeACurlRequest();
        //comment to redeploy
    }
}
