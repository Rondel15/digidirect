<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class CampaignMonitor
 * Integration for https://www.campaignmonitor.com
 */
class CampaignMonitor extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'campaignmonitor';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.createsend.com/api';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v3.2';

    /**
     * Default Method
     */
    const GET_METHOD = 'GET';

    /**
     * @var string
     */
    const CONSENT_TO_TRACK = 'Yes';

    /**
     * @var null|false|array
     */
    private $customFields;

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
    public function callAPIMethod($apiMethodName, $params = null, $method = self::GET_METHOD, $encodeParams = null)
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
        return $this->callAPIMethod('/clients.json');
    }

    /**
     * Retrieve array of lists
     *
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllLists()
    {
        $this->setClientID($this->getConfigValue('client_id'));

        if (null === $this->allLists) {
            $this->allLists = [];
            $apiResult = $this->callAPIMethod('/clients/' . $this->clientID . '/lists.json');

            if (is_array($apiResult) && ! isset($apiResult['httpStatus'])) {

                foreach ($apiResult as $list) {
                    if (!empty($list['ListID'])) {
                        $this->allLists[$list['ListID']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['ListID']] = $this->getSubscribersCount($list['ListID']);
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
        $data['EmailAddress'] = trim($email);

        if (! empty($data['firstname'])) {
            $data['Name'] = $data['firstname'];
        }

        if (! empty($data['EmailAddress']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $data['Resubscribe'] = true;
                $data['RestartSubscriptionBasedAutoresponders'] = true;
                $data['ConsentToTrack'] = self::CONSENT_TO_TRACK;

                $apiResult = $this->callAPIMethod(
                    '/subscribers/' . $listId . '.json',
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
        $curlClient->setCredentials($this->getApiKey(), ":x");

        return $this;
    }

    /**
     * @param  string $ListID
     * @return int
     */
    private function getSubscribersCount($ListID)
    {
        if ($ListID) {
            $apiResult = $this->callAPIMethod('/lists/' . $ListID . '/active.json');

            if (is_array($apiResult) && !isset($apiResult['httpStatus'])) {
                return $this->prepareSubscribersCount($apiResult);
            }
        }

        return 0;
    }

    /**
     * Retrieve prepared label for list
     *
     * @param $list
     * @return \Magento\Framework\Phrase|string
     */
    private function prepareListLabel($list)
    {
        return ! empty($list['Name']) ? (string)$list['Name'] : __('Unknown List');
    }

    /**
     * Retrieve prepared subscribers count for list
     *
     * @param $list
     * @return int
     */
    private function prepareSubscribersCount($apiResponse)
    {
        return $apiResponse['TotalNumberOfRecords'];
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
        $result['name'] = '';

        foreach ($data as $key => $value) {
            if (! in_array($key, $buildInFields, true)) {
                $result['CustomFields'][] = [
                    'key' => $key,
                    'value' => $value
                ];
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
            'EmailAddress',
            'firstname',
            'Name',
            'email'
        ];
    }
}
