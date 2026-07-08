<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Digidirect\Qantas\Model\Data;

/**
 * Customer model
 *
 * @api
 */

class Customer extends \Magento\Customer\Model\Data\Customer implements \Digidirect\Qantas\Api\Data\CustomerInterface
{
    /**
    * Verify QFF Details
    *
    * @return status
    */
    public function verifyQffDetails()
    {
        $serviceUrl = "https://api.services.qantasloyalty.com/api/validation/members";
        $qff_number = $this->_get(self::QFF_NUMBER);
        $qff_lastname = $this->_get(self::QFF_LASTNAME);
        
        $status = false;
        
        if(!empty($qff_number) && !empty($qff_lastname))
        {
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
    
    /**
     * Get QFF Number.
     *
     * @return int|null
     */
    
    public function getQffNumber()
    {
        return $this->_get(self::QFF_NUMBER);
    }
    
    /**
     * Get QFF Last name.
     *
     * @return int|null
     */
    
    public function getQffLastname()
    {
        return $this->_get(self::QFF_LASTNAME);
    }
    
    /**
     * Set QFF Number
     *
     * @param string $qff_number
     * @return $this
     */
    public function setQffNumber($qff_number)
    {
        return $this->setData(self::QFF_NUMBER, $qff_number);
    }
    
    /**
     * Set QFF Last name
     *
     * @param string $qff_lastname
     * @return $this
     */
    public function setQffLastname($qff_lastname)
    {
        return $this->setData(self::QFF_LASTNAME, $qff_lastname);
    }

}
