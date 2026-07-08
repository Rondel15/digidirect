<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Integration\Authorization;

use Plumrocket\Newsletterpopup\Helper\Data;
use Plumrocket\Newsletterpopup\Model\Integration\ConstantContact as IntegrationModel;

class ConstantContact
{
    /**
     * Request url to access token
     */
    const ACCESS_TOKEN_REQUEST_URL = 'https://idfed.constantcontact.com/as/token.oauth2';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Define if refresh token is used
     *
     * @var boolean
     */
    private $isTokenRefreshed = false;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $refreshToken;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curlClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ConstantContact constructor.
     *
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig
     * @param \Magento\Framework\Encryption\Encryptor                      $encryptor
     * @param \Plumrocket\Newsletterpopup\Helper\Config                    $configHelper
     * @param \Magento\Framework\HTTP\Client\Curl                          $curlClient
     * @param \Psr\Log\LoggerInterface                                     $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Magento\Framework\HTTP\Client\Curl $curlClient,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->configHelper = $configHelper;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->curlClient = $curlClient;
        $this->logger = $logger;
    }

    /**
     * Retrieve authorization code url
     *
     * @return string
     */
    public function getAuthorizationCodeUrl()
    {
        $params = http_build_query(
            [
                'redirect_uri' => $this->configHelper->getConstantContactRedirectUri(),
                'client_id' => $this->configHelper->getConstantContactApiKey(),
                'scope' => 'contact_data',
                'response_type' => 'code',
            ]
        );

        return IntegrationModel::API_URL . '/v3/idfed/?' . $params;
    }

    /**
     * Generate and save new access token
     *
     * @param string $code
     * @return array|bool
     */
    public function generateAccessToken($code)
    {
        $params = http_build_query(
            [
                'code' => $code,
                'redirect_uri' => $this->configHelper->getConstantContactRedirectUri(),
                'grant_type' => 'authorization_code',
                'client_secret' => $this->configHelper->getConstantContactSecret(),
                'client_id' => $this->configHelper->getConstantContactApiKey()
            ]
        );

        $response = $this->sendCurlRequest(self::ACCESS_TOKEN_REQUEST_URL . '?' . $params);

        if (! $response) {
            return false;
        }

        return $this->saveTokensData(json_decode($response, true));
    }

    /**
     * Undocumented function
     *
     * @return array|bool
     */
    public function refreshAccessToken()
    {
        if ($this->isTokenRefreshed) {
            return false;
        }

        $params = http_build_query(
            [
                'client_secret' => $this->configHelper->getConstantContactSecret(),
                'client_id' => $this->configHelper->getConstantContactApiKey(),
                'refresh_token' => $this->configHelper->getConstantContactRefreshToken(),
                'grant_type' => 'refresh_token',
            ]
        );

        $response = $this->sendCurlRequest(self::ACCESS_TOKEN_REQUEST_URL . '?' . $params);

        $this->isTokenRefreshed = true;

        return $this->saveTokensData(json_decode($response, true));
    }

    /**
     * Retrieve access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Retrieve refresh token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Save regenerated refresh and access token
     *
     * @param array $response
     * @return array|bool
     */
    private function saveTokensData($response)
    {
        if (! empty($response['access_token']) && ! empty($response['refresh_token'])) {
            $this->saveConfig('access_token', $response['access_token']);
            $this->saveConfig('refresh_token', $response['refresh_token']);
            $this->scopeConfig->clean();
            $this->accessToken = $response['access_token'];
            $this->refreshToken = $response['refresh_token'];

            return true;
        }

        return $response;
    }

    /**
     * Save config data
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    private function saveConfig($name, $value)
    {
        $this->config->saveConfig(
            $this->getConfigPath($name),
            $this->encryptor->encrypt($value)
        );

        return $this;
    }

    /**
     * Retrieve config path
     *
     * @param string $config
     * @return string
     */
    private function getConfigPath($config)
    {
        return sprintf('%s/integration/%s/%s', Data::SECTION_ID, IntegrationModel::INTEGRATION_ID, $config);
    }

    /**
     * @param string $uri
     * @return string|false
     */
    private function sendCurlRequest($uri)
    {
        try {
            $this->curlClient->post($uri, []);

            return $this->curlClient->getBody();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }
}
