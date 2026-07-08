<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;

/**
 * Class Ontraport
 * Integration for https://app.ontraport.com
 */
class Ontraport extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'ontraport';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.ontraport.com';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = '1';

    /**
     * Default Method
     */
    const GET_METHOD = 'GET';

    /**
     * @var string
     */
    const CONSENT_TO_TRACK = 'Yes';

    /**
     * @var null| string
     */
    private $clientID;

    /**
     * @var null| string
     */
    private $appID;

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setApiUrl(self::API_URL);

        if (!empty($this->getConfigValue('secret_id'))) {
            $this->setAppID($this->getConfigValue('secret_id'));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrationId()
    {
        return self::INTEGRATION_ID;
    }

    /**
     * @return bool
     */
    public function canUseGeneralContactList()
    {
        return true;
    }

    /**
     * @param $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
    }

    /**
     * @param $secret_id
     */
    public function setAppID($appId)
    {
        $this->appID = $appId;
    }

    /**
     * @return string|null
     */
    public function getAppID()
    {
        return $this->appID;
    }

    /**
     * Call API resource by API URL and endpoint script with specific parameters
     *
     * @param $url
     * @param null $params
     * @param string $method
     * @param null $encodeParams
     * @return mixed
     */
    public function callAPIResource($url, $params = null, $method = self::GET_METHOD, $encodeParams = null)
    {
        $method = mb_strtoupper($method);
        $params = ! empty($params) && is_array($params) ? $params : [];

        if (self::GET_METHOD === $method) {
            $url .= ! empty($params) ? ('?' . http_build_query($params)) : '';
            $params = null;
        }

        return $this->sendRequestByCurl($url, $params, $encodeParams);
    }

    /**
     * @param $apiMethodName
     * @param null $params
     * @param string $method
     * @return mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = self::GET_METHOD)
    {
        $this->setApiEndpoint(self::API_ENDPOINT . $apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo($appID = null)
    {
        if (! empty($appID)) {
            $this->setAppID($appID);
        }

        return $this->callAPIMethod('/CampaignBuilderItems?listFields=ids');
    }

    /**
     * Retrieve prepared label for list
     *
     * @param $list
     * @return \Magento\Framework\Phrase|string
     */
    private function prepareListLabel($list)
    {
        return ! empty($list['name']) ? (string)$list['name'] : __('Unknown List');
    }

    public function addContactToList($email, $listIds, $data = null)
    {
        $email = trim($email);
        $listIds = is_array($listIds) ? $listIds : [(string)$listIds];

        if (empty($email) || empty($listIds)) {
            return false;
        }

        return in_array(DataHelper::DEFAULT_GENERAL_LIST_NAME, $listIds)
            ? $this->addContact($email, $data)
            : false;
    }

    /**
     * Add email and data to service
     * @param      $email
     * @param null $data
     * @return array|bool|int
     */
    public function addContact($email, $data = null)
    {
        $data = $this->internalPrepareDataFields($data);
        $data['email'] = trim($email);

        if (! empty($data['email'])) {
            $result = [];

            $url = ! empty($data) ? ('?' . http_build_query($data)) : '';
            $apiResult = $this->callAPIMethod(
                '/Contacts' . $url,
                $data,
                'POST'
            );

            if (is_array($apiResult) && empty($apiResult['httpStatus'])) {
                $result = $this->getResponseCode();
            } else {
                $this->logFailAddContactToList($apiResult, $email);
            }

            return ! empty($result) ? $result : false;
        }

        return false;
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);

        $curlClient->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curlClient->addHeader('Api-Key', $this->getApiKey());
        $curlClient->addHeader('Api-Appid', $this->getAppID());

        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        $result = [];
        $data = $this->prepareDataForContact($data);

        $buildInFields = $this->getBuildInFields();

        foreach ($data as $key => $value) {
            if (! in_array($key, $buildInFields, true)) {
                $result[$key] = $value;
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getBuildInFields()
    {
        return [
            'firstname',
            'lastname',
            'email',
            'cell_phone'
        ];
    }
}
