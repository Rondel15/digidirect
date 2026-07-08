<?php

namespace Digidirect\ParticularAudienceAPI\Controller\Events;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Slots extends Action implements HttpPostActionInterface {
    
    protected $_resultJsonFactory;

    protected $logger;
    
    protected $variable;
    
    protected $curl;

    protected $jsonSerializer;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->variable = $variable;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }

    /**
    * @return ResultInterface
    * @throws LocalizedException
    */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $eventData = json_encode($this->getRequest()->getParam('eventData'));
        
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');

        $getEventUrl = "https://api-recs.particularaudience.com/3.0/events/slot-impressions";
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
        $this->curl->post($getEventUrl, $eventData);

        $getEventResult = $this->curl->getBody();
        $getEventResultJson = $this->jsonSerializer->unserialize($getEventResult);

        $result->setData($getEventResultJson);
        return $result;
    }
}
