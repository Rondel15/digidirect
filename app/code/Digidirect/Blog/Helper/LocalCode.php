<?php
/**
 * Hreflang
 */

namespace Digidirect\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;

class LocalCode extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Hreflang constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLocaleCode()
    {
        $currentStore = $this->storeManager->getStore();
        $storeCode = $currentStore ? $currentStore->getCode() : null;
        $localeCode = $this->getLocaleByStoreCode($storeCode);
        return $localeCode;
    }

    /**
     * @param null|string $storeCode
     * @return mixed
     * @throws LocalizedException
     */
    public function getLocaleByStoreCode($storeCode = null)
    {
        $localeCode = '';
        try {
            $localeCode = $this->config->getValue(
                DirectoryHelper::XML_PATH_DEFAULT_LOCALE,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
        return $localeCode;
    }

    /**
     * @param array $currentLocalCode
     * @param array $storesIds
     * @return array
     */
    public function getUniqueStoreLocales($currentLocalCode, $storesIds = [])
    {
        $storesLocaleCode = [];
        $allStores = $this->storeManager->getStores();
        $isDefaultStore = $this->isDefaultStoreSet($storesIds);
        $currentStoresIds = $isDefaultStore ? $allStores : $storesIds;
        foreach ($currentStoresIds as $storeId) {
            $store = $this->storeManager->getStore($storeId);
            if ($store) {
                $storesLocaleCode[] = $this->getLocaleByStoreCode($store->getCode());
            }
        }
        return array_diff(array_unique($storesLocaleCode), $currentLocalCode);
    }

    /**
     * @param array $storesIds
     * @return bool
     */
    public function isDefaultStoreSet($storesIds)
    {
        return in_array('0', $storesIds);
    }
}
