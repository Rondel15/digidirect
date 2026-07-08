<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\PickupEmail;

class Pickup extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        PickupEmail $helper)
	{
                $this->helper = $helper;
		return parent::__construct($context);
	}

	public function execute()
	{
            $test = 0;
            echo "pickup";
            if(isset($_GET["test"])){
                $test = $_GET["test"];
            }

            $this->helper->sendReadytoPickupEmail($test);

            
    }
}
