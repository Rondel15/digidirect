<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\TestPronto;

class MagentoPronto extends \Magento\Framework\App\Action\Action
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
            if(isset($_GET["orderId"])){
                $orderId = $_GET["orderId"];
            }
            if(isset($_GET["pronto"])){
                $pronto = $_GET["pronto"];
            }
            echo "Pronto Order <br />";
            $this->helper->populateMagento($orderId, $pronto) ;
            exit;
	}
}