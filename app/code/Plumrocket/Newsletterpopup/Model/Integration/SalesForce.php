<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class SalesForce
 * Integration for https://www.salesforce.com/
 */
class SalesForce extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'salesforce';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v46.0';

    /**
     * Default Method
     */
    const GET_METHOD = 'GET';

    /**
     * @var string
     */
    const CONSENT_TO_TRACK = 'Yes';

    /**
     * @var
     */
    private $encryptor;

    /**
     * @var null| string
     */
    private $clientID;

    /**
     * @var
     */
    private $instance_url;

    /**
     * @var Authorization\SalesForce
     */
    private $auth;

    /**
     * SalesForce constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param Logger $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curlClient
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\AttributeFactory $customerAttributeFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param Authorization\SalesForce $auth
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
        \Plumrocket\Newsletterpopup\Model\Integration\Authorization\SalesForce $auth,
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

        $this->auth = $auth;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();

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
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getInstanceUrl()
    {
        return $this->encryptor->decrypt($this->getConfigValue('instance_url'));
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
        return $this->callAPIResource($apiMethodName, $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     *
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAccountInfo()
    {
        $response = $this->auth->generateAccessToken();
        $this->instance_url = $this->encryptor->decrypt($this->getConfigValue('instance_url'));

        return $response;
    }

    /**
     * @param bool $apiResult
     * @return bool
     */
    private function refreshExpiredAccessToken($apiResult = false)
    {
        if (! $apiResult) {
            return false !== $this->auth->refreshAccessToken();
        } elseif (is_array($apiResult) && ! empty($apiResult[0]['errorCode'])) {
            return false !== $this->auth->refreshAccessToken();
        }

        return false;
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

            $apiResult = $this->callAPIMethod(
                $this->getInstanceUrl()
                . '/services/data/'
                . self::API_ENDPOINT . '/sobjects/Campaign/'
            );

            if ($this->refreshExpiredAccessToken($apiResult)) {
                return $this->getAllLists();
            }

            if (is_array($apiResult) && ! isset($apiResult['httpStatus'])) {
                foreach ($apiResult['recentItems'] as $list) {
                    if (!empty($list['Id'])) {
                        $this->allLists[$list['Id']] = $list['Name'];
                        $this->listSubscribersCount[$list['Id']] = $this->getSubscribersCount($list['Id']);
                    }
                }
            } else {
                $this->logFailGetAllLists($apiResult);
            }
        }

        return $this->allLists;
    }

    /**
     * @param $listId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSubscribersCount($listId)
    {
        if ($listId) {
            $apiResult = $this->callAPIMethod(
                $this->getInstanceUrl()
                . '/services/data/'
                . self::API_ENDPOINT
                . '/sobjects/Campaign/'
                . $listId
            );

            if ($this->refreshExpiredAccessToken($apiResult)) {
                return $this->getSubscribersCount($listId);
            }

            if (is_array($apiResult) && ! isset($apiResult['httpStatus'])) {
                return $this->prepareSubscribersCount($apiResult);
            }
        }

        return 0;
    }

    /**
     * Retrieve prepared subscribers count for list
     *
     * @param $apiResponse
     * @return mixed
     */
    private function prepareSubscribersCount($apiResponse)
    {
        return $apiResponse['NumberOfContacts'];
    }

    /**
     * @param      $email
     * @param      $listIds
     * @param null $data
     * @return array|bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
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

            $apiResult = $this->callAPIMethod(
                $this->getInstanceUrl() . '/services/data/v46.0/sobjects/Contact/',
                $data,
                'POST'
            );

            if ($this->refreshExpiredAccessToken($apiResult)) {
                return $this->addContactToList($email, $listIds, $data);
            }

            foreach ($listIds as $listId) {
                $subscriptionStatus = false;

                if (is_array($apiResult) && ! empty($apiResult['id'])) {
                    $subscriptionStatus = $this->subscribeContactToCampaign($listId, $apiResult['id']);
                }

                if (is_array($apiResult) && $subscriptionStatus && empty($apiResult['httpStatus'])) {
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
     * Subscribe contact to Campaign
     *
     * @param $campaignId
     * @param $contactId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function subscribeContactToCampaign($campaignId, $contactId)
    {
        $subscribeInfo = [
            'CampaignId' => $campaignId,
            'ContactId' => $contactId,
            'Status' => 'Sent'
        ];

        $apiResult = $this->callAPIMethod(
            $this->getInstanceUrl() . '/services/data/v46.0/sobjects/CampaignMember/',
            $subscribeInfo,
            'POST'
        );

        return is_array($apiResult) && empty($apiResult['httpStatus']);
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this|\Plumrocket\Newsletterpopup\Model\AbstractIntegration
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);
        $curlClient->addHeader('Authorization', 'Bearer ' . $this->getConfigValue('access_token'));

        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        $result = [];

        if (! isset($data['MiddleName'])) {
            $data['middlename'] = ' ';
        }

        $data = $this->prepareDataForContact($data);
        $buildInFields = $this->getBuildInFields();

        foreach ($data as $key => $value) {
            if (in_array($key, $buildInFields, true)) {
                $result[$key] = $value;
                continue;
            }
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
            'LastName',
            'FirstName',
            'MiddleName',
            'Suffix',
            'Name',
            'MailingStreet',
            'MailingCity',
            'MailingState',
            'MailingPostalCode',
            'MailingCountry',
            'MailingLatitude',
            'MailingLongitude',
            'MailingGeocodeAccuracy',
            'Phone',
            'Fax',
            'MobilePhone',
            'ReportsToId',
            'Title',
            'Department',
            'PhotoUrl',
            'Jigsaw',
            'JigsawContactId'
        ];
    }
}
