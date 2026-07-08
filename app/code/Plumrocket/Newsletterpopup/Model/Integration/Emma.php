<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class Emma
 * Integration for https://myemma.com
 */
class Emma extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'emma';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.e2ma.net';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = '';

    /**
     * Default Method
     */
    const GET_METHOD = 'GET';

    /**
     * @var
     */
    private $encryptor;

    /**
     * @var null| string
     */
    private $secretAPI;

    /**
     * @var null| string
     */
    private $accountID;

    /**
     * Emma constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param Logger $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curlClient
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\AttributeFactory $customerAttributeFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory ,
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Plumrocket\Newsletterpopup\Model\Integration\Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curlClient,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\AttributeFactory $customerAttributeFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        parent::__construct(
            $configHelper,
            $logger,
            $curlClient,
            $encryptor,
            $remoteAddress,
            $eavConfig,
            $customerAttributeFactory,
            $regionFactory,
            $curlFactory,
            $serializer
        );
        $this->encryptor = $encryptor;
    }

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
     * @param $secretAPIkey
     */
    public function setSecretAPIkey($secretAPIkey)
    {
        $this->secretAPI = $secretAPIkey;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSecretAPIkey()
    {
        return $this->secretAPI ?: $this->encryptor->decrypt($this->getConfigValue('secret_id'));
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getApiAccountID()
    {
        return $this->accountID ?: $this->encryptor->decrypt($this->getConfigValue('account_id'));
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

        return $this->sendRequestByCurl($url, $params, $encodeParams, $method);
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
     * @param $tempSecretAPI
     * @param $accountId
     * @return mixed
     */
    public function getAccountInfo($tempSecretAPI, $accountId)
    {
        if (! empty($tempSecretAPI) && ! empty($accountId)) {
            $this->setSecretAPIkey($tempSecretAPI);
            $this->setApiAccountID($accountId);
        }

        return $this->callAPIMethod($this->getApiAccountID() . '/groups?group_types=g,t');
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
            $apiResult = $this->callAPIMethod($this->getApiAccountID() . '/groups');

            if (is_array($apiResult) && ! isset($apiResult['httpStatus'])) {
                foreach ($apiResult as $list) {
                    if (!empty($list['group_name'])) {
                        $this->allLists[$list['member_group_id']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['member_group_id']] = $list['active_count'];
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

        if (! empty($data['email'])) {
            $result = [];

            $contactInfo = $this->callAPIMethod(
                $this->getApiAccountID() . '/members/add',
                $data,
                'POST'
            );

            $subscribeApiResult = $this->subscribeContactToGroups($contactInfo['member_id'], $listIds);

            if (is_array($subscribeApiResult) && empty($subscribeApiResult['httpStatus'])) {
                $result = $this->getResponseCode();
            } else {
                $this->logFailAddContactToList($subscribeApiResult, $email, 2);
            }

            return ! empty($result) ? $result : false;
        }

        return false;
    }

    /**
     * Subscribe contact to group
     * @param $memberId
     * @param $groupIds
     *
     * @return mixed
     */
    private function subscribeContactToGroups($memberId, $groupIds)
    {
        $groupIds = [
            'group_ids' => $groupIds
        ];

        $this->setHeadersPUT();

        $subscribeApiResult = $this->callAPIMethod(
            $this->getApiAccountID() . '/members/' . $memberId . '/groups',
            $groupIds,
            'PUT'
        );

        $this->setAdapterHeaders([]);

        return $subscribeApiResult;
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);

        $curlClient->setCredentials($this->getApiKey(), $this->getSecretAPIkey());
        $curlClient->addHeader('Content-Type', 'application/json');

        return $this;
    }

    /**
     * Set headers for PUT request
     */
    private function setHeadersPUT()
    {
        $this->setCredentials($this->getApiKey(), $this->getSecretAPIkey());
        $this->addHeaderPUT('Content-Type', 'application/json');
    }

    /**
     * Retrieve prepared label for list
     *
     * @param $list
     * @return \Magento\Framework\Phrase|string
     */
    private function prepareListLabel($list)
    {
        return ! empty($list['group_name']) ? (string)$list['group_name'] : __('Unknown List');
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
                $result['fields'][$key] = $value;
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
