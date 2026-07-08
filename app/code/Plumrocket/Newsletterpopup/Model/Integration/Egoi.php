<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class Egoi
 * Integration for https://www.e-goi.com/
 */
class Egoi extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'egoi';

    /**
     * Default API URL
     */
    const API_URL = 'http://api.e-goi.com/';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = '/v2/rest.php';

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
        $this->setApiEndpoint(self::API_ENDPOINT);

        $methodName = [
            'method' => $apiMethodName
        ];

        $params = array_merge($methodName, $params);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method, self::DATA_FORMAT_JSON);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo()
    {
        return $this->callAPIMethod(
            'getUserData',
            [
                'functionOptions' => [
                    'apikey' => $this->getApiKey(),
                ],
                'type' => 'json',
            ]
        );
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
                'getLists',
                [
                'functionOptions' => [
                        'apikey' => $this->getApiKey()
                    ],
                    'type' => 'json',
                ]
            );

            if (is_array($apiResult)
                && !isset($apiResult['httpStatus'])
                && !isset($apiResult['Egoi_Api']['getLists']['ERROR'])
            ) {
                $l = $apiResult['Egoi_Api']['getLists']['key_0'];

                $this->allLists[$l['listnum']] = $this->prepareListLabel($l);
                $this->listSubscribersCount[$l['listnum']] = $this->getSubscribersCount($l['listnum']);
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

        $basicData = [
            'functionOptions' => [
                'apikey' => $this->getApiKey(),
                'email' =>trim($email),
            ],
            'type' => 'json'
        ];

        $data = array_merge($basicData, $this->internalPrepareDataFields($data));

        if (! empty($basicData['functionOptions']['email']) && ! empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $data['functionOptions']['listID'] = $listId;
                $data['functionOptions']['email'] = trim($email);
                $data['functionOptions']['apikey'] = $this->getApiKey();

                $apiResult = $this->callAPIMethod(
                    'addSubscriber',
                    $data
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
     * @param  string $listnum
     * @return int
     */
    private function getSubscribersCount($listnum)
    {

        $apiResult = $this->callAPIMethod('getLists', [
            'functionOptions' => [
                'apikey' => $this->getApiKey(),
                'listID' => $listnum
            ],
            'type' => 'json'
        ]);
        if (is_array($apiResult) && !isset($apiResult['httpStatus'])) {
            return $apiResult['Egoi_Api']['getLists']['key_0']['subs_total'];
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
        return ! empty($list['title']) ? (string)$list['title'] : __('Unknown List');
    }

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        $result = [];

        $result['functionOptions']['first_name'] = $data['firstname'] ?? '';

        $data = $this->prepareDataForContact($data);

        $buildInFields = $this->getBuildInFields();

        foreach ($data as $key => $value) {
            if (! in_array($key, $buildInFields, true)) {
                $result['functionOptions'][$key] = $value;
                continue;
            }

            $result['functionOptions'][$key] = $value;
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
            'first_name',
        ];
    }
}
