<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class Klaviyo
 * Integration for https://www.klaviyo.com/
 */
class Klaviyo extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'klaviyo';

    /**
     * Default API URL
     */
    const API_URL = 'https://a.klaviyo.com/api';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'v2';

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

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, $encodeParams);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo()
    {
        /**
         * We need to use /lists instead of account details
         * because integration does not have the required functionality
         */
        return $this->callAPIMethod('/lists', [
            'api_key' => $this->getApiKey()
        ]);
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
            $apiResult = $this->callAPIMethod('/lists', [
                'api_key' => $this->getApiKey()
            ]);

            if (is_array($apiResult) && ! isset($apiResult['message'])) {
                foreach ($apiResult as $list) {
                    $this->allLists[$list['list_id']] = $this->prepareListLabel($list);
                    $this->listSubscribersCount[$list['list_id']] = $this->getSubscribersCount($list['list_id']);
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
        $data = $this->prepareDataForContact($data);
        $data['email'] = trim($email);
        $data = ['profiles'=>[$data]];
        $data['api_key'] = $this->getApiKey();

        if (! empty($data['profiles']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $apiResult = $this->callAPIMethod(
                    '/list/' . $listId . '/members/',
                    $data,
                    'POST',
                    parent::DATA_FORMAT_JSON
                );

                if (is_array($apiResult) && isset($apiResult[0]['id'])) {
                    $result[$listId] = $apiResult[0]['id'];
                } else {
                    $this->logFailAddContactToList($apiResult, $email, $listId);
                }
            }

            return ! empty($result) ? $result : false;
        }

        return false;
    }

    /**
     * @param  string $campaignId
     * @return int
     */
    private function getSubscribersCount($listId)
    {
        if ($listId) {
            $apiResult = $this->callAPIMethod('/group/' . $listId . '/members/all', [
                'api_key' => $this->getApiKey(),
            ]);

            if (is_array($apiResult) && isset($apiResult['records'])) {
                return $this->prepareSubscribersCount($apiResult['records']);
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
        return ! empty($list['list_name']) ? (string)$list['list_name'] : __('Unknown List');
    }

    /**
     * Retrieve prepared subscribers count for list
     *
     * @param $list
     * @return int
     */
    private function prepareSubscribersCount($records)
    {
        return $records instanceof \Countable ? count($records) : 0;
    }
}
