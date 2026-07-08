<?php
namespace Digidirect\Pronto\Controller\Index;

//use Digidirect\Pronto\Helper\Inventory;
use Digidirect\Pronto\Helper\Product;

class TestProductEnquiry extends \Magento\Framework\App\Action\Action
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
        if (isset($_GET["p"])) {
            $startItem = $_GET["p"];
        }
        $counter = 10;

        if (isset($_GET["counter"])) {
            $counter = $_GET["counter"];
        }

        echo "Pronto Product <br />";
        $this->helper->productTestProntoSet($startItem, $counter);
        //redeploy
    }
}
