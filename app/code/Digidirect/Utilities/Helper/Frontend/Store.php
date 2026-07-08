<?php

namespace Digidirect\Utilities\Helper\Frontend;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @since 1.16.3
 * Ability to get Current store information
 */
class Store extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var null
     */
    protected $currentStore = null;

    /**
     * Store constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface|null|\Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        if (null === $this->currentStore) {
            $this->currentStore = $this->storeManager->getStore();
        }

        return $this->currentStore;
    }

    /**
     * @return bool|\Magento\Store\Model\Website
     */
    public function getCurrentWebsite()
    {
        try {
            $store = $this->getCurrentStore();
            return $store->getWebsite();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @param null $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore($storeId = null)
    {
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsites()
    {
        return $this->storeManager->getWebsites();
    }
}
