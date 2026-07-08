<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\Sftpfilesender;

class SendFile extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Sftpfilesender $helper)
    {
        $this->helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {


        $this->helper->sendFile();
    }
}
