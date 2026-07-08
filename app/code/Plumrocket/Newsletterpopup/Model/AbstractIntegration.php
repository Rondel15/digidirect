<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Plumrocket\Newsletterpopup\Helper\Data as DataHelper;

/**
 * Abstract Class AbstractIntegration
 * Base class for all integrations
 */
abstract class AbstractIntegration implements \Plumrocket\Newsletterpopup\Model\IntegrationInterface
{
    /**
     * Default customer first name
     */
    const DEFAULT_FIRST_NAME = 'Valued';

    /**
     * Default customer last name
     */
    const DEFAULT_LAST_NAME = 'Customer';

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    public $remoteAddress;

    /**
     * @var null|array
     */
    protected $allLists;

    /**
     * @var array
     */
    protected $listSubscribersCount = [];

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curlClient;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    private $curlFactory;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    private $customerAttributeFactory;

    /**
     * URL of API resource
     * Required property
     *
     * @var string
     */
    private $apiUrl;

    /**
     * name of API APP
     * Non required property
     *
     * @var string
     */
    private $appName;

    /**
     * Specific endpoint for API request
     * Non required property
     *
     * @var string
     */
    private $apiEndpoint;

    /**
     * Unique key for account of API
     * Required property
     *
     * @var string
     */
    private $apiKey;

    /**
     * Non required property
     *
     * @var string
     */
    private $dataFormat = self::DATA_FORMAT_JSON;

    /**
     * Force enable model functionality for testing connection
     * Used for magento backend only
     * It giving possibility for send API request without check service option - "Enable"
     * Example of usage: $integrationModel->setTestMode(true);
     *
     * @var bool
     */
    private $isTestConnectionMode = false;

    /**
     * Property for storing last response
     *
     * @var array
     */
    private $response = [
        self::RESPONSE_KEY_CODE => null,
        self::RESPONSE_KEY_BODY => null,
        self::RESPONSE_KEY_DATA => null,
    ];

    /**
     * Array of responses
     *
     * @var array
     */
    private $responseLog = [];

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    /**
     * Request headers
     * @var array
     */
    private $adapterHeaders = [];

    /**
     * Integration constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Logger $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curlClient
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\AttributeFactory $customerAttributeFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory ,
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Plumrocket\Newsletterpopup\Model\Integration\Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curlClient,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\AttributeFactory $customerAttributeFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->curlClient = $curlClient;
        $this->encryptor = $encryptor;
        $this->remoteAddress = $remoteAddress;
        $this->eavConfig = $eavConfig;
        $this->customerAttributeFactory = $customerAttributeFactory;
        $this->regionFactory = $regionFactory;
        $this->curlFactory = $curlFactory;
        $this->initFromSystemConfig();
    }

    /**
     * Initialize params from system configuration
     *
     * @return $this
     */
    public function initFromSystemConfig()
    {
        /* Load and set API Url from system configuration */
        $this->setApiUrl(
            $this->getConfigValue('url')
        );
        /* Load and set API Key from system configuration */
        $this->setApiKey(
            $this->getConfigValue('key')
        );

        return $this;
    }

    /**
     * Retrieve logger instance
     *
     * @return \Plumrocket\Newsletterpopup\Model\Integration\Logger
     */
    public function getLogger()
    {
        $this->logger->setIntegrationName(static::INTEGRATION_ID);

        return $this->logger;
    }

    /**
     * Retrieve config helper
     *
     * @return \Plumrocket\Newsletterpopup\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }

    /**
     * Retrieve value of configuration for integration
     * $key is name of field by integration identifier in system.xml file
     *
     * @param $key
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getConfigValue($key)//@codingStandardsIgnoreLine method will be used for extended classes
    {
        $integrationId = $this->getIntegrationId();

        if (empty($integrationId)) {
            $message = __('Config group ID not specified.');
            $this->getLogger()->addDebug($message);
            throw new \Magento\Framework\Exception\LocalizedException($message);
        }

        $configValue = $this->configHelper->getSectionConfig(
            sprintf('integration/%s/%s', $integrationId, $key)
        );

        if ('key' === mb_strtolower($key) || 'access_token' === mb_strtolower($key)) {
            $configValue = $this->encryptor->decrypt($configValue);
        }

        return $configValue;
    }

    /**
     * @return array
     */
    public function getFieldsMapping()
    {
        $value = json_decode($this->getConfigValue('fields_tag'), true);

        return is_array($value) ? $value : [];
    }

    /**
     * @param $data
     * @return array
     */
    public function prepareDataForContact($data)
    {
        $result = [];
        $fieldsMap = $this->getFieldsMapping();

        if (empty($data['firstname'])) {
            $data['firstname'] = self::DEFAULT_FIRST_NAME;
        }

        if (empty($data['lastname'])) {
            $data['lastname'] = self::DEFAULT_LAST_NAME;
        }

        if (empty($data['middlename'])) {
            $data['middlename'] = self::DEFAULT_FIRST_NAME . ' ' . self::DEFAULT_LAST_NAME;
        }

        if (! empty($data['gender'])) {
            $data['gender'] = $this->getGenderOptionText($data['gender']);
        }

        if (empty($data['region']) && ! empty($data['region_id'])) {
            $region = $this->regionFactory->create();
            $region->load($data['region_id']);
            $data['region'] = $region->getName();
        }

        foreach ($fieldsMap as $fieldName => $integrationField) {
            $integrationField = $this->prepareIntegrationField($integrationField, $fieldName);

            if (empty($integrationField) || ! isset($data[$fieldName])) {
                continue;
            }

            $result[$integrationField] = $data[$fieldName];
        }

        return $result;
    }

    /**
     * @param $integrationFieldName
     * @param null $fieldName
     * @return string
     */
    public function prepareIntegrationField($integrationFieldName, $fieldName = null)
    {
        return trim($integrationFieldName);
    }

    /**
     * Retrieve enabled config value
     *
     * @return bool
     */
    public function isEnable()
    {
        return (bool)$this->getConfigValue('enable');
    }

    /**
     * Retrieve flag and define cases when we can skip add contact into contacts list
     *
     * @param $listId
     * @return bool
     */
    public function canSkipContactList($listId)
    {
        return mb_strtolower($listId) === DataHelper::DEFAULT_GENERAL_LIST_NAME;
    }

    /**
     * @return array
     */
    public function getAllLists()
    {
        if (null === $this->allLists) {
            $this->allLists = [];
        }

        return $this->allLists;
    }

    /**
     * Retrieve count of subscribers by specified list id
     *
     * @param $listId
     * @return int
     */
    public function getListSubscribersCount($listId)
    {
        return isset($this->listSubscribersCount[$listId]) ? (int)$this->listSubscribersCount[$listId] : 0;
    }

    /**
     * Retrieve array of selected lists
     *
     * @return array
     */
    public function getSelectedLists()
    {
        $value = (string)$this->getConfigValue('list');

        return ! empty($value) ? explode(',', $value) : [];
    }

    /**
     * @param $email
     * @param null $data
     * @return mixed
     */
    public function addContactToSelectedLists($email, $data = null)
    {
        try {
            return $this->addContactToList($email, $this->getSelectedLists(), $data);
        } catch (\Exception $e) {
            $this->logErrorAddContactToSelectedLists($email, $e->getMessage());
        }

        return false;
    }

    /**
     * @param $email
     * @param $message
     * @return void
     */
    protected function logErrorAddContactToSelectedLists($email, $message)
    {
        $logMessage = sprintf(
            'Email %s failed to be added to the %s network. %s',
            $email,
            $this->getIntegrationId(),
            $message
        );
        $this->logger->critical($logMessage);
    }

    /**
     * Add new record to integration log file
     *
     * @param $apiResult
     * @param null $message
     * @return $this
     */
    protected function logFail($apiResult, $message = null)
    {
        if ($this->isEnable()) {
            $message = sprintf('Integration (%s): %s', mb_strtoupper($this->getIntegrationId()), $message);
            $this->logger->critical($message, ! empty($apiResult) ? $apiResult : $this->getResponseLog());
        }

        return $this;
    }

    /**
     * @param $apiResult
     * @return $this
     */
    public function logFailGetAllLists($apiResult)
    {
        $this->logFail($apiResult, 'Lists cannot be loaded. See API response for details.');

        return $this;
    }

    /**
     * @param $apiResult
     * @param null $email
     * @return $this
     */
    public function logFailAddContact($apiResult, $email = null)
    {
        $message = sprintf('Subscriber %s cannot be added. See API response for details.', $email);
        $this->logFail($apiResult, $message);

        return $this;
    }

    /**
     * @param $apiResult
     * @param null $email
     * @param null $listId
     * @return $this
     */
    public function logFailAddContactToList($apiResult, $email = null, $listId = null)
    {
        $message = sprintf(
            'Subscriber %s cannot be added to the Contact List %s. See API response for details.',
            $email,
            $listId
        );
        $this->logFail($apiResult, $message);

        return $this;
    }

    /**
     * Set test connection mode
     *
     * @param $flag
     * @return $this
     */
    public function setTestConnectionMode($flag)
    {
        $this->isTestConnectionMode = (bool)$flag;

        return $this;
    }

    /**
     * Retrieve flag test connection mode is enable
     *
     * @return bool
     */
    public function getTestConnectionMode()
    {
        return (bool)$this->isTestConnectionMode;
    }

    /**
     * Set URL of API resource
     *
     * @param $url
     * @return $this
     */
    public function setApiUrl($url)
    {
        $this->apiUrl = rtrim((string) $url, '/?');

        return $this;
    }

    /**
     * Retrieve URL of API resource
     *
     * @return string
     */
    public function getApiUrl()
    {
        return rtrim($this->apiUrl, '/?');
    }

    /**
     * Set name of API APP
     *
     * @param $name
     * @return $this
     */
    public function setAppName($name)
    {
        $this->appName = trim((string)$name);

        return $this;
    }

    /**
     * Retrieve name of API APP
     *
     * @return string
     */
    public function getAppName()
    {
        return trim($this->appName);
    }

    /**
     * Set specific endpoint for API requests
     *
     * @param $endpoint
     * @return $this
     */
    public function setApiEndpoint($endpoint)
    {
        $this->apiEndpoint = trim($endpoint, '/?');

        return $this;
    }

    /**
     * Retrieve endpoint of API requests
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return trim($this->apiEndpoint, '/?');
    }

    /**
     * Set API Key
     *
     * @param $key
     * @return $this
     */
    public function setApiKey($key)
    {
        $this->apiKey = trim($key);

        return $this;
    }

    /**
     * Retrieve API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return (string)$this->apiKey;
    }

    /**
     * Retrieve prepared base part of all API URLs
     * It will be used as base for each API request
     *
     * @return string
     */
    public function getBaseApiUrl()
    {
        return $this->getApiUrl() . '/' . $this->getApiEndpoint();
    }

    /**
     * Array of supported response formats
     *
     * @return array
     */
    public function getSupportedDataFormats()
    {
        return [
            self::DATA_FORMAT_XML,
            self::DATA_FORMAT_JSON,
            self::DATA_FORMAT_SERIALIZE,
            self::DATA_FORMAT_PLAINT_TEXT
        ];
    }

    /**
     * Set data format
     *
     * @param $format
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDataFormat($format)
    {
        $format = mb_strtolower($format);

        if (! in_array($format, $this->getSupportedDataFormats())) {
            $message = __('Unsupported response format.');
            $this->getLogger()->debug($message);
            throw new \Magento\Framework\Exception\LocalizedException($message);
        }

        $this->dataFormat = $format;

        return $this;
    }

    /**
     * Retrieve current data format
     *
     * @return string
     */
    public function getDataFormat()
    {
        return $this->dataFormat;
    }

    /**
     * Retrieve last response data
     *
     * @param null $part
     * @return array
     */
    public function getResponse($part = null)
    {
        if (empty($part)) {
            return $this->response;
        }

        return ! empty($this->response[$part])
            ? $this->response[$part]
            : null;
    }

    /**
     * Retrieve array of logged responses
     *
     * @return array
     */
    public function getResponseLog()
    {
        return $this->responseLog;
    }

    /**
     * Set code of HTTP response into response property
     *
     * @param $code
     * @return $this
     */
    private function setResponseCode($code)
    {
        $this->response[self::RESPONSE_KEY_CODE] = (int)$code;

        return $this;
    }

    /**
     * Retrieve code of HTTP response
     *
     * @return int
     */
    public function getResponseCode()
    {
        return (int)$this->getResponse(self::RESPONSE_KEY_CODE);
    }

    /**
     * Set property of response
     *
     * @param $value
     * @return $this
     */
    private function setResponseBody($value)
    {
        $this->response[self::RESPONSE_KEY_BODY] = (string)$value;

        return $this;
    }

    /**
     * Retrieve response text
     *
     * @return string
     */
    public function getResponseBody()
    {
        return (string)$this->getResponse(self::RESPONSE_KEY_BODY);
    }

    /**
     * Set property of response
     *
     * @param $data
     * @return $this
     */
    private function setResponseData($data)
    {
        if (empty($data) || ! is_array($data)) {
            $data = [];
        }

        $this->response[self::RESPONSE_KEY_DATA] = $data;

        return $this;
    }

    /**
     * Retrieve parsed response body
     *
     * @return array
     */
    public function getResponseData()
    {
        return (array)$this->getResponse(self::RESPONSE_KEY_DATA);
    }

    /**
     * Add single response to responseLog array
     *
     * @param $response
     * @return $this
     */
    protected function addResponseLog($response)//@codingStandardsIgnoreLine method will be used for extended classes
    {
        if (is_array($response) && ! empty($response)) {
            $item = [];

            foreach (array_keys($this->response) as $key) {
                $key = strtolower(trim($key));
                $item[$key] = array_key_exists($key, $response)
                    ? $response[$key]
                    : null;
            }

            array_push($this->responseLog, $item);
        }

        return $this;
    }

    /**
     * Check if request can be send
     *
     * @return bool
     */
    public function canSendRequest()
    {
        if ($this->getTestConnectionMode()) {
            return true;
        }

        return $this->isEnable();
    }

    /**
     * Send request to URL by curl
     *
     * @param $url
     * @param null $params
     * @param null $encodeParams
     * @return mixed
     */
    protected function sendRequestByCurl($url, $params = null, $encodeParams = null, $method = null)
    {
        if (! $this->canSendRequest()) {
            return false;
        }

        try {
            $this->curlClient->setOptions([]);
            $this->curlClient->setOption(CURLOPT_HEADER, 0);
            $this->curlClient->setOption(CURLOPT_FOLLOWLOCATION, true);
            $this->curlClient->setHeaders([]);

            /**
             * Set additional Headers for specific response format
             */
            switch ($this->getDataFormat()) {
                case self::DATA_FORMAT_XML:
                    $this->curlClient->addHeader('Content-Type', 'text/xml');
                    $this->curlClient->addHeader('Accept-Charset', 'UTF-8,ISO-8859-1,US-ASCII');
                    break;
            }

            /**
             * Sanitize params if $encodeParams specified
             */
            if (! empty($params)) {
                switch ($encodeParams) {
                    case self::DATA_FORMAT_JSON:
                        $this->curlClient->addHeader('Content-Type', 'application/json');
                        $params = is_array($params) ? $params : [];
                        $params = json_encode($params);
                        break;
                    case self::DATA_FORMAT_SERIALIZE:
                        $params = $this->serializer->serialize($params);
                        break;
                    case self::DATA_FORMAT_XML:
                        if ($params instanceof \SimpleXMLElement) {
                            $params = $params->asXML();
                        } elseif (is_string($params)) {
                            $xmlObj = new \SimpleXMLElement($params);
                            $params = $xmlObj instanceof \SimpleXMLElement
                                ? $xmlObj->asXML()
                                : $params;
                        }
                        break;
                }
            }

            // Add possibility for set custom options
            $this->beforeMakeRequest($this->curlClient);

            if (! empty($params)) {
                if ('PUT' === $method) {
                    /** @var \Magento\Framework\HTTP\Adapter\Curl $httpAdapter */
                    $httpAdapter = $this->curlFactory->create();
                    $httpAdapter->write(
                        'PUT',
                        $url,
                        '',
                        $this->adapterHeaders,
                        $params
                    );

                    $result = $httpAdapter->read();
                    $body = $this->extractBody($result);
                    $status = 200;
                } else {
                    /* Make POST request */
                    $this->curlClient->post($url, $params);
                    $status = $this->curlClient->getStatus();
                    $body = $this->curlClient->getBody();
                }
            } else {
                /* Make GET request */
                $this->curlClient->get($url);
                $status = $this->curlClient->getStatus();
                $body = $this->curlClient->getBody();
            }

            $this->processingResponse(
                $status,
                $body
            );
        } catch (\Exception $e) {
            $message = __(
                'CURL request to "%1" can not be executed. %1',
                $url,
                $e->getMessage()
            );
            $this->getLogger()->critical($message);
            $this->processingResponse(500, false);
        }

        return $this->getResponseData();
    }

