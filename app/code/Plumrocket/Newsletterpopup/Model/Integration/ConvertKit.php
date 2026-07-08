<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class ConvertKit
 * Integration for https://app.convertkit.com
 */
class ConvertKit extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'convertkit';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.convertkit.com';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v3';

    /**
     * @var null|false|array
     */
    private $customFields;

    /**
     * @var null| string
     */
    private $secret_id;

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setApiUrl(self::API_URL);
        $this->setSecretID($this->getConfigValue('secret_id'));

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
     * @param $secret_id
     */
    public function setSecretID($secret_id)
    {
        $this->secret_id = $secret_id;
    }

    /**
     * @return string|null
     */
    public function getSecretID()
    {
        return $this->secret_id;
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
    public function callAPIMethod($apiMethodName, $params = null, $method = 'GET', $encodeParams = null)
    {
        $this->setApiEndpoint(self::API_ENDPOINT . $apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo($tempSecretId = null)
    {
        if (! empty($tempSecretId)) {
            $this->setSecretID($tempSecretId);
        }

        return $this->callAPIMethod('/account?api_secret=' . $this->getSecretID());
    }

    /**
     * Retrieve array of lists
     *
     * @return array
     */
    public function getAllLists()
    {
        $this->setSecretID($this->getConfigValue('secret_id'));

        if (null === $this->allLists) {
            $this->allLists = [];
            $apiResult = $this->callAPIMethod('/sequences?api_key=' . $this->getApiKey());

            if ($apiResult) {
                if (isset($apiResult['courses'])
                    && is_array($apiResult['courses'])
                    && !isset($apiResult['httpStatus'])
                ) {
                    foreach ($apiResult['courses'] as $list) {
                        $this->allLists[$list['id']] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$list['id']] = $this->getSubscribersCount($list['id']);
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
        if (! empty($data['firstname'])) {
            $data['first_name'] = $data['firstname'];
        }

        $data = $this->internalPrepareDataFields($data);
        $data['email'] = trim($email);
        $data['api_key'] = $this->getApiKey();

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $apiResult = $this->callAPIMethod(
                    '/courses/' . $listId . '/subscribe',
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
                '/sequences/' . $id .
                '/subscriptions?api_secret=' .
                $this->getSecretID()
            );

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
        return ! empty($list['name']) ? (string)$list['name'] : __('Unknown List');
    }

    /**
     * Retrieve prepared subscribers count for list
     *
     * @param $list
     * @return int
     */
    private function prepareSubscribersCount($apiResponse)
    {
        return $apiResponse['total_subscriptions'];
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
            'first_name',
            'email'
        ];
    }
}
