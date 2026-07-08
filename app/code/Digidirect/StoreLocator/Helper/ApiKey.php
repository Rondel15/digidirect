<?php

namespace Digidirect\StoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * You can use configured mapping in di.xml
 * or modify it
 *
 * @since 1.5.0
 */
class ApiKey extends AbstractHelper
{
    /**
     * @var array
     */
    protected $pathArray = [];

    /**
     * @var array
     */
    protected $apiKeyConfiguration = [];

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * ApiKey constructor.
     * @param Context $context
     * @param Config $config
     * @param array $apiKeyConfiguration
     */
    public function __construct(Context $context, Config $config, array $apiKeyConfiguration = [])
    {
        $this->apiKeyConfiguration = $apiKeyConfiguration;
        $this->configHelper = $config;
        $this->preparePathArray($apiKeyConfiguration);
        parent::__construct($context);
    }

    /**
     * @param array $pathArray
     * @return ApiKey
     */
    protected function preparePathArray(array $pathArray = []): self
    {
        return $this->setApiKeyPath($pathArray);
    }

    /**
     * @param array $pathArray
     * @return ApiKey
     */
    public function setApiKeyPath(array $pathArray = []): self
    {
        foreach ($pathArray as $api => $path) {
            $this->pathArray[$api] = $path;
        }

        return $this;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getOriginalApiKeyConfiguration($storeId = null)
    {
        $api = $this->configHelper->getApi($storeId);
        $path = $this->apiKeyConfiguration[$api] ?? null;
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getApiKey($storeId = null)
    {
        $api = $this->configHelper->getApi($storeId);
        $path = $this->pathArray[$api] ?? null;
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
