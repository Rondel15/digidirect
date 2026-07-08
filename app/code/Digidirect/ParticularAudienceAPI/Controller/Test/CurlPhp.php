<?php

namespace Digidirect\ParticularAudienceAPI\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory; 

class CurlPhp extends Action {
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    
    protected $logger;
    
    protected $jsonSerializer;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }  

    public function execute() {
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://conf.shrdsrvcs.p-a.io/auth/connect/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=DBD6C84F-2332-EC11-AAE9-02DCA44CCEEC&client_secret=BcD12@3EeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz456_7890!@DDR&grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        $curlPhp = $this->jsonSerializer->unserialize($server_output);
        $this->logger->info("curlPhp: " . json_encode($curlPhp));
        curl_close($ch);
        
        echo $curlPhp['access_token'];

    }

}
