<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class HubSpot
 * Integration for https://www.hubspot.com
 */
class HubSpot extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'hubspot';

    const LIST_ID_PREFIX = 'hubspot-';

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
        return true;
    }

    /**
     * Add email to list
     * If list not specified $listIds will be get from magento configuration
     *
     * @param $email
     * @param null $data
     * @param null $listIds
     * @return array|bool
     */
    public function addContactToList($email, $listIds, $data = null)
    {
        if (null === $listIds) {
            return false;
        }

        $email = trim($email);
        $listIds = is_array($listIds) ? $listIds : [(string)$listIds];

        if (! empty($email) && ! empty($listIds)) {
            $contactId = $this->addContact($email, $data);

            if ($contactId) {
                $result = [];

                foreach ($listIds as $listId) {
                    if ($this->canSkipContactList($listId)) {
                        continue;
                    }

                    $listId = str_replace(self::LIST_ID_PREFIX, '', $listId);
                    $requestData = [
                        'vids' => [$contactId],
                    ];
                    $apiResult = $this->callAPIMethod(
                        '/contacts/v1/lists/' . urlencode($listId) . '/add',
                        $requestData,
                        'POST'
                    );

                    $responseData = $apiResult ? array_merge($apiResult['updated'], $apiResult['discarded']) : [];

                    if (in_array($contactId, $responseData)) {
                        $result[] = $listId;
                    } else {
                        $this->logFailAddContactToList($apiResult, $email, $listId);
                    }
                }

                return ! empty($result) ? [$contactId => $result] : false;
            }
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
        if (! empty($email)) {
            $properties = $this->preparePropertiesForContact($data);
            $properties[] = [
                'property' => 'email',
                'value' => (string)$email,
            ];

            $apiResult = $this->callAPIMethod(
                '/contacts/v1/contact',
                ['properties' => $properties],
                'POST'
            );

            if (! empty($apiResult['vid'])) {
                return (string)$apiResult['vid'];
            }

            if (! empty($apiResult['status'])
                && ($apiResult['status'] == 'error')
                && ! empty($apiResult['identityProfile']['vid'])
            ) {
                return (string)$apiResult['identityProfile']['vid'];
            } else {
                $this->logFailAddContact($apiResult, $email);
            }
        }

        return false;
    }

    /**
     * @param $email
     * @param null $data
     * @return array|bool|mixed|string
     */
    public function addContactToSelectedLists($email, $data = null)
    {
        try {
            $lists = $this->getSelectedLists();

            return ! empty($lists)
                ? $this->addContactToList($email, $lists, $data)
                : $this->addContact($email, $data);
        } catch (\Exception $e) {
            $this->logErrorAddContactToSelectedLists($email, $e->getMessage());
        }

        return false;
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
            $apiResult = $this->callAPIMethod('/contacts/v1/lists/static', [
                'count' => 250,
            ]);

            if ($apiResult
                && ! empty($apiResult['lists'])
                && is_array($apiResult['lists'])
            ) {
                foreach ($apiResult['lists'] as $list) {
                    $listId = self::LIST_ID_PREFIX . $list['listId'];
                    $this->allLists[$listId] = $this->prepareListLabel($list);
                    $this->listSubscribersCount[$listId] = $this->prepareSubscribersCount($list);
                }
            } else {
                $this->logFailGetAllLists($apiResult);
            }
        }

        return $this->allLists;
    }

    /**
     * Retrieve data of account
     *
     * @return bool|array
     */
    public function getAccountInfo()
    {
        return $this->callAPIMethod('/integrations/v1/me');
    }

    /**
     * Prepare API URL and params by API method
     *
     * @param $apiMethodName
     * @param null $params
     * @param string $method
     * @return mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = "GET")
    {
        $this->setApiEndpoint($apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method);
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this|\Plumrocket\Newsletterpopup\Model\Integration\HubSpot
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);

        $curlClient->addHeader('Content-Type', 'application/json');
        $curlClient->addHeader('Authorization', 'Bearer ' . $this->getApiKey());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function callAPIResource($url, $params = null, $method = "GET", $encodeParams = null)
    {
        $method = mb_strtoupper($method);
        $params = ! empty($params) && is_array($params) ? $params : [];

        if ("GET" === $method) {
            $url .=  empty($params) ? '' : '?' . http_build_query($params);
            $params = null;
        }

        if (null === $encodeParams) {
            $encodeParams = self::DATA_FORMAT_JSON;
        }

        return $this->sendRequestByCurl($url, $params, $encodeParams);
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
    private function prepareSubscribersCount($list)
    {
        return ! empty($list['metaData']['size']) ? (int)$list['metaData']['size'] : 0;
    }

    /**
     * @param $data
     * @return array
     */
    private function preparePropertiesForContact($data)
    {
        $result = [];
        $data = $this->prepareDataForContact($data);

        foreach ($data as $property => $value) {
            if ('email' == mb_strtolower($property)) {
                continue;
            }

            $result[] = [
                'property' => $property,
                'value' => (string)$value,
            ];
        }

        return $result;
    }
}
