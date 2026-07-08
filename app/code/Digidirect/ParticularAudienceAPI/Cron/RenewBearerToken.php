<?php
namespace Digidirect\ParticularAudienceAPI\Cron;

use Psr\Log\LoggerInterface;

class RenewBearerToken {
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $variable;
    
    protected $jsonSerializer;
    
    public function __construct(
        LoggerInterface $logger,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->logger = $logger;
        $this->variable = $variable;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute() {
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $variableData->setPlainValue($this->getBearerToken())->save();
    }
    
    public function getBearerToken()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://conf.shrdsrvcs.p-a.io/auth/connect/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=DBD6C84F-2332-EC11-AAE9-02DCA44CCEEC&client_secret=BcD12@3EeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz456_7890!@DDR&grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        $curlPhp = $this->jsonSerializer->unserialize($server_output);
        $this->logger->info("curlPhp: " . json_encode($curlPhp));
        //$result = json_encode($curlPhp);
        curl_close($ch);
        return $curlPhp['access_token'];
    }
    
}