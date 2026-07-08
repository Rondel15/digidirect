<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class Mailjet
 * Integration for https://app.mailjet.com
 */
class Mailjet extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'mailjet';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.mailjet.com';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v3/REST';

    /**
     * @var null|false|array
     */
    private $customFields;

    /**
     * @var
     */
    private $secret_key;

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setApiUrl(self::API_URL);
        $this->setSecretKey($this->getConfigValue('secret_id'));

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
     * @param $secret_key
     */
    public function setSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
    }

    /**
     * @return string|null
     */
    public function getSecretKey()
    {
        return $this->secret_key;
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
        $this->setApiEndpoint(self::API_ENDPOINT . $apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     * @param null $tempSecretKey
     * @return mixed
     */
    public function getAccountInfo($tempSecretKey = null)
    {
        if (! empty($tempSecretKey)) {
            $this->setSecretKey($tempSecretKey);
        }

        return $this->callAPIMethod('/apikey');
    }

    /**
     * Retrieve array of lists
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllLists()
    {
        $this->setSecretKey($this->getConfigValue('secret_id'));

        if (null === $this->allLists) {
            $this->allLists = [];
            $apiResult = $this->callAPIMethod('/contactslist');

            if ($this->getSecretKey() !== null) {
                if (isset($apiResult['Data']) && is_array($apiResult) && !isset($apiResult['httpStatus'])) {
                    foreach ($apiResult['Data'] as $list) {
                        $this->allLists[$list['ID']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['ID']] = $list['SubscriberCount'];
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
        $data['Action'] = 'addnoforce';

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $apiResult = $this->callAPIMethod(
                    '/contactslist/' . $listId . '/managecontact',
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
        $curlClient->setCredentials($this->getApiKey(), $this->getSecretKey());

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
        return ! empty($list['Name']) ? (string)$list['Name'] : __('Unknown List');
    }

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        $result = [];

        if (! empty($data['firstname'])) {
            $result['Properties']['Name'] = $data['firstname'];
        }

        $data = $this->prepareDataForContact($data);
        $customFields = $this->getCustomFields();

        foreach ($data as $key => $value) {
            if ('email' === $key) {
                continue;
            }

            if ('name' === $key) {
                $result['Properties']['Name'] = $value;
                continue;
            }

            $customFieldId = array_search($key, $customFields);

            if (false === $customFieldId) {
                continue;
            }

            $result['Properties'][$key] = $value;
        }

        return $result;
    }

    /**
     * Retrieve id for custom fields
     *
     * @return array
     */
    public function getCustomFields()
    {
        if (null === $this->customFields) {
            $apiResult = $this->callAPIMethod(
                '/contactmetadata'
            );

            if (is_array($apiResult) && ! empty($apiResult)) {
                foreach ($apiResult['Data'] as $item) {
                    if (isset($item['ID'], $item['Name'])) {
                        $this->customFields[$item['ID']] = $item['Name'];
                    }
                }
            }
        }

        return $this->customFields;
    }
}
