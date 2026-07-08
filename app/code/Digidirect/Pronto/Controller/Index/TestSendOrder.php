<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\TestPronto;

class TestSendOrder extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
                TestPronto $helper)
	{
                $this->helper = $helper;
		return parent::__construct($context);
	}

	public function execute()
	{
            $orderId = 0;
            $date = 0;
            $page = 1;
            $size = 1;
            $test = 1;
            if(isset($_GET["order"])){
                $orderId = $_GET["order"];
            }
            if(isset($_GET["date"])){
                $date = $_GET["date"];
            }
            if(isset($_GET["page"])){
                $page = $_GET["page"];
            }
            if(isset($_GET["size"])){
                $size = $_GET["size"];
            }
            if(isset($_GET["test"])){
                $test = $_GET["test"];
            }
            echo "Pronto Send Order - ".$orderId." - ".$date."- ".$size."- ".$page.": test - ".$test."<br />";
            $this->helper->orderPostTec($orderId, $date, $size, $page, $test);
	}
}
