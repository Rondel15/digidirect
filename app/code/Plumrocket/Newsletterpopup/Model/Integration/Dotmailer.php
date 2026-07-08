<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class Dotmailer
 * Integration for https://www.dotmailer.com/
 */
class Dotmailer extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'dotmailer';

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setAppName($this->getConfigValue('app_name'));

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

        if (null === $encodeParams) {
            $encodeParams = self::DATA_FORMAT_JSON;
        }

        return $this->sendRequestByCurl($url, $params, $encodeParams);
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        parent::beforeMakeRequest($curlClient);
        $curlClient->setOption(CURLOPT_USERPWD, $this->getAppName() . ':' . $this->getApiKey());

        return $this;
    }

    /**
     * @param $apiMethodName
     * @param null $params
     * @param string $method
     * @return mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = 'GET')
    {
        $this->setApiEndpoint((string)$apiMethodName);

        return $this->callAPIResource($this->getBaseApiUrl(), $params, $method);
    }

    /**
     * Retrieve data of account
     *
     * @return mixed
     */
    public function getAccountInfo()
    {
        return $this->callAPIMethod('/v2/account-info');
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
            $apiResult = $this->callAPIMethod('/v2/address-books', [
                'select' => 250,
                'skip' => 0,
            ]);

            if ($apiResult && is_array($apiResult) && ! isset($apiResult['message'])) {
                foreach ($apiResult as $list) {
                    $this->allLists[$list['id']] = $this->prepareListLabel($list);
                    $this->listSubscribersCount[$list['id']] = $this->prepareSubscribersCount($list);
                }
            } else {
                $this->logFailGetAllLists($apiResult);
            }
        }

        return $this->allLists;
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
        $params = [
            'email' => $email,
            'optInType' => 'Single',
            'emailType' => 'Html',
            'dataFields' => $this->internalPrepareDataFields($data),
        ];

        $apiResult = $this->callAPIMethod('/v2/contacts', $params, 'POST');

        if ($apiResult && ! empty($apiResult['id'])) {
            return $apiResult['id'];
        } else {
            $this->logFailAddContact($apiResult, $email);
        }

        return false;
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
                $params = [
                    'email' => $email
                ];

                foreach ($listIds as $listId) {
                    if ($this->canSkipContactList($listId)) {
                        continue;
                    }

                    $apiResult = $this->callAPIMethod(
                        sprintf('/v2/address-books/%s/contacts', $listId),
                        $params,
                        'POST'
                    );

                    if ($apiResult && ! empty($apiResult['id'])) {
                        $result[$listId] = $apiResult['id'];
                    } else {
                        $this->logFailAddContactToList($apiResult, $email, $listId);
                    }
                }

                return ! empty($result) ? $result : false;
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
        return ! empty($list['contacts']) ? (int)$list['contacts'] : 0;
    }

    /**
     * @param $data
     * @return array
     */
    private function internalPrepareDataFields($data)
    {
        $result = [];
        $data = $this->prepareDataForContact($data);

        foreach ($data as $key => $value) {
            if ('email' == mb_strtolower($key)) {
                continue;
            }

            $result[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        if (! isset($data['FULLNAME'])) {
            $firstName = ! empty($data['FIRSTNAME']) ? $data['FIRSTNAME'] : self::DEFAULT_FIRST_NAME;
            $lastName = ! empty($data['LASTNAME']) ? $data['LASTNAME'] : self::DEFAULT_LAST_NAME;
            $result[] = [
                'key' => 'FULLNAME',
                'value' => sprintf('%s %s', $firstName, $lastName),
            ];
        }

        return $result;
    }
}
