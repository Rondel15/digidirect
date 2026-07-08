<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class IContact
 * Integration for hhttps://www.icontact.com
 */
class IContact extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'icontact';

    /**
     * Default API URL
     */
    const API_URL = 'https://app.icontact.com/icp/a/';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = '2.2';

    /**
     * @var null| string
     */
    private $password;

    /**
     * @var null| string
     */
    private $accountID;

    /**
     * @var null| string
     */
    private $cliendFolderID;

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setApiUrl(self::API_URL);
        $this->setAppName($this->getConfigValue('app_name'));
        $this->setApiPassword($this->getConfigValue('secret_id'));
        $this->setApiAccountID($this->getConfigValue('account_id'));
        $this->setApiClientFolderID($this->getConfigValue('client_folder_id'));

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
     * @param $password
     */
    public function setApiPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getApiPassword()
    {
        return $this->password;
    }

    /**
     * @param $accountID
     */
    public function setApiAccountID($accountID)
    {
        $this->accountID = $accountID;
    }

    /**
     * @return string|null
     */
    public function getApiAccountID()
    {
        return $this->accountID;
    }

    /**
     * @param $userID
     */
    public function setApiClientFolderID($clientFolderID)
    {
        $this->cliendFolderID = $clientFolderID;
    }

    /**
     * @return string|null
     */
    public function getApiClientFolderID()
    {
        return $this->cliendFolderID;
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
    public function callAPIResource($url, $params = null, $method = 'GET', $encodeParams = null)
    {

        $method = mb_strtoupper($method);
        $params = ! empty($params) && is_array($params) ? $params : [];

        if ('GET' === $method) {
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
    public function callAPIMethod($apiMethodName, $params = null, $method = 'GET')
    {

        $this->setApiEndpoint($apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo($password = null, $accountID = null, $clientFolderID = null)
    {
        if (! empty($password)) {
            $this->setApiPassword($password);
        }

        if (! empty($accountID)) {
            $this->setApiAccountID($accountID);
        }

        if (! empty($clientFolderID)) {
            $this->setApiClientFolderID($clientFolderID);
        }

        return $this->callAPIMethod($this->getApiAccountID());
    }

    /**
     * Retrieve array of lists
     *
     * @return array
     */
    public function getAllLists()
    {
        if (null === $this->allLists) {
            $this->allLists = [];
            $apiResult = $this->callAPIMethod(
                $this->getApiAccountID() . '/c/' . $this->getApiClientFolderID() . '/lists/'
            );

            if ($apiResult) {
                if (isset($apiResult['lists']) && is_array($apiResult['lists']) && !isset($apiResult['httpStatus'])) {
                    foreach ($apiResult['lists'] as $list) {
                        $this->allLists[$list['listId']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['listId']] = $this->getSubscribersCount($list['listId']);
                    }
                } else {
                    $this->logFailGetAllLists($apiResult);
                }
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

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];

            $apiResult = $this->callAPIMethod(
                $this->getApiAccountID() . '/c/' . $this->getApiClientFolderID() . '/contacts',
                [$data],
                'POST'
            );

            foreach ($listIds as $listId) {
                $subscriberData = [
                    'contactId' => $apiResult['contacts'][0]['contactId'],
                    'listId'    => $listId,
                    'status'    => 'normal'
                ];

                $subscribApiResult = $this->subscribeContactToList($subscriberData);

                if (is_array($subscribApiResult) && empty($subscribApiResult['httpStatus'])) {
                    $result[$listId] = $this->getResponseCode();
                } else {
                    $this->logFailAddContactToList($subscribApiResult, $email, $listId);
                }
            }
            return ! empty($result) ? $result : false;
        }
        return false;
    }

    /**
     * Subscribe contact to List
     *
     * @param null $subscriberData
     * @return bool|mixed
     */
    private function subscribeContactToList($subscriberData = null)
    {
        if ($subscriberData) {
            $subsResult = $this->callAPIMethod(
                $this->getApiAccountID() . '/c/' . $this->getApiClientFolderID() . '/subscriptions',
                [$subscriberData],
                'POST'
            );

            return ! empty($subsResult) ? $subsResult : false;
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
        $curlClient->addHeader('Except', '');

        $curlClient->addHeader('Content-Type', 'application/json');
        $curlClient->addHeader('Api-Version', '2.2');
        $curlClient->addHeader('API-AppId', $this->getApiKey());
        $curlClient->addHeader('Api-Username', $this->getAppName());
        $curlClient->addHeader('Api-Password', $this->getApiPassword());

        return $this;
    }

    /**
     * @param  string $id
     * @return int
     */
    private function getSubscribersCount($id)
    {
        if ($id) {
            $apiResult = $this->callAPIMethod(
                $this->getApiAccountID() . '/c/' . $this->getApiClientFolderID() .
                '/subscriptions?listId='.
                $id
            );
        }
        if (is_array($apiResult) && !isset($apiResult['httpStatus'])) {
            return $this->prepareSubscribersCount($apiResult);
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
        return ! empty($list['name']) ? (string)$list['name'] : __('Unknown List');
    }

    /**
     * Retrieve prepared subscribers count for list
     *
     * @param $apiResponse
     * @return int|void
     */
    private function prepareSubscribersCount($apiResponse)
    {
        return count($apiResponse['subscriptions']);
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
            'email',
        ];
    }
}
