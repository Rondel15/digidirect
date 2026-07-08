<?php

namespace Digidirect\Vii\Service\Http;

use Digidirect\AbstractGiftCard\Service\Http\TransferBuilder;
use Digidirect\AbstractGiftCard\Service\Http\TransferFactoryInterface;
use Digidirect\AbstractGiftCard\Service\Http\TransferInterface;
use Digidirect\AbstractGiftCard\Service\ConfigInterface;

/**
 * Class TransferFactory
 * @package Digidirect\Vii\Service\Http\Xml
 */
class TransferFactory implements TransferFactoryInterface
{
    const CURL_SSL_VERSION_FIELD     = 'curl_ssl_version';
    const CLIENT_CONFIG_CONTENT_TYPE = 'text/xml';
    const CLIENT_CONFIG_CHARSET      = 'utf-8';

    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * @var TransferBuilder
     */
    protected $_transferBuilder;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->_config = $config;
        $this->_transferBuilder = $transferBuilder;
    }

    /**
     * @param array $request
     * @param null $storeId
     * @return TransferInterface|mixed
     */
    public function create(array $request, $storeId = null)
    {
        return $this->_transferBuilder
            ->setClientConfig($this->getClientConfig())
            ->setBody($request['xml'])
            ->setMethod(\Zend_Http_Client::POST)
            ->setUri($this->getUri($storeId))
            ->setHeaders($this->getHeaders())
            ->setOptions($this->getOptions())
            ->build();
    }

    /**
     * @return array
     */
    public function getClientConfig()
    {
        return [];
    }

    /**
     * @param null|int $storeId
     * @return string
     */
    public function getUri($storeId = null)
    {
        return $this->_config->getEndpointUrl($storeId);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return [
            'Content-Type'  => self::CLIENT_CONFIG_CONTENT_TYPE,
            'charset'       => self::CLIENT_CONFIG_CHARSET
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        if (($curlSslVersion = (int)$this->_config->getValue(self::CURL_SSL_VERSION_FIELD)) >= 0) {
            $options[CURLOPT_SSLVERSION] = $curlSslVersion;
        }

        return $options;
    }
}