    /**
     * Set headers from hash
     *
     * @param $headers
     * @return $this
     */
    public function setAdapterHeaders($headers)
    {
        $this->adapterHeaders = $headers;

        return $this;
    }

    /**
     * Add header
     *
     * @param string $name name, ex. "Location"
     * @param string $value value ex. "http://google.com"
     * @return $this
     */
    public function addHeaderPUT($name, $value)
    {
        $this->adapterHeaders[] = $name . ':' . $value;

        return $this;
    }

    /**
     * Authorization: Basic header
     *
     * Login credentials support
     *
     * @param string $login username
     * @param string $pass password
     * @return $this
     */
    public function setCredentials($login, $pass)
    {
        $val = base64_encode("{$login}:{$pass}");
        $this->addHeaderPUT('Authorization', "Basic {$val}");

        return $this;
    }

    /**
     * @param \Magento\Framework\HTTP\ClientInterface $curlClient
     * @return $this
     */
    protected function beforeMakeRequest(\Magento\Framework\HTTP\ClientInterface $curlClient)
    {
        // You can set specific curl options before send request
        return $this;
    }

    /**
     * @param $code
     * @param $response
     * @return void
     */
    private function processingResponse($code, $response)
    {
        $this->setResponseCode($code);
        $this->setResponseBody($response);
        $this->setResponseData($this->parseResponse($response));
        $this->addResponseLog($this->getResponse());
    }

    /**
     * Parse response data
     *
     * @param $response
     * @return bool|array|SimpleXMLElement
     */
    protected function parseResponse($response)
    {
        if ($response) {
            try {
                switch ($this->getDataFormat()) {
                    case self::DATA_FORMAT_XML:
                        $xml = new \SimpleXMLElement($response);
                        $response = $this->xmlToArray($xml);
                        break;
                    case self::DATA_FORMAT_JSON:
                        $response = json_decode($response, true);
                        break;
                    case self::DATA_FORMAT_SERIALIZE:
                        $response = $this->serializer->unserialize($response);
                        break;
                    case self::DATA_FORMAT_PLAINT_TEXT:
                        $response = ['data' => $response];
                        break;
                    default:
                        $response = false;
                }
            } catch (\Exception $e) {
                $this->getLogger()->critical(__(
                    'CURL response can not be parsed. %1',
                    $e->getMessage()
                ));
                $response = false;
            }
        }

        return ! empty($response) && is_array($response) ? $response : false;
    }

    /**
     * Convert SimpleXML object to array
     *
     * @param $xmlObject
     * @param array $out
     * @return array
     */
    public function xmlToArray($xmlObject, $out = [])
    {
        $i = 0;
        $skipIndex = [];

        foreach ($xmlObject as $index => $node) {
            if (! in_array($index, $skipIndex) && array_key_exists($index, $out)) {
                array_push($skipIndex, $index);
                $out[$i] = $out[$index];
                unset($out[$index]);
                $i++;
            }

            if (in_array($index, $skipIndex)) {
                $index = $i;
                $i++;
            }

            $out[$index] = (is_object($node) && ($node->count() > 0))
                ? $this->xmlToArray($node)
                : $node->__toString();
        }

        return $out;
    }

    /**
     * @param $genderValue
     * @return bool|string
     */
    public function getGenderOptionText($genderValue)
    {
        if (empty($genderValue)) {
            return false;
        }

        /** @var \Magento\Customer\Model\Attribute $customerAttribute */
        $customerAttribute = $this->customerAttributeFactory->create();
        $entityType = $this->eavConfig->getEntityType(Customer::ENTITY);
        $customerAttribute->loadByCode($entityType, CustomerInterface::GENDER);
        $source = $customerAttribute->getSource();

        return $source ? $source->getOptionText((string)$genderValue) : false;
    }

    /**
     * Extract the body from a response string
     *
     * @param string $responseStr
     * @return string
     */
    private function extractBody(string $responseStr): string
    {
        $parts = preg_split('|(?:\r\n){2}|m', $responseStr, 2);
        return $parts[1] ?? '';
    }
}
