<?php

namespace Digidirect\AbstractGiftCard\Service\Http;

/**
 * Class TransferBuilder
 * @api
 */
class TransferBuilder
{
    /**
     * @var array
     */
    private $_clientConfig = [];

    /**
     * @var array
     */
    private $_headers = [];

    /**
     * @var array
     */
    private $_options = [];

    /**
     * @var string
     */
    private $_method;

    /**
     * @var array|string
     */
    private $_body = [];

    /**
     * @var string
     */
    private $_uri = '';

    /**
     * @var bool
     */
    private $_encode = false;

    /**
     * @var array
     */
    private $_auth = [Transfer::AUTH_USERNAME => null, Transfer::AUTH_PASSWORD => null];

    /**
     * @param array $clientConfig
     * @return $this
     */
    public function setClientConfig(array $clientConfig)
    {
        $this->_clientConfig = $clientConfig;

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * @param array|string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->_body = $body;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setAuthUsername($username)
    {
        $this->_auth[Transfer::AUTH_USERNAME] = $username;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setAuthPassword($password)
    {
        $this->_auth[Transfer::AUTH_PASSWORD] = $password;

        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->_method = $method;

        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;

        return $this;
    }

    /**
     * @param bool $encode
     * @return $this
     */
    public function shouldEncode($encode)
    {
        $this->_encode = $encode;

        return $this;
    }

    /**
     * @return TransferInterface
     */
    public function build()
    {
        return new Transfer(
            $this->_clientConfig,
            $this->_headers,
            $this->_options,
            $this->_body,
            $this->_auth,
            $this->_method,
            $this->_uri,
            $this->_encode
        );
    }
}
