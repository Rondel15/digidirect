<?php

namespace Digidirect\Utilities\Helper\Frontend;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Helper\Data as MagentoDirectory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\Website;
use Magento\Framework\App\Helper\AbstractHelper;

class WebsiteSwitcher extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var null
     */
    private $rawStores = null;

    /**
     * @var null|bool
     */
    private $storeInUrl;

    /**
     * @var array
     */
    private $currencyById = [];

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * WebsiteSwitcher constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory
    ) {
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]|Website[]
     */
    public function getWebsites()
    {
        return $this->storeManager->getWebsites(false);
    }

    /**
     * @return array
     */
    public function getRawStores()
    {
        if (null === $this->rawStores) {
            $stores = [];
            foreach ($this->getWebsites() as $website) {

                $websiteStores = $website->getStores();
                foreach ($websiteStores as $store) {
                    /* @var $store \Magento\Store\Model\Store */
                    if (!$store->isActive()) {
                        continue;
                    }
                    $localeCode = $this->scopeConfig->getValue(
                        MagentoDirectory::XML_PATH_DEFAULT_LOCALE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $store->setLocaleCode($localeCode);
                    $params = ['_query' => []];
                    if (!$this->isStoreInUrl()) {
                        $params['_query']['___store'] = $store->getCode();
                    }
                    $baseUrl = $store->getUrl('', $params);

                    $store->setHomeUrl($baseUrl);
                    $stores[$store->getGroupId()][$store->getId()] = $store;
                }
            }
            $this->rawStores = $stores;
        }
        return $this->rawStores;
    }

    /**
     * @return bool
     */
    public function isStoreInUrl()
    {
        if ($this->storeInUrl === null) {
            $this->storeInUrl = $this->storeManager->getStore()->isUseStoreInUrl();
        }
        return $this->storeInUrl;
    }

    /**
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @param StoreInterface|Store $store
     * @return mixed
     */
    public function getStoreCurrency($store)
    {
        $storeId = $store->getId();
        if (!isset($this->currencyById[$storeId])) {
            if ($this->storeManager->getStore()->getId() == $storeId) {
                $this->currencyById[$storeId] = $this->storeManager->getStore()->getCurrentCurrency();
            } else {

                $currency = $this->currencyFactory->create()->load($store->getCurrentCurrencyCode());
                $baseCurrency = $store->getBaseCurrency();

                if (!$baseCurrency->getRate($currency)) {
                    $currency = $baseCurrency;
                    $store->setCurrentCurrencyCode($baseCurrency->getCode());
                }
                $this->currencyById[$storeId] = $currency;
            }
        }

        return $this->currencyById[$storeId];
    }
}
