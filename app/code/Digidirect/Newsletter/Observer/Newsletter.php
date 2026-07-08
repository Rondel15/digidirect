<?php
namespace Digidirect\Newsletter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\Helper\AbstractHelper;

class Newsletter extends AbstractHelper implements \Magento\Framework\Event\ObserverInterface
{
    protected $customerRepository;
    
    protected $customerSession;
    
    protected $curl;
    
    protected $jsonSerializer;
    
    protected $logger;
    
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Psr\Log\LoggerInterface $logger
    ) 
    {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }
    
    public function execute(Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $email = $subscriber->getEmail();
        $subscriberStatus = $subscriber->getSubscriberStatus();
        // subscriberStatus = 1 subscribe
        // subscriberStatus = 3 unsubscribed
        $customerID = $subscriber->getCustomerId();
        
        if ($customerID) {
            $customer = $this->customerRepository->getById($customerID);
            if ( $subscriberStatus == '1') {
                $customer->setCustomAttribute('marketing_consent', 1);
                $this->customerRepository->save($customer);
            } else {
                $customer->setCustomAttribute('marketing_consent', 0);
                $this->customerRepository->save($customer);
            }
        }
        //$this->logger->info("customerID: " . $customerID); 
        
        $getTokenUrl = 'https://digidirect2022.my.salesforce.com/services/oauth2/token';
        $getTokenParams = ["grant_type"=>"password","username"=>"sfdc.connect@digidirect.com.au","password"=>"idv5EdQ3cNYG1zuF3pje!inXRgbsxaaQRzbjWCnllpWZ0z","client_id"=>"3MVG9wt4IL4O5wvKHkw4LwXtVE2s.EYz9zxXLdFQ_F5LhhQQ9dRSWJEvkcyWje6OFpVm3qOLjsWVBjJVUy26z","client_secret"=>"CEEF6DD5884CF7C8DA8089015A1438F089B9B729A2DA0CEC9F63E1003B63D9B9"];
        
        $this->curl->addHeader("Content-Type", "application/x-www-form-urlencoded");
        $this->curl->post($getTokenUrl, $getTokenParams);

        $getTokenResult = $this->curl->getBody();

        $getTokenJson = $this->jsonSerializer->unserialize($getTokenResult);
        
        //$this->logger->info("getTokenJson['access_token']: " . $getTokenJson['access_token']); 
        
        $webSignUpUrl = 'https://digidirect2022.my.salesforce.com/services/apexrest/WebSignup';
        $webSignUpParams = json_encode(["email"=>$email,"source"=>"Web"]);
        
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $getTokenJson['access_token']);
        $this->curl->post($webSignUpUrl, $webSignUpParams);
        
        $webSignUpResult = $this->curl->getBody();
        $getSignUpResultJson = $this->jsonSerializer->unserialize($webSignUpResult);
        //$this->logger->info("Response: " . $webSignUpResult); 
        
    }
}
