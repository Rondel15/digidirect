<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class ConstantContact
 * Integration for https://www.constantcontact.com/
 */
class ConstantContact extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'constantcontact';

    /**
     * Url of Constant Contact API
     */
    const API_URL = 'https://api.cc.email';

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact
     */
    private $auth;

    /**
     * @var array[]|null
     */
    private $customFields;

    /**
     * ConstantContact constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param Logger $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curlClient
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\AttributeFactory $customerAttributeFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param Authorization\ConstantContact $auth
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
        \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact $auth,
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
    }

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        $this->setApiUrl(self::API_URL);
        $this->setApiKey(
            $this->getConfigValue('key')
        );

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
     * @param string $encodeParams
     * @return mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = 'GET', $encodeParams = null)
    {
        $this->setApiEndpoint('v3' . $apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, $encodeParams);
    }

    /**
     * Make API request to refresh token
     *
     * @return mixed
     */
    public function validateCredentials()
    {
        $apiResponse = $this->makeListRequest(false);

        return isset($apiResponse['error_message']) ? $apiResponse : true;
    }

    /**
     * Retrieve array of lists
     *
     * @return array
     */
    public function getAllLists()
    {
        if (null === $this->allLists) {
            $apiResult = $this->makeListRequest();

            if ($this->refreshExpiredAccessToken($apiResult)) {
                return $this->getAllLists();
            }

            $this->allLists = [];

            if (is_array($apiResult) && ! empty($apiResult['lists'])) {
                foreach ($apiResult['lists'] as $list) {
                    $this->allLists[$list['list_id']] = $this->prepareListLabel($list);
                    $this->listSubscribersCount[$list['list_id']] = $list['membership_count'];
                }
            } else {
                $this->logFailGetAllLists($apiResult);
            }
        }

        return $this->allLists;
    }

    /**
     * Retrieve array of Custom Fields
     *
     * @param int $limit
     * @return array|false
     */
    public function getCustomFields($limit = 50)
    {
        if (null === $this->customFields) {
            $apiResult = $this->makeCustomFieldsRequest($limit);

            if ($this->refreshExpiredAccessToken($apiResult)) {
                return $this->getCustomFields($limit);
            }

            $this->customFields = [];

            if (is_array($apiResult) && ! empty($apiResult['custom_fields'])) {
                foreach ($apiResult['custom_fields'] as $customField) {
                    $this->customFields[] = [
                        'id' => $customField['custom_field_id'],
                        'label' => $customField['label'],
                    ];
                }
            } else {
                $this->logFail($apiResult, 'Custom Fields cannot be loaded. See API response for details.');
                return false;
            }
        }

        return $this->customFields;
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
        $contactId = $this->addContact($email, $data);

        if ($contactId && ! empty($listIds)) {
            $result = [];
            $data = ['source' => ['contact_ids' => [$contactId]]];
            $data['list_ids'] = is_array($listIds) ? $listIds : [$listIds];

            $apiResult = $this->callAPIMethod(
                '/activities/add_list_memberships',
                $data,
                'POST',
                parent::DATA_FORMAT_JSON
            );

            if (is_array($apiResult) && ! empty($apiResult['error_key'])) {
                $this->logFailAddContactToList($apiResult, $email, implode(', ', $data['list_ids']));
            } else {
                foreach ($data['list_ids'] as $listId) {
                    $result[$listId] = $apiResult;
                }
            }

            return ! empty($result) ? $result : false;
        }

        return false;
    }

    /**
     * Add contact to service
     *
     * @param $email
     * @param null $data
     * @return bool|string
     */
    public function addContact($email, $data = null)
    {
        $preparedContactData = $this->internalPrepareDataFields($data);

        $apiResult = $this->callAPIMethod(
            '/contacts',
            $preparedContactData,
            'POST',
            parent::DATA_FORMAT_JSON
        );

        if ($this->refreshExpiredAccessToken($apiResult)) {
            return $this->addContact($email, $data);
        }

        if ($apiResult && ! empty($apiResult['contact_id'])) {
            return $apiResult['contact_id'];
        }

        $this->logFailAddContact($apiResult, $email);

        return false;
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);
        $curlClient->addHeader('Authorization', 'Bearer ' . $this->getConfigValue('access_token'));
        $curlClient->addHeader('X-Api-Key', $this->getApiKey());

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
     * @param array $data
     * @return array
     */
    private function internalPrepareDataFields(array $data)
    {
        $result = [];
        $data = $this->prepareDataForContact($data);

        $buildInFields = $this->getBuildInFields();

        foreach ($data as $key => $value) {
            if ('address' === $key) {
                $result['email_address'] = ['address' => $value];
                continue;
            }

            if (in_array($key, ['street', 'city', 'state', 'country', 'postal_code'], true)) {
                $result['street_addresses'][$key] = $value;
                continue;
            }

            if ('birthday' === $key) {
                $strTime = strtotime($value);
                $result['birthday_month'] = date('m', $strTime);
                $result['birthday_day'] = date('d', $strTime);
                continue;
            }

            if ('phone_number' === $key) {
                $result['phone_numbers'][$key] = $value;
                continue;
            }

            if (! in_array($key, $buildInFields, true)) {
                $result['custom_fields'][] = [
                    'custom_field_id' => $key,
                    'value'           => $value
                ];
                continue;
            }

            $result[$key] = $value;
        }

        if (! empty($result['street_addresses'])) {
            $result['street_addresses']['kind'] = 'home';
            $result['street_addresses'] = [$result['street_addresses']];
        }

        if (! empty($result['phone_numbers'])) {
            $result['phone_numbers']['kind'] = 'home';
            $result['phone_numbers'] = [$result['phone_numbers']];
        }

        $result['create_source'] = 'Account';

        return $result;
    }

    /**
     * Retrieve data after get lists request
     *
     * @param  boolean $includeSubscriberCount
     * @return array
     */
    private function makeListRequest($includeSubscriberCount = true)
    {
        return $this->callAPIMethod('/contact_lists', ['include_count' => $includeSubscriberCount]);
    }

    /**
     * @param int $limit
     * @return array
     */
    private function makeCustomFieldsRequest($limit)
    {
        return $this->callAPIMethod('/contact_custom_fields', ['limit' => (int)$limit]);
    }

    /**
     * @param $apiResult
     * @return bool
     */
    private function refreshExpiredAccessToken($apiResult)
    {
        if (is_array($apiResult)
            && ! empty($apiResult['error_key'])
            && 'unauthorized' === $apiResult['error_key']
        ) {
            return false !== $this->auth->refreshAccessToken();
        }

        return false;
    }

    /**
     * @return array
     */
    private function getBuildInFields()
    {
        return [
            'email_address',
            'first_name',
            'last_name',
            'job_title',
            'company_name',
            'create_source',
            'birthday_month',
            'birthday_day',
            'anniversary',
            'custom_fields',
            'steet_addresses',
        ];
    }
}
