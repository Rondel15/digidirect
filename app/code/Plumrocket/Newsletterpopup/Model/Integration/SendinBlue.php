<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class SendinBlue
 * Integration for https://my.sendinblue.com
 */
class SendinBlue extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'sendinblue';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.sendinblue.com/';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v3';

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
        $this->setApiEndpoint(self::API_ENDPOINT . $apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo()
    {
        return $this->callAPIMethod('/account');
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
            $apiResult = $this->callAPIMethod('/contacts/lists');

            if (isset($apiResult['lists']) && is_array($apiResult) && ! isset($apiResult['httpStatus'])) {
                foreach ($apiResult['lists'] as $k => $list) {
                    if (! empty($list)) {
                        $this->allLists[$list['id']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['id']] = $list['totalSubscribers'];
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

        $listIds = is_array($listIds) ? array_map('intval', $listIds) : [(int)$listIds];

        $data = $this->internalPrepareDataFields($data);
        $data['email'] = trim($email);
        $data['listIds'] = $listIds;

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $apiResult = $this->callAPIMethod(
                    '/contacts',
                    $data,
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
        $curlClient->addHeader('api-key', $this->getApiKey());

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

        $buildInFields = $this->getBuildInFields();

        foreach ($data as $key => $value) {
            if (! in_array($key, $buildInFields, true)) {
                $result['attributes'][$key] = $value;
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
            'email'
        ];
    }
}
