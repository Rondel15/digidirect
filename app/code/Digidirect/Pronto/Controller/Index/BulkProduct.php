<?php
namespace Digidirect\Pronto\Controller\Index;

//use Digidirect\Pronto\Helper\Inventory;
use Digidirect\Pronto\Helper\Product;

class BulkProduct extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
                Product $helper)
	{
                $this->helper = $helper;
		return parent::__construct($context);
	}

	public function execute()
	{
            if(isset($_GET["start"])){
                $startItem = $_GET["start"];
            }
            if(isset($_GET["end"])){
                $endItem = $_GET["end"];
            }
            echo "Pronto Product <br />";
            $this->helper->productProntoBulk($startItem,$endItem);
            exit;
	}
}