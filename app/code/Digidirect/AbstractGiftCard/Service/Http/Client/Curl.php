<?php

namespace Digidirect\AbstractGiftCard\Service\Http\Client;

use Magento\Framework\HTTP\Client\Curl as CurlClient;
use Digidirect\AbstractGiftCard\Service\Http\ClientInterface;
use Digidirect\AbstractGiftCard\Service\Http\ConverterInterface;
use Digidirect\AbstractGiftCard\Service\Http\TransferInterface;
use Digidirect\AbstractGiftCard\Model\Service\Logger;

/**
 * Class Zend
 * @package Digidirect\AbstractGiftCard\Service\Http\Client
 * @api
 */
class Curl implements ClientInterface
{
    /**
     * @var CurlClient
     */
    private $_client;

    /**
     * @var ConverterInterface | null
     */
    private $_converter;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface
     */
    private $_logger;

    /**
     * @param CurlClient $client
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface $logger
     * @param ConverterInterface | null $converter
     */
    public function __construct(
        CurlClient $client,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardLoggerInterface $logger,
        ConverterInterface $converter = null
    ) {
        $this->_client = $client;
        $this->_converter = $converter;
        $this->_logger = $logger;
    }

    /**
     * {inheritdoc}
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [
            'request' => $transferObject->getBody(),
            'request_uri' => $transferObject->getUri()
        ];
        $result = [];
        $this->_client->setOptions($transferObject->getOptions());
        $this->_client->setHeaders($transferObject->getHeaders());
        try {
            switch ($transferObject->getMethod()) {
                case \Zend_Http_Client::GET:
                    $this->_client->get($transferObject->getUri());
                    break;
                case \Zend_Http_Client::POST:
                    $this->_client->setOption(CURLOPT_POSTFIELDS, $transferObject->getBody());
                    $this->_client->post($transferObject->getUri(), []);
                    break;
                default:
                    throw new \LogicException(
                        sprintf(
                            'Unsupported HTTP method %s',
                            $transferObject->getMethod()
                        )
                    );
            }

            $log['response_status'] = $this->_client->getStatus();
            $body = $this->_client->getBody();
            $log['response'] = $body;
            $result = $this->_converter ? $this->_converter->convert($body) : [$body];
        } catch (\Zend_Http_Client_Exception $e) {
            throw new \Digidirect\AbstractGiftCard\Service\Http\ClientException(
                __($e->getMessage())
            );
        } catch (\Digidirect\AbstractGiftCard\Service\Http\ConverterException $e) {
            throw $e;
        } finally {
            $this->_logger->debug($log);
        }

        return $result;
    }
}
