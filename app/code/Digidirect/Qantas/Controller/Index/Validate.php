<?php

namespace Digidirect\Qantas\Controller\Index;

class Validate extends \Magento\Framework\App\Action\Action
{
    
    public function __construct(    
            \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
    }
    
    /**
    * The controller action
    *
    */
    public function execute()
    {
        $data = array();
        $data["result"] = $this->verifyQffDetails();

        echo json_encode($data);
        exit;
    }
    
    public function verifyQffDetails()
    {
        $serviceUrl = "https://api.services.qantasloyalty.com/api/validation/members";
        $status = false;
        
        if(isset($_POST["qff_number"]) && !empty($_POST["qff_number"]) &&
           isset($_POST["qff_lastname"]) && !empty($_POST["qff_lastname"])){
            $qff_number = $_POST["qff_number"];
            $qff_lastname = $_POST["qff_lastname"];
            $qff_action = $_POST["qff_action"];
        
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $serviceUrl,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_POSTFIELDS => "{\r\n \"memberId\" : \"$qff_number\",\r\n \"criteria\" : {\"surname\" : \"$qff_lastname\"}\r\n}\r\n",
              CURLOPT_HTTPHEADER => array(
                "authorization: Basic ZGlnaURpcmVjdDpzWUNsdm8wUjZsTGdFODg1"
              ),
            ));

            $initial_response = curl_exec($curl);

            curl_close($curl);

            $status = false;
            $response = json_decode($initial_response);
            if(!empty($response->status))
            {
                if($response->status == "ACTIVE")
                {
                    $status = true;
                }
            }
        }
        
        return $status;
    }
}