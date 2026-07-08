<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration;

use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;

/**
 * Class Mautic
 * Integration for https://mautic.com
 */
class Mautic extends \Plumrocket\Newsletterpopup\Model\AbstractIntegration
{
    /**
     * Identifier of integration
     */
    const INTEGRATION_ID = 'mautic';

    /**
     * Default API Endpoint
     */
    const API_ENDPOINT = 'api';

    /**
     * Default Method
     */
    const GET_METHOD = 'GET';

    /**
     * {@inheritdoc}
     */
    public function initFromSystemConfig()
    {
        parent::initFromSystemConfig();

        $this->setApiUrl($this->getConfigValue('url'));
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
     * @param        $url
     * @param null   $params
     * @param string $method
     * @param null   $encodeParams
     * @return mixed
     */
    public function callAPIResource($url, $params = null, $method = self::GET_METHOD, $encodeParams = null)
    {
        $method = mb_strtoupper($method);
        $params = !empty($params) && is_array($params) ? $params : [];

        if (self::GET_METHOD === $method) {
            $url .= !empty($params) ? ('?' . http_build_query($params)) : '';
            $params = null;
        }

        return $this->sendRequestByCurl($url, $params, $encodeParams);
    }

    /**
     * @param        $apiMethodName
     * @param null   $params
     * @param string $method
     * @return mixed
     */
    public function callAPIMethod($apiMethodName, $params = null, $method = self::GET_METHOD)
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
        return $this->callAPIMethod('/users');
    }

    /**
     * @param      $email
     * @param      $listIds
     * @param null $data
     * @return array|bool|int|mixed
     */
    public function addContactToList($email, $listIds, $data = null)
    {
        $email = trim($email);
        $listIds = is_array($listIds) ? $listIds : [(string)$listIds];

        if (empty($email) || empty($listIds)) {
            return false;
        }

        return in_array(DataHelper::DEFAULT_GENERAL_LIST_NAME, $listIds)
            ? $this->addContact($email, $data)
            : false;
    }

    /**
     * Add email and data to service
     * @param      $email
     * @param null $data
     * @return array|bool|int
     */
    public function addContact($email, $data = null)
    {
        $data = $this->internalPrepareDataFields($data);
        $data['email'] = trim($email);

        if (! empty($data['email'])) {
            $result = [];

            $apiResult = $this->callAPIMethod(
                '/contacts/new',
                $data,
                'POST'
            );

            if (is_array($apiResult) && empty($apiResult['httpStatus'])) {
                $result = $this->getResponseCode();
            } else {
                $this->logFailAddContactToList($apiResult, $email);
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

        $curlClient->setCredentials($this->getAppName(), $this->getApiKey());
        $curlClient->addHeader('Cache-Control', 'no-cache');

        return $this;
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
        $result['name'] = '';

        foreach ($data as $key => $value) {
            if (! in_array($key, $buildInFields, true)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getBuildInFields()
    {
        return [
            'email'
        ];
    }
}
