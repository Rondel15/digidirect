<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class MadMimi
 * Integration for https://madmimi.com
 */
class MadMimi extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'madmimi';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.madmimi.com/';

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
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();

        $this->setApiUrl(self::API_URL);
        $this->setAppName($this->getConfigValue('app_name'));

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
        return false;
    }

    /**
     * @param $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = $clientID;
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
        $this->setApiEndpoint($apiMethodName);

        return $this->callAPIResource(
            $this->getBaseApiUrl(),
            $params,
            $method,
            self::DATA_FORMAT_JSON
        );
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo()
    {
        return $this->callAPIMethod('/audience_lists?username='
            . $this->getAppName()
            . '&api_key=' . $this->getApiKey());
    }

    /**
     * Retrieve array of lists
     *
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllLists()
    {
        if (null === $this->allLists) {
            $this->allLists = [];
            $apiResult = $this->callAPIMethod('audience_lists?username=' .
                $this->getAppName() . '&api_key=' .
                $this->getApiKey());

            if (is_array($apiResult) && ! isset($apiResult['httpStatus'])) {
                foreach ($apiResult as $list) {
                    if (!empty($list['id'])) {
                        $this->allLists[$list['id']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['id']] = $list['list_size'];
                    }
                }
            } else {
                $this->logFailGetAllLists($apiResult);
            }
        }

        return $this->allLists;
    }

    /**
     * Add email to list
     * If list not specified $listIds will be get from magento configuration
     *
     * @param $email
     * @param null $data
     * @param array|null $listIds
     * @return array|bool
     */
    public function addContactToList($email, $listIds, $data = null)
    {
        if (null === $listIds) {
            return false;
        }

        $listIds = is_array($listIds) ? $listIds : [(string)$listIds];
        $data = $this->internalPrepareDataFields($data);
        $data['email'] = trim($email);
        $connectData = [];
        $connectData['api_key'] = $this->getApiKey();
        $connectData['username'] = $this->getAppName();

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];
            $url = ! empty($data) ? ('&' . http_build_query($data)) : '';

            foreach ($listIds as $listId) {
                $apiResult = $this->callAPIMethod(
                    'audience_lists/' . $listId . '/add?email=' . $email . $url,
                    $connectData,
                    'POST'
                );

                if (is_array($apiResult) && empty($apiResult['httpStatus'])) {
                    $result[$listId] = $this->getResponseCode();
                } else {
                    $this->logFailAddContactToList($apiResult, $email, $listId);
                }
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

        $curlClient->addHeader('Accept', 'application/json');

        return $this;
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

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        $result = [];
        $data = $this->prepareDataForContact($data);

        foreach ($data as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }
}
