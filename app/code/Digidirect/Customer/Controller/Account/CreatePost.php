<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digidirect\Customer\Controller\Account;

use Magento\Framework\App\ObjectManager;
/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    public function execute()
    {
        $qff_number = $this->getRequest()->getParam('qff_number');
        $qff_lastname = $this->getRequest()->getParam('qff_lastname');

        if ($qff_number != "" && $qff_lastname != "") {
            $resultRedirect = $this->resultRedirectFactory->create();
            $validationResult = false;
            $action = ""; //Initialized action

            $validationResult = $this->verifyQffDetails($action);
            if ($validationResult) {
                return parent::execute();
            } else {
                $url = $this->urlModel->getUrl('customer/account/create');

                $message = __(
                    'Qantas Frequent Flyer details are invalid',
                    $url
                );

                $this->messageManager->addError($message);

                $this->session->setCustomerFormData($this->getRequest()->getPostValue());
                $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                return $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
            }
        }else{
            return parent::execute();
        }
    }

    public function verifyQffDetails($action)
    {
        $serviceUrl = "https://api.services.qantasloyalty.com/api/validation/members";
        $status = false;

        if(isset($_POST["qff_number"]) && !empty($_POST["qff_number"]) &&
           isset($_POST["qff_lastname"]) && !empty($_POST["qff_lastname"])){
            $qff_number = $_POST["qff_number"];
            $qff_lastname = $_POST["qff_lastname"];

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
                    "authorization: Basic ZGlnaURpcmVjdDpTZzAyMFdDczFzdkZwakU3"
                ),
            ));

            $initial_response = curl_exec($curl);

            curl_close($curl);

            $status = false;
            $response = json_decode($initial_response);
            if (!empty($response->status)) {
                if ($response->status == "ACTIVE") {
                    $status = true;
                }
            }
        }

        return $status;
    }
}
