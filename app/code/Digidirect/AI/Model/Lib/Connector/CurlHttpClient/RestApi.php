<?php

namespace Digidirect\AI\Model\Lib\Connector\CurlHttpClient;

use Zend\Http\ClientFactory as HttpClientFactory;
use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;
use Digidirect\AI\Model\Lib\Connector\CurlHttpClient;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\Driver\File as FileSystemDriver;
use Magento\Framework\App\Filesystem\DirectoryList;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Helper\Logger as LoggerHelper;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

/**
 * Class RestApiClient
 *
 * @package Digidirect\AI\Model\Lib\Connector
 */
class RestApi extends CurlHttpClient
{
    /**
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @var array
     */
    protected $defaultRequestHeaders = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept' => 'application/json',
    ];

    /**
     * AbstractCurlClient constructor.
     *
     * @param HttpClientFactory $httpClientFactory
     * @param ResourceConnection $resourceConnection
     * @param FileSystemDriver $filesystemDriver
     * @param DirectoryList $directoryList
     * @param SerializerJson $serializerJson
     * @param LoggerHelper $loggerHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpClientFactory $httpClientFactory,
        ResourceConnection $resourceConnection,
        FileSystemDriver $filesystemDriver,
        DirectoryList $directoryList,
        SerializerJson $serializerJson,
        LoggerHelper $loggerHelper,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $httpClientFactory,
            $resourceConnection,
            $filesystemDriver,
            $directoryList,
            $loggerHelper,
            $logger
        );
        $this->serializerJson = $serializerJson;
    }

    /**
     * @param string $endpoint
     * @param string $methodUri
     * @param array $urlParameters
     * @return string
     * @deprecated since release/1.15.3. use ->getRequestUrl() instead of this one
     */
    public function getRestApiRequestUrl($endpoint, $methodUri, array $urlParameters = [])
    {
        return $this->getRequestUrl($endpoint, $methodUri, $urlParameters);
    }

    /**
     * @param string|array $rawBody
     * @param array $mask
     * @return $this
     */
    protected function prepareContent($rawBody = '', array $mask = [])
    {
        if (is_array($rawBody)) {
            $rawBody = empty($rawBody) ? '' : $this->serializerJson->serialize($rawBody);
        }
        return $rawBody;
    }

    /**
     * @param string $responseBody
     * @return mixed
     */
    protected function parseResponse($responseBody)
    {
        return $this->serializerJson->unserialize($responseBody);
    }
}
