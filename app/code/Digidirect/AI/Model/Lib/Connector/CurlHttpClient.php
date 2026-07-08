<?php

namespace Digidirect\AI\Model\Lib\Connector;

use Zend\Http\ClientFactory as HttpClientFactory;
use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Magento\Framework\Filesystem\Driver\File as FileSystemDriver;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Digidirect\AI\Model\Lib\Connector\Exceptions as Exceptions;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Logger\Logger;
use Digidirect\AI\Helper\Logger as LoggerHelper;

/**
 * Class AbstractClient
 *
 * @package Digidirect\AI\Model\Lib\Connector
 */
class CurlHttpClient
{
    const URL_PARAM_PATTERN = '{%s}';

    const STREAM_PARENT_FOLDER = DirectoryList::VAR_DIR;
    const STREAM_FOLDER = 'ai_curl_stream';

    const DATA_TYPE_REQUEST = 'request';
    const DATA_TYPE_RESPONSE = 'response';

    const LOG_PREFIX = 'CONN :: ';

    const MASKED_VALUE = '******';

    const MAX_LENGTH_TO_LOG = 1024 * 10;

    /**
     * Zend Client
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * ResourceConnection
     *
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerHelper
     */
    protected $loggerHelper;

    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * File Driver
     *
     * @var FileSystemDriver
     */
    protected $filesystemDriver;

    /**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @see \Zend\Http\Client::config
     *
     * @var array
     */
    protected $httpClientOptions = [
        'maxredirects' => 0,
        'strictredirects' => false,
        'useragent' => HttpClient::class,
        'timeout' => 60,
        'connecttimeout' => null,
        'adapter' => \Zend\Http\Client\Adapter\Curl::class,
        'httpversion' => HttpRequest::VERSION_11,
        'storeresponse' => true,
        'keepalive' => false,
        'outputstream' => false,
        'encodecookies' => true,
        'argseparator' => '&',
        'rfc3986strict' => false,
        'sslcafile' => null,
        'sslcapath' => null,
        'strict' => false,
        'curloptions' => [
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => false,
        ],
    ];

    /**
     * @var array
     */
    protected $defaultRequestHeaders = [];

    /**
     * directory name for store request / response files
     *
     * @var string|null
     */
    protected $rawRequestAndResponseDir = null;

    /**
     * @var bool
     */
    protected $logConnectorData = true;

    /**
     * @var string
     */
    protected $logPlace = Logger::LOG_PLACE_FILE_AND_DB;

    /**
     * AbstractCurlClient constructor.
     *
     * @param HttpClientFactory $httpClientFactory
     * @param ResourceConnection $resourceConnection
     * @param FileSystemDriver $filesystemDriver
     * @param DirectoryList $directoryList
     * @param LoggerHelper $loggerHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpClientFactory $httpClientFactory,
        ResourceConnection $resourceConnection,
        FileSystemDriver $filesystemDriver,
        DirectoryList $directoryList,
        LoggerHelper $loggerHelper,
        LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClientFactory->create();
        $this->resourceConnection = $resourceConnection;
        $this->filesystemDriver = $filesystemDriver;
        $this->directoryList = $directoryList;
        $this->loggerHelper = $loggerHelper;
        $this->logger = $logger;
        $this->init();
    }

    /**
     * @return $this
     */
    protected function init()
    {
        $this->httpClient->setOptions($this->httpClientOptions);
        if (!empty($this->defaultRequestHeaders)) {
            $this->httpClient->setHeaders($this->defaultRequestHeaders);
        }
        return $this;
    }

    /**
     * @param bool $log
     * @return $this
     */
    public function setLogConnectorData($log = true)
    {
        $this->logConnectorData = $log;
        return $this;
    }

    /**
     * @param string $logPlace
     * @return $this
     */
    public function setLogPlace($logPlace)
    {
        $this->logPlace = $logPlace;
        return $this;
    }

