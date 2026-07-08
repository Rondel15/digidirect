<?php

namespace Digidirect\ParticularAudienceAPI\Block\Widget;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;


class HomepageOptimized extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    
    public const PA_CUSTOMER_ID = "pa_customer_id";
    
    public const PA_SESSION_ID = "pa_session_id";
    
    protected $_cookieManager;
    
    protected $_cookieMetadataFactory;
    
    protected $_sessionManager;
    
    protected $_objectManager;
    
    protected $_remoteAddressInstance;
    
    protected $_template = 'Digidirect_ParticularAudienceAPI::widget/homepage-optimized.phtml';
    
    protected $variable;
    
    protected $customer;
    
    protected $curl;
    
    protected $jsonSerializer;
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,  
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        array $data = []
    ) {        
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_objectManager = $objectManager;
        $this->_remoteAddressInstance = $this->_objectManager->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
        $this->variable = $variable;
        $this->customer = $customerSession;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }
    
    public function getCookie($cookie) {
        return $this->_cookieManager->getCookie($cookie);
    }
    
    public function setCookie($cookie, $value) {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            $cookie,
            $value,
            $metadata
        );
    }
    
    public function getBearerToken() {
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');
        return $bearerToken;
    }
    
    public function getConfig() {
        
        $customerId = $this->getCookie(self::PA_CUSTOMER_ID);
        $sessionId = $this->getCookie(self::PA_SESSION_ID);
        
        if (!$customerId || !$sessionId) {
            $bearerToken = $this->getBearerToken();
            if ($customerId) {
                $customerIdParam = "?customerId=".$customerId;
            } else {
                $customerIdParam = "";
            }

            $getConfigUrl = "https://api-recs.particularaudience.com/3.0/config".$customerIdParam;
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
            $this->curl->get($getConfigUrl);

            $getConfigResult = $this->curl->getBody();
            $getConfigResultJson = $this->jsonSerializer->unserialize($getConfigResult);

            $this->setCookie(self::PA_CUSTOMER_ID, $getConfigResultJson['payload']['customerId']);
            $this->setCookie(self::PA_SESSION_ID, $getConfigResultJson['payload']['session']['id']);
        }
    }
    
    public function getRecommendations(){
        
        $this->getConfig();

        $customerId = $this->getCookie(self::PA_CUSTOMER_ID);
        $bearerToken = $this->getBearerToken();
        
        if ($customerId) {
            $customerIdParam = "&customerId=".$customerId;
        } else {
            $customerIdParam = "";
        }

        $getRecommendationsUrl = "https://api-recs.particularaudience.com/3.0/recommendations?currentUrl=https://www.digidirect.com.au/pa-digi-home-page&expandProductDetails=true".$customerIdParam;
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
        $this->curl->get($getRecommendationsUrl);

        $getRecommendationsResult = $this->curl->getBody();
        $getRecommendationsResultJson = $this->jsonSerializer->unserialize($getRecommendationsResult);
        
        return $getRecommendationsResultJson;

    }
    
    public function checkCustomer() {
        return $this->customer;
    }
    
}