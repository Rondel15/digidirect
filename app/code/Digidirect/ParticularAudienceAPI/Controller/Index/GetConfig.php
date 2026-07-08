<?php

namespace Digidirect\ParticularAudienceAPI\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

class GetConfig extends Action implements HttpPostActionInterface {
    
    protected $_resultJsonFactory;

    protected $logger;
    
    protected $variable;
    
    protected $curl;

    protected $jsonSerializer;
    
    public const PA_CUSTOMER_ID = "pa_customer_id";
    
    public const PA_SESSION_ID = "pa_session_id";

    protected $_cookieManager;

    protected $_cookieMetadataFactory;

    protected $_sessionManager;

    protected $_objectManager;
    
    protected $_remoteAddressInstance;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->variable = $variable;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_objectManager = $objectManager;
        $this->_remoteAddressInstance = $this->_objectManager->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
        parent::__construct($context);
    }

    /**
    * @return ResultInterface
    * @throws LocalizedException
    */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $customerId = $this->getRequest()->getParam('customerId');
        
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');
        
        if ($customerId) {
            $customerIdParam = "?customerId=".$customerId;
        } else {
            $customerIdParam = "";
        }

        $getConfigUrl = "https://api-recs.particularaudience.com/3.0/config".$customerIdParam;
        //$this->logger->info("getRecommendationsUrl: " . $getRecommendationsUrl);
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
        $this->curl->get($getConfigUrl);

        $getConfigResult = $this->curl->getBody();
        $getConfigResultJson = $this->jsonSerializer->unserialize($getConfigResult);
        $result->setData($getConfigResultJson);
        
        $this->setCustomerId($getConfigResultJson['payload']['customerId']);
        $this->setSessionId($getConfigResultJson['payload']['session']['id']);
        
        return $result;
    }

    public function setCustomerId($value) {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            self::PA_CUSTOMER_ID,
            $value,
            $metadata
        );
    }

    public function setSessionId($value, $duration = 1800) {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            self::PA_SESSION_ID,
            $value,
            $metadata
        );
    }
}
