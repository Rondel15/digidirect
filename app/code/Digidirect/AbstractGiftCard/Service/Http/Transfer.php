<?php

namespace Digidirect\AbstractGiftCard\Service\Http;

/**
 * Class Transfer
 */
class Transfer implements TransferInterface
{
    /**
     * Name of Auth username field
     */
    const AUTH_USERNAME = 'username';

    /**
     * Name of Auth password field
     */
    const AUTH_PASSWORD = 'password';

    /**
     * @var array
     */
    private $_clientConfig;

    /**
     * @var array
     */
    private $_headers;

    /**
     * @var array
     */
    private $_options;

    /**
     * @var string
     */
    private $_method;

    /**
     * @var array|string
     */
    private $_body;

    /**
     * @var string
     */
    private $_uri;

    /**
     * @var bool
     */
    private $_encode;

    /**
     * @var array
     */
    private $_auth;

    /**
     * Transfer constructor.
     *
     * @param array $clientConfig
     * @param array $headers
     * @param array $options
     * @param string $body
     * @param array $auth
     * @param string $method
     * @param string $uri
     * @param string $encode
     */
    public function __construct(
        array $clientConfig,
        array $headers,
        array $options,
        $body,
        array $auth,
        $method,
        $uri,
        $encode
    ) {
        $this->_clientConfig = $clientConfig;
        $this->_headers = $headers;
        $this->_options = $options;
        $this->_body = $body;
        $this->_auth = $auth;
        $this->_method = $method;
        $this->_uri = $uri;
        $this->_encode = $encode;
    }

    /**
     * Returns gateway client configuration
     *
     * @return array
     */
    public function getClientConfig()
    {
        return $this->_clientConfig;
    }

    /**
     * Returns method used to place request
     *
     * @return string|int
     */
    public function getMethod()
    {
        return (string)$this->_method;
    }

    /**
     * Returns headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Returns client options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns request body
     *
     * @return array|string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Returns URI
     *
     * @return string
     */
    public function getUri()
    {
        return (string)$this->_uri;
    }

    /**
     * @return boolean
     */
    public function shouldEncode()
    {
        return $this->_encode;
    }

    /**
     * Returns Auth username
     *
     * @return string
     */
    public function getAuthUsername()
    {
        return $this->_auth[self::AUTH_USERNAME];
    }

    /**
     * Returns Auth password
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->_auth[self::AUTH_PASSWORD];
    }
}
