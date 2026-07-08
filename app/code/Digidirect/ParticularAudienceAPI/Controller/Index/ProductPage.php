<?php

namespace Digidirect\ParticularAudienceAPI\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class ProductPage extends Action implements HttpPostActionInterface {
    
    protected $_resultJsonFactory;

    protected $logger;
    
    protected $variable;
    
    protected $curl;

    protected $jsonSerializer;
    
    protected $_registry;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Registry $registry
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->variable = $variable;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->_registry = $registry;
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
        $productId = $this->getRequest()->getParam('productId');
        
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');
        
        if ($productId) {
            $refIdParam = "&refId=".$productId;
        } else {
            $refIdParam = "";
        }
        
        if ($customerId) {
            $customerIdParam = "&customerId=".$customerId;
        } else {
            $customerIdParam = "";
        }
        
        $route = "pa-digi-products-pdp";
        $iphone16s = [388575,388578,388581,388584,388587,388590,388593,388596,388599,388602,388605,388608,388611,388614,388623,390369,390372,390375,390378,390381,390384,390387,390390,390393,390396,390399,390402,390405,390408,390411,390414,390417,390420,390447,390453,390456,390459,390462,390465,390468,390471,390474,390477,390480,390483,390486,390489,390492,390495,390498,390501,390504,390507,390510,390513,390516,390519,390522,395268,395271,395274,395277,395280,395283,395286,395289];
        $iphone15s = [92954,92957,92960,92963,92966,92969,92972,92975,92978,92981,92984,92987,92990,92993,92996,92999,93002,93005,93008,93011,93014,93017,93020,93023,93026,93029,93032,93035,93038,93041,93044,93047,93050,93053,93056,93059,93062,93065,93068,93071,93074,93077,93080,93083,93086,93089,93092,93095,93098,93101,93104,93107,93110,93113,94073,94076,94079,98792,238342,238348,238381,315381,315384,315387,315390,315393,315396,315399,315402,315405,315408,315411,315414];

        if (in_array($productId, $iphone16s)) {
            $route = "pa-iphone-16";
        }
        if (in_array($productId, $iphone15s)) {
            $route = "pa-iphone-15";
        }
        
        $getRecommendationsUrl = "https://api-recs.particularaudience.com/3.0/recommendations?currentUrl=https://www.digidirect.com.au/".$route."&expandProductDetails=true".$refIdParam.$customerIdParam;
        //$this->logger->info("getRecommendationsUrl: " . $getRecommendationsUrl);ß
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
        $this->curl->get($getRecommendationsUrl);

        $getRecommendationsResult = $this->curl->getBody();
        $getRecommendationsResultJson = $this->jsonSerializer->unserialize($getRecommendationsResult);

        $result->setData($getRecommendationsResultJson);
        return $result;
    }
}
