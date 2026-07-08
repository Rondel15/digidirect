<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\Newsletterpopup\Model;

interface IntegrationInterface
{
    /**
     * Unique Identifier of integration
     */
    public const INTEGRATION_ID = null;

    /**
     * Response log key for code
     */
    public const RESPONSE_KEY_CODE = 'code';

    /**
     * Response log key for code
     */
    public const RESPONSE_KEY_BODY = 'body';

    /**
     * Response log key for code
     */
    public const RESPONSE_KEY_DATA = 'data';

    /**
     * Data format type XML
     */
    public const DATA_FORMAT_XML = 'xml';

    /**
     * Data format type JSON
     */
    public const DATA_FORMAT_JSON = 'json';

    /**
     * Data format serialized string
     */
    public const DATA_FORMAT_SERIALIZE = 'serialize';

    /**
     * Data format plain text
     */
    public const DATA_FORMAT_PLAINT_TEXT = 'plain_text';

    /**
     * Retrieve string identifier of integration
     * This identifier must be a unique string for each integration
     *
     * @return string
     */
    public function getIntegrationId();

    /**
     * @return bool
     */
    public function canUseGeneralContactList();

    /**
     * @param $email
     * @param $listIds
     * @param null $data
     * @return mixed
     */
    public function addContactToList($email, $listIds, $data = null);

    /**
     * Retrieve array of lists
     * Must be loaded via API Request
     *
     * @return array
     */
    public function getAllLists();

    /**
     * Retrieve count of subscribers by specified list id
     *
     * @param $listId
     * @return int
     */
    public function getListSubscribersCount($listId);

    /**
     * Call API resource by API URL and endpoint script with specific parameters
     *
     * @param $url
     * @param null $params
     * @param string $method
     * @param null $encodeParams
     * @return mixed
     */
    public function callAPIResource($url, $params = null, $method = 'GET', $encodeParams = null);

    /**
     * Retrieve enabled config value
     *
     * @return bool
     */
    public function isEnable();

    /**
     * Set test connection mode
     *
     * @param $flag
     * @return $this
     */
    public function setTestConnectionMode($flag);

    /**
     * Retrieve flag test connection mode is enable
     *
     * @return bool
     */
    public function getTestConnectionMode();

    /**
     * Set URL of API resource
     *
     * @param $url
     * @return $this
     */
    public function setApiUrl($url);

    /**
     * Retrieve URL of API resource
     *
     * @return string
     */
    public function getApiUrl();

    /**
     * Set name of API APP
     *
     * @param $name
     * @return $this
     */
    public function setAppName($name);

    /**
     * Retrieve name of API APP
     *
     * @return string
     */
    public function getAppName();

    /**
     * Set specific endpoint for API requests
     *
     * @param $apiEndpoint
     * @return $this
     */
    public function setApiEndpoint($apiEndpoint);

    /**
     * Retrieve endpoint of API requests
     *
     * @return string
     */
    public function getApiEndpoint();

    /**
     * Set API Key
     *
     * @param $key
     * @return $this
     */
    public function setApiKey($key);

    /**
     * Retrieve API Key
     *
     * @return string
     */
    public function getApiKey();

    /**
     * Retrieve prepared base part of all API URLs
     * It will be used as base for each API request
     *
     * @return string
     */
    public function getBaseApiUrl();

    /**
     * Array of supported response formats
     *
     * @return array
     */
    public function getSupportedDataFormats();

    /**
     * Set data format
     *
     * @param $format
     * @return $this
     */
    public function setDataFormat($format);

    /**
     * Retrieve current data format
     *
     * @return string
     */
    public function getDataFormat();

    /**
     * Retrieve last response data
     *
     * @param null $part
     * @return array
     */
    public function getResponse($part = null);

    /**
     * Retrieve array of logged responses
     *
     * @return array
     */
    public function getResponseLog();
}
