<?php

namespace Digidirect\DigiSecondsForm\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Mail\Template\TransportBuilder;


class Index extends Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransportBuilder $transportBuilder
        
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
    }   
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        
        // Get post values
        $firstname = $this->getRequest()->getParam('firstname');
        $lastname = $this->getRequest()->getParam('lastname');
        $phone = $this->getRequest()->getParam('phone');
        $email = $this->getRequest()->getParam('email');
        $brands = $this->getRequest()->getParam('brands');
        $productName = $this->getRequest()->getParam('productName');
        $purchaseYear = $this->getRequest()->getParam('purchaseYear');
        $notes = $this->getRequest()->getParam('notes');
        $askingPrice = $this->getRequest()->getParam('askingPrice');

        // Send Mail functionality starts from here 
        $from = $email;
        $nameFrom = $firstname." ".$lastname;
        $to = array("geoff.n@digidirect.com.au","paul@digidirect.com.au","dev4@digidirect.com.au");
        $bcc = "orders@kayweb.com.au";
        $nameTo = "Digidirect";
        $body = "
        <div>
            <p>FullName: ".$firstname." ".$lastname."</p>
            <p>Phone: ".$phone."</p>
            <p>Email: ".$email."</p>
            <p>Brands: ".$brands."</p>
            <p>Product Name: ".$productName."</p>
            <p>Purchase Year: ".$purchaseYear."</p>
            <p>Notes: ".$notes."</p>
            <p>Asking Price: ".$askingPrice."</p>
        </div>";

        $email = new \Zend_Mail();
        $email->setSubject("DigiSeconds Form"); 
        $email->setBodyHtml($body);     // use it to send html data
        //$email->setBodyText($body);   // use it to send simple text data
        $email->setFrom($from, $nameFrom);
        $email->addTo($to, $nameTo);
        $email->addBcc($bcc);
        $email->send();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();       
        $data = $objectManager->create('Digidirect\DigiSecondsForm\Model\DigiSecondsForm');
        $data->setData($post);
        $data->save();
//        echo "success";
        /* echo "hello";
        exit; */
        
        $this->messageManager->addSuccess(__('Form successfully submitted'));
             
    }
}