    /**
     * @param string $message
     * @param string $level
     * @param array $context
     * @param null|string $place
     * @return $this
     */
    protected function log($message, $level = 'info', array $context = [], $place = null)
    {
        if ($this->logConnectorData && $this->logger instanceof LoggerInterface) {
            if ($place === null) {
                $place = $this->logPlace;
            }
            switch ($level) {
                case 'emergency':
                case 'alert':
                case 'critical':
                case 'error':
                case 'warning':
                case 'notice':
                case 'info':
                case 'debug':
                    break;
                default:
                    $level = 'info';
            }
            $message = static::LOG_PREFIX . $message;
            $this->logger->$level($message, $context, $place);
        }
        return $this;
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $level
     * @return $this
     */
    protected function logAnyway($message, $level = 'info', array $context = [])
    {
        if ($this->logger instanceof LoggerInterface) {
            switch ($level) {
                case 'emergency':
                case 'alert':
                case 'critical':
                case 'error':
                case 'warning':
                case 'notice':
                case 'info':
                case 'debug':
                    break;
                default:
                    $level = 'info';
            }
            $message = static::LOG_PREFIX . $message;
            $this->logger->$level($message, $context, Logger::LOG_PLACE_FILE_AND_DB);
        }
        return $this;
    }

    /**
     * Get Stream Unique Name
     *
     * @param string $type
     * @return string
     */
    protected function getStreamUniqueName($type)
    {
        return sprintf(
            '%s/%s/%s_%s_%s.txt',
            $this->rawRequestAndResponseDir,
            date('Y-m-d'),
            date('H-i-s'),
            $type,
            substr(md5(mt_rand(0, 999999999)), 0, 10)
        );
    }

    /**
     * @param string|\Zend\Uri\Http $uri
     * @return $this
     * @throws Exceptions\RequestException
     */
    public function setUri($uri)
    {
        try {
            $this->log('request uri: ' . $uri);
            $this->httpClient->setUri($uri);
        } catch (\Zend\Uri\Exception\ExceptionInterface $e) {
            $this->logAnyway('Could not set Uri. Zend Uri Exception: ' . $e->getMessage(), 'error');
            throw new Exceptions\RequestException(__('Invalid URI supplied: (%1)', $uri));
        } catch (\Throwable $e) {
            $this->logAnyway('Could not set Uri. Exception: ' . $e->getMessage(), 'error');
            throw new Exceptions\RequestException(__('Invalid URI supplied: (%1)', $uri));
        }

        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query)
    {
        $request = $this->httpClient->getRequest();
        $request->getQuery()->fromArray($query);
        $this->log('request query: ' . urldecode($request->getQuery()->toString()));
        return $this;
    }

    /**
     * @param string $method
     * @return $this
     * @throws Exceptions\RequestException
     */
    public function setMethod($method)
    {
        try {
            $this->log('request method: ' . $method);
            $this->httpClient->setMethod($method);
        } catch (\Zend\Http\Exception\InvalidArgumentException $e) {
            $this->logAnyway('set request method error: ' . $e->__toString(), 'error');
            throw new Exceptions\RequestException($e->__toString());
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getMaskedValue()
    {
        return static::MASKED_VALUE;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return mixed
     */
    protected function maskArrayValues(array $array = [], array $keys = [])
    {
        foreach ($keys as $key) {
            if (strpos($key, '.') === false) {
                if (isset($array[$key])) {
                    $array[$key] = $this->getMaskedValue();
                }
                continue;
            }

            $keyParts = explode('.', $key);
            $firstKey = array_shift($keyParts);
            if (isset($array[$firstKey])) {
                $array[$key] = $this->maskArrayValues($array[$key], [implode('.', $keyParts)]);
            }
        }

        return $array;
    }

    /**
     * @param array $headers
     * @param array $mask
     * @return $this
     * @throws Exceptions\RequestException
     */
    public function setHeaders(array $headers = [], array $mask = [])
    {
        try {
            $headers = array_merge($this->defaultRequestHeaders, $headers);
            $headersToLog = $this->maskArrayValues($headers, $mask);
            $this->log('request headers: ', 'info', $headersToLog);
            $this->httpClient->setHeaders($headers);
        } catch (\Throwable $e) {
            $this->logAnyway('set headers error: ' . $e->getMessage(), 'error');
            throw new Exceptions\RequestException(__('Invalid headers was set'));
        }
        return $this;
    }

    /**
     * @param string|array $rawBody
     * @return string
     */
    protected function prepareContent($rawBody)
    {
        if (is_array($rawBody)) {
            $rawBody = http_build_query($rawBody, '', '&');
        }
        return $rawBody;
    }

    /**
     * @param string|array $rawBody
     * @param array $mask
     * @return $this
     */
    public function setContent($rawBody = '', array $mask = [])
    {
        $rawBodyToLog = $rawBody;
        if (is_array($rawBody)) {
            $rawBodyToLog = $this->maskArrayValues($rawBody, $mask);
        }

        $rawBodyToLog = $this->loggerHelper->varExportForLog($rawBodyToLog);
        if (mb_strlen($rawBodyToLog, 'UTF-8') > self::MAX_LENGTH_TO_LOG) {
            $this->log('request body: ' . PHP_EOL . $rawBodyToLog, 'info', [], Logger::LOG_PLACE_FILE);

            $rawBodyToLogDb = mb_substr($rawBodyToLog, 0, self::MAX_LENGTH_TO_LOG, 'UTF-8');
            $rawBodyToLogDb .= '...' . PHP_EOL . 'request body is too big: download log file to see';
            $this->log('request body: ' . PHP_EOL . $rawBodyToLogDb, 'info', [], Logger::LOG_PLACE_DB);
        } else {
            $this->log('request body: ' . PHP_EOL . $rawBodyToLog);
        }

        $rawBody = $this->prepareContent($rawBody);
        $this->httpClient->setRawBody($rawBody);
        return $this;
    }

    /**
     * @param string $encType
     * @param null|string $boundary
     * @return $this
     */
    public function setEncType($encType, $boundary = null)
    {
        $this->log('encType: ' . $encType);
        $this->httpClient->setEncType($encType, $boundary);
        return $this;
    }

    /**
     * @param string $user
     * @param string $password
     * @param string $type
     * @return $this
     */
    public function setAuth($user, $password = '', $type = HttpClient::AUTH_BASIC)
    {
        $this->httpClient->setAuth($user, $password, $type);
        return $this;
    }

    /**
     * @return string
     */
    protected function getDirectoryPathForStream()
    {
        $basePath = $this->directoryList->getPath(static::STREAM_PARENT_FOLDER)
            . DIRECTORY_SEPARATOR
            . static::STREAM_FOLDER;

        if (!$this->filesystemDriver->isDirectory($basePath)) {
            $this->filesystemDriver->createDirectory($basePath);
        }

        return $basePath;
    }

    /**
     * @param string $fileContents
     * @param string $type
     * @return string
     */
    protected function _putFileContentsToUniqueStreamFile($fileContents, $type)
    {
        $file = $this->getDirectoryPathForStream() . DIRECTORY_SEPARATOR . $this->getStreamUniqueName($type);
        try {
            $dir = dirname($file);
            if (!$this->filesystemDriver->isDirectory($dir)) {
                $this->filesystemDriver->createDirectory($dir);
            }
            $this->filesystemDriver->filePutContents($file, $fileContents);
        } catch (\Throwable $e) {
            $errorMessage = __(
                'Can`t write %1 to file %2. Error: %3',
                $type,
                $file,
                $e->getMessage()
            );
            $this->logAnyway($errorMessage, 'error');
        }
        return $file;
    }

    /**
     * @param null|string $saveDirName if null, request and response will not be saved.
     * @return $this
     */
    public function setSaveRawRequestAndResponseDir($saveDirName = null)
    {
        $this->rawRequestAndResponseDir = $saveDirName;
        return $this;
    }

    /**
     * Store request data to related log
     *
     * @return $this
     */
    protected function saveRawRequestAndResponse()
    {
        if (!$this->rawRequestAndResponseDir) {
            return $this;
        }

        $requestFile = $responseFile = '';
        if ($lastRawRequest = $this->httpClient->getLastRawRequest()) {
            $requestFile = $this->_putFileContentsToUniqueStreamFile($lastRawRequest, static::DATA_TYPE_REQUEST);
        }

        if ($lastRawResponse = $this->httpClient->getLastRawResponse()) {
            $responseFile = $this->_putFileContentsToUniqueStreamFile($lastRawResponse, static::DATA_TYPE_RESPONSE);
        }

        if (!$this->logger instanceof LoggerInterface) {
            return $this;
        }

        $logId = $this->logger->getLogRecordIdentifier(Logger::LOG_PLACE_DB);
        if (!$logId) {
            return $this;
        }

        $connectorData = [
            'url' => $this->httpClient->getUri()->toString(),
            'method' => $this->httpClient->getMethod(),
            'response_status' => $this->httpClient->getResponse()->getStatusCode(),
            'response_file' => $responseFile,
            'request_file' => $requestFile,
            'log_id' => $logId
        ];

        $connection = $this->resourceConnection->getConnection();
        $connectorDataTable = $this->resourceConnection->getTableName('digidirect_ai_logs_connector_data');
        $connection->insert($connectorDataTable, $connectorData);

        return $this;
    }

    /**
     * @param HttpResponse $response
     * @return $this
     */
    protected function logResponseInfo(HttpResponse $response)
    {
        /**
         * @var $adapter \Zend\Http\Client\Adapter\Curl
         */
        $adapter = $this->httpClient->getAdapter();
        $requestTime = curl_getinfo($adapter->getHandle(), CURLINFO_TOTAL_TIME);
        $requestTimeMessage = sprintf('Request time: %s sec.', round($requestTime, 3));

        $this->logAnyway('request time: ' . $requestTimeMessage);
        $this->logAnyway('response status code: ' . $response->getStatusCode());
        $this->logAnyway('response reason phrase: ' . $response->getReasonPhrase());

        $this->log('response headers: ' . PHP_EOL . $response->getHeaders()->toString());
        if ($this->isResponseBodyEmpty($response)) {
            $this->log('response body is empty');
        } else {
            $rawBodyToLog = $this->loggerHelper->varExportForLog($response->getBody());
            if (mb_strlen($rawBodyToLog, 'UTF-8') > self::MAX_LENGTH_TO_LOG) {
                $this->log('response body: ' . PHP_EOL . $rawBodyToLog, 'info', [], Logger::LOG_PLACE_FILE);

                $rawBodyToLogDb = mb_substr($rawBodyToLog, 0, self::MAX_LENGTH_TO_LOG, 'UTF-8');
                $rawBodyToLogDb .= '...' . PHP_EOL . 'response body is too big: download log file to see';
                $this->log('response body: ' . PHP_EOL . $rawBodyToLogDb, 'info', [], Logger::LOG_PLACE_DB);
            } else {
                $this->log('response body: ' . PHP_EOL . $rawBodyToLog);
            }
        }

        $this->saveRawRequestAndResponse();

        return $this;
    }

    /**
     * @param HttpResponse $response
     * @return void
     * @throws Exceptions\ResponseClientException
     * @throws Exceptions\ResponseException
     * @throws Exceptions\ResponseNotFoundException
     * @throws Exceptions\ResponseRedirectException
     * @throws Exceptions\ResponseServerException
     */
    protected function processResponseCode(HttpResponse $response)
    {
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        if ($response->isInformational() || $response->isSuccess()) {
            return;
        }

        $errorMessage = sprintf('Invalid status code: %s. Reason phrase: %s', $statusCode, $reasonPhrase);

        if ($response->isRedirect()) {
            $this->logAnyway('Response exception: redirect. ' . $errorMessage, 'error');
            throw new Exceptions\ResponseRedirectException($response);
        }

        if ($response->isNotFound()) {
            $this->logAnyway('Not found exception. ' . $errorMessage, 'error');
            throw new Exceptions\ResponseNotFoundException($response);
        }

        if ($response->isForbidden()) {
            $this->logAnyway('Response exception: forbidden. ' . $errorMessage, 'error');
            throw new Exceptions\ResponseClientException($response);
        }

        if ($response->isClientError()) {
            $this->logAnyway('Response exception: client error. ' . $errorMessage, 'error');
            throw new Exceptions\ResponseClientException($response);
        }

        if ($response->isServerError()) {
            $this->logAnyway('Response exception: server error. ' . $errorMessage, 'error');
            throw new Exceptions\ResponseServerException($response);
        }

        $this->logAnyway('Response exception. ' . $errorMessage, 'error');
        throw new Exceptions\ResponseException($response);
    }

    /**
     * @return HttpResponse
     * @throws Exceptions\ConnectionException
     * @throws Exceptions\ResponseException
     * @throws Exceptions\ResponseNotFoundException
     */
    protected function _doRequest()
    {
        try {
            $this->log('before send request');
            $response = $this->httpClient->send();
            $this->log('after send request');

            $this->logResponseInfo($response);
            $this->reset();
            $this->checkAdapterErrors();
            $this->processResponseCode($response);

            return $response;
        } catch (\Zend\Http\Exception\ExceptionInterface $e) {
            if ($e instanceof \Zend\Http\Client\Adapter\Exception\ExceptionInterface) {
                $type = 'Http Client Adapter';
            } elseif ($e instanceof \Zend\Http\Client\Exception\ExceptionInterface) {
                $type = 'Http Client';
            } elseif ($e instanceof \Zend\Http\Header\Exception\ExceptionInterface) {
                $type = 'Http Header';
            } else {
                $type = 'Http';
            }
            $this->closeConnection();
            $this->logAnyway('Zend ' . $type . ' Exception: ' . $e->getMessage(), 'error');
            throw new Exceptions\ConnectionException($e->getMessage());
        }
    }

    /**
     * @param string $responseBody
     * @return mixed
     */
    protected function parseResponse($responseBody)
    {
        return $responseBody;
    }

    /**
     * @param HttpResponse $response
     * @return null|string
     */
    protected function isResponseBodyEmpty(HttpResponse $response)
    {
        $content = $response->getContent();
        $content = trim($content);
        return empty($content);
    }

    /**
     * @return mixed
     */
    public function doRequest()
    {
        $response = $this->_doRequest();
        if ($this->isResponseBodyEmpty($response)) {
            return null;
        }
        $responseBody = $response->getBody();
        return $this->parseResponse($responseBody);
    }

    /**
     * Close connection
     *
     * @return void
     */
    public function closeConnection()
    {
        /**
         * @var $adapter \Zend\Http\Client\Adapter\Curl
         */
        $adapter = $this->httpClient->getAdapter();
        $adapter->close();
    }

    /**
     * Get Http Client
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @return void
     * @throws \Zend\Http\Client\Adapter\Exception\RuntimeException
     */
    protected function checkAdapterErrors()
    {
        /**
         * @var $adapter \Zend\Http\Client\Adapter\Curl
         */
        $adapter = $this->httpClient->getAdapter();
        if ($errNo = curl_errno($adapter->getHandle())) {
            $errorMessage = sprintf('CURL error #%s: %s', $errNo, curl_error($adapter->getHandle()));
            $this->logAnyway('CURL ERROR: ' . $errorMessage, 'error');
            throw new \Zend\Http\Client\Adapter\Exception\RuntimeException($errorMessage);
        }
    }

    /**
     * @param string $endpoint
     * @param string $methodUri
     * @param array $urlParameters
     * @return string
     */
    public function getRequestUrl($endpoint, $methodUri, array $urlParameters = [])
    {
        $parsedUrl = parse_url($methodUri);
        if (!empty($parsedUrl['scheme'])) {
            $url = $methodUri;
        } else {
            $url = $endpoint;
            if ($methodUri) {
                $url = rtrim($url, '/') . '/' . ltrim($methodUri, '/');
            }
        }
        foreach ($urlParameters as $urlParamName => $urlParamValue) {
            $replace = sprintf(static::URL_PARAM_PATTERN, $urlParamName);
            $url = str_replace($replace, $urlParamValue, $url);
        }
        return $url;
    }

    /**
     * Reset last request info
     *
     * @return $this
     */
    protected function reset()
    {
        $this->httpClient->reset();
        return $this;
    }
}
