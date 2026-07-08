<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class GetResponse
 * Integration for https://www.getresponse.co.uk/
 */
class GetResponse extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'getresponse';

    /**
     * Default API URL
     */
    const API_URL = 'https://api.getresponse.com';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v3';

    /**
     * @var null|false|array
     */
    private $customFields;

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
     * Call API resource by API URL and endpoint script with specific parameters
     *
     * @param $url
     * @param null $params
     * @param string $method
     * @param null $encodeParams
     * @return mixed
     */
    public function callAPIResource($url, $params = null, $method = "GET", $encodeParams = null)
    {
        $method = mb_strtoupper($method);
        $params = ! empty($params) && is_array($params) ? $params : [];

        if ("GET" === $method) {
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
    public function getAccountInfo()
    {
        return $this->callAPIMethod('/accounts');
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
            $apiResult = $this->callAPIMethod('/campaigns', [
                'page' => 1,
                'perPage' => 100,
                'sort[name]' => 'asc'
            ]);
            if (is_array($apiResult) && !isset($apiResult['httpStatus'])) {
                foreach ($apiResult as $list) {
                    $this->allLists[$list['campaignId']] = $this->prepareListLabel($list);
                    $this->listSubscribersCount[$list['campaignId']] = $this->getSubscribersCount($list['campaignId']);
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

        if (! empty($data['email']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $data['campaign']['campaignId'] = $listId;
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
        $curlClient->addHeader('X-Auth-Token', 'api-key ' . $this->getApiKey());

        return $this;
    }

    /**
     * @param  string $campaignId
     * @return int
     */
    private function getSubscribersCount($campaignId)
    {
        if ($campaignId) {
            $apiResult = $this->callAPIMethod('/campaigns/statistics/list-size', [
                'query[campaignId]' => $campaignId,
                'query[groupBy]' => 'total'
            ]);

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
        return array_sum(array_column($apiResponse, 'totalSubscribers'));
    }

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        if (!empty($data['middlename'])) {
            $data['firstname'] = empty($data['firstname'])
                ? $data['middlename']
                : $data['firstname'] . ' ' . $data['middlename'];
        }

        if (!empty($data['lastname'])) {
            $data['firstname'] = empty($data['firstname'])
                ? $data['lastname']
                : $data['firstname'] . ' ' . $data['lastname'];
        }

        $result = [];
        $data = $this->prepareDataForContact($data);

        $customFields = $this->getCustomFields();

        foreach ($data as $key => $value) {
            if ('email' === $key) {
                continue;
            }

            if ('name' === $key) {
                $result['name'] = $value;
                continue;
            }

            if ('phone' === $key) {
                continue;
            }

            $customFieldId = array_search($key, $customFields);

            if (false === $customFieldId) {
                continue;
            }

            $result['customFieldValues'][] = [
                'customFieldId' => $customFieldId,
                'value' => is_array($value) ? $value : [$value],
            ];
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
                '/custom-fields',
                ['fields' => 'name']
            );

            if (is_array($apiResult) && ! empty($apiResult)) {
                foreach ($apiResult as $item) {
                    if (isset($item['customFieldId'], $item['name'])) {
                        $this->customFields[$item['customFieldId']] = $item['name'];
                    }
                }
            }
        }

        return $this->customFields;
    }
}
