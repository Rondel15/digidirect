<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

/**
 * Class ActiveCampaign
 * Integration for https://www.activecampaign.com
 */
class ActiveCampaign extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'activecampaign';

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();
        $this->setApiEndpoint('/admin/api.php');

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
     * Add email to list
     *
     * @param $email
     * @param $data
     * @param null $listIds
     *
     * @return bool|array
     */
    public function addContactToList($email, $listIds, $data = null)
    {
        if (null === $listIds) {
            return false;
        }

        $email = trim($email);
        $listIds = is_array($listIds) ? $listIds : [(string)$listIds];

        if (! empty($email) && !empty($listIds)) {
            $result = [];

            foreach ($listIds as $listId) {
                $contactId = false;
                $apiResult = $this->callAPIMethod(
                    'contact_add',
                    $this->internalPrepareContactData($email, $data, $listId),
                    'POST'
                );

                if ($apiResult && ! empty($apiResult['subscriber_id'])) {
                    $contactId = $apiResult['subscriber_id'];
                }

                if ($apiResult && ! empty($apiResult[0]['id'])) {
                    $contactId = $apiResult[0]['id'];
                }

                if ($contactId) {
                    $result[$contactId][] = $listId;
                } else {
                    $this->logFailAddContactToList($apiResult, $email, $listId);
                }
            }

            return ! empty($result) ? $result : false;
        }

        return false;
    }

    /**
     * Retrieve data of account
     *
     * @return bool|mixed
     */
    public function getAccountInfo()
    {
        return $this->callAPIMethod('account_view');
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
            $apiResult = $this->callAPIMethod('list_list', [
                'ids' => 'all',
                'full' => 1,
            ]);

            if (is_array($apiResult)) {
                foreach ($apiResult as $list) {
                    if (is_array($list) && ! empty($list['id'])) {
                        $id = (string)$list['id'];
                        $this->allLists[$id] = $this->prepareListLabel($list);
                        $this->listSubscribersCount[$id] = $this->prepareSubscribersCount($list);
                    }
                }
            } else {
                $this->logFailGetAllLists($apiResult);
            }
        }

        return $this->allLists;
    }

    /**
     * {@inheritdoc}
     */
    public function callAPIResource($url, $params = null, $method = "GET", $encodeParams = null)
    {
        $method = mb_strtoupper($method);

        if ("GET" === $method) {
            $sign = (false !== mb_strpos($url, '?')) ? '&' : '?';
            $url .= $sign . http_build_query($params);
            $params = null;
        }

        if ("GET" !== $method && empty($params)) {
            $params = ['is_post' => true];
        }

        return $this->sendRequestByCurl($url, $params, $encodeParams);
    }

    /**
     * Prepare API URL and params by API method
     *
     * @param $apiMethodName
     * @param null $params
     * @param string $method
     *
     * @return bool|mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = 'GET')
    {
        $params = ! empty($params) && is_array($params) ? $params : [];
        $baseParams = [
            'api_action' => (string)$apiMethodName,
            'api_key' => $this->getApiKey(),
            'api_output' => $this->getDataFormat() ,
        ];
        $url = $this->getBaseApiUrl() . '?' . http_build_query($baseParams);

        return $this->callAPIResource($url, $params, $method);
    }

    /**
     * Retrieve prepared data for addContact API request
     *
     * @param $email
     * @param $data
     * @param $listId
     *
     * @return array
     */
    private function internalPrepareContactData($email, $data, $listId)
    {
        $data = $this->prepareDataForContact($data);
        $requiredData = [
            'email' => (string)$email,
            'ip4' => (string)$this->remoteAddress->getRemoteAddress(),
            'p[' . $listId . ']' => $listId,
            'status[' . $listId . ']' => 1,
        ];

        return array_merge($data, $requiredData);
    }

    /**
     * @param $integrationFieldName
     * @param null $fieldName
     * @return string
     */
    public function prepareIntegrationField($integrationFieldName, $fieldName = null)
    {
        if ($this->isCustomField($integrationFieldName)) {
            return 'field[' . $integrationFieldName . ',0]';
        }

        return parent::prepareIntegrationField($integrationFieldName, $fieldName);
    }

    /**
     * @param $fieldName
     * @return bool
     */
    public function isCustomField($fieldName)
    {
        $fieldName = (string)$fieldName;

        if (empty($fieldName) || mb_strlen($fieldName) < 3) {
            return false;
        }

        return '%' == $fieldName[0] && '%' == $fieldName[mb_strlen($fieldName)-1];
    }

    /**
     * @param $list
     * @return \Magento\Framework\Phrase|string
     */
    private function prepareListLabel($list)
    {
        return ! empty($list['name']) ? (string)$list['name'] : __('Unknown List');
    }

    /**
     * @param $list
     * @return int
     */
    private function prepareSubscribersCount($list)
    {
        return ! empty($list['subscriber_count']) ? (int)$list['subscriber_count'] : 0;
    }
}
