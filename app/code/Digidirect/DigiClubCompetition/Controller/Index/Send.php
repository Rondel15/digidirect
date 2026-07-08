<?php
namespace Digidirect\DigiClubCompetition\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Mail\Template\TransportBuilder;

class Send extends Action
{
    protected $logger;
    
    protected $customerSession;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    
    ) {
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute() {
        $post = $this->getRequest()->getPostValue();
        // Get post values
        // $email = $this->getRequest()->getParam('email');
        $message = $this->getRequest()->getParam('message');
        $this->logger->info('$message: ' . $message);

        // Send Mail functionality starts from here 
        $from = $this->customerSession->getCustomer()->getEmail();
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
        $email->setFrom($from, $from);
        $email->addTo($to, $to);
        // $email->addBcc($bcc);
        $email->send();

        $this->messageManager->addSuccess(__('Form successfully submitted'));
        
        return true;
    }
}