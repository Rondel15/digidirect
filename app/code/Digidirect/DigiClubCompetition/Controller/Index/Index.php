<?php
namespace Digidirect\DigiClubCompetition\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Mail\Template\TransportBuilder;

class Index extends Action
{
    protected $_resultPageFactory;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->transportBuilder = $transportBuilder;
    }

    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set("Competition");
        return $resultPage;


        $post = $this->getRequest()->getPostValue();
        
        // Get post values
        // $email = $this->getRequest()->getParam('email');
        $message = $this->getRequest()->getParam('message');

        // Send Mail functionality starts from here 
        $from = "jirehcapao@gmail.com";
        // $from = $email;
        $to = array("rondel.d@digidirect.com.au","jireh.c@digidirect.com.au","community@digidirect.com.au");
        // $bcc = "orders@kayweb.com.au";
        $nameTo = "Digidirect";
        $body = "
        <div>
            <p>Message: ".$message."</p>
        </div>";

        $email = new \Zend_Mail();
        $email->setSubject("digiClub Competition Form"); 
        $email->setBodyHtml($body);     // use it to send html data
        //$email->setBodyText($body);   // use it to send simple text data
        $email->setFrom($from, $nameFrom);
        $email->addTo($to, $nameTo);
        // $email->addBcc($bcc);
        $email->send();

        $this->messageManager->addSuccess(__('Form successfully submitted'));
    }
}