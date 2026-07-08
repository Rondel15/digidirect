<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

class Sendy extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'sendy';

    /**
     * @var string
     */
    const DEFAULT_TEST_RESPONSE = 'List does not exist';

    /**
     * @var int
     */
    private $api_key;

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();

        $this->setApiUrl($this->getConfigValue('url'));
        $this->setApiKey($this->getConfigValue('key'));
        $this->setDataFormat(self::DATA_FORMAT_PLAINT_TEXT);

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
    public function setApiKey($secret_id)
    {
        $this->api_key = $secret_id;
    }

    /**
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->api_key;
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
    public function callAPIResource($url, $params = null, $method = "POST", $encodeParams = null)
    {
        $params = ! empty($params) && is_array($params) ? $params : [];

        return $this->sendRequestByCurl($url, $params, $encodeParams);
    }

    /**
     * @param $apiMethodName
     * @param null $params
     * @param string $method
     * @return mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = 'POST', $encodeParams = null)
    {
        $this->setApiEndpoint($apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_PLAINT_TEXT);
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
            $data['name'] = $data['firstname'];
        }

        $data = $this->internalPrepareDataFields($data);
        $data['email'] = trim($email);

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $data['list'] = $listId;

                $apiResult = $this->callAPIMethod(
                    '/subscribe',
                    $data,
                    'POST'
                );

                if (is_array($apiResult)) {
                    $result[$listId] = $this->getResponseCode();
                } else {
                    $this->logFailAddContactToList($apiResult, $email, $listId);
                }

                return ! empty($result) ? $result : false;
            }
        }

        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllLists()
    {
        if (null === $this->allLists) {
            $lists = $this->getListsConfig();
            $this->allLists = [];

            if (null !== $lists) {
                foreach ($lists as $list) {
                    $this->allLists[$list['list_id']] = $this->prepareListLabel($list);
                    $this->listSubscribersCount[$list['list_id']] = $this->getSubscribersCount($list['list_id']);
                }
            }
        }

        return $this->allLists;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getListsConfig()
    {
        $listsEncoded = $this->getConfigValue('list');

        return json_decode((string)$listsEncoded, true);
    }

    /**
     * @param null $appID
     * @param null $appUrl
     * @return mixed
     */
    public function testConnection($appID = null, $appUrl = null)
    {
        if (! empty(str_replace('*', '', $appID))) {
            $this->setApiKey($appID);
        }

        if (! empty($appUrl)) {
            $this->setApiUrl($appUrl);
        }

        $result['error_message'] = __('Something went wrong.');
        $result['success'] = false;

        $checkConnect= $this->getSubscribersCount('default', true);

        if (isset($checkConnect) && is_string($checkConnect)) {
            if ($checkConnect === self::DEFAULT_TEST_RESPONSE) {
                $result['success'] = true;
                unset($result['error_message']);
            } elseif (is_string($checkConnect)) {
                $result['error_message'] = $checkConnect;
            }
        }

        return $result;
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
     * @param      $listId
     * @param bool $forTest
     * @return int|mixed
     */
    private function getSubscribersCount($listId, $forTest = false)
    {
        if ($listId || $forTest) {
            $data = [
                'api_key' => $this->getApiKey(),
                'list_id' => $listId
            ];

            $apiResult = $this->callAPIMethod(
                '/api/subscribers/active-subscriber-count.php',
                $data,
                false
            );

            if (isset($apiResult['data'])) {
                return $apiResult['data'];
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
                $result["fields"][$key] = $value;
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getAccountInfo()
    {
        $this->callAPIMethod('');

        return $this->getResponseCode();
    }

    /**
     * @return array
     */
    private function getBuildInFields()
    {
        return [
            'name',
            'email'
        ];
    }
}
