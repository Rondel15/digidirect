<?php

namespace Digidirect\SEO\Model\Router;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SuffixPathUpdater
 *
 * @package Digidirect\SEO\Model\Router
 */
class SuffixPathUpdater implements PathsUpdaterInterface
{
    /**
     * @var string
     */
    protected $suffixPath;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * SuffixPathUpdater constructor.
     *
     * @param string                $suffixPath
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        string $suffixPath,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->suffixPath = $suffixPath;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $paths
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function update(array $paths)
    {
        $suffix = $this->getUrlSuffix();
        if (!$suffix) {
            return $paths;
        }

        $paths = array_map(
            function ($path) use ($suffix) {
                return $path . $suffix;
            },
            $paths
        );

        return $paths;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function getUrlSuffix()
    {
        $storeId = 0;
        $store = $this->storeManager->getStore();
        if ($store) {
            $storeId = $store->getId();
        }

        return $this->scopeConfig->getValue(
            $this->suffixPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
