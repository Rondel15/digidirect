<?php

namespace Digidirect\Blog\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Current store getter/setter
 * Needed as multiple store content is applied
 *
 * @api
 */
class CurrentStoreFetcher
{
    const PARAM_STORE = 'store';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var int|null
     */
    protected $currentStoreId;

    /**
     * @var State
     */
    protected $appState;

    /**
     * CurrentStoreFetcher constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param State $appState
     */
    public function __construct(StoreManagerInterface $storeManager, RequestInterface $request, State $appState)
    {
        $this->appState = $appState;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentStoreId()
    {
        if (null === $this->currentStoreId) {
            if ($this->isAdmin()) {
                $this->currentStoreId = $this->request->getParam(static::PARAM_STORE, Store::DEFAULT_STORE_ID);
            } else {
                $this->currentStoreId = $this->storeManager->getStore()->getId();
            }
        }
        return $this->currentStoreId;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAdmin(): bool
    {
        return $this->appState->getAreaCode() == Area::AREA_ADMINHTML;
    }

    /**
     * @param int $storeId
     * @return CurrentStoreFetcher
     */
    public function setCurrentStoreId(int $storeId): CurrentStoreFetcher
    {
        $this->currentStoreId = $storeId;
        return $this;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIsDefault(): bool
    {
        return $this->getCurrentStoreId() == Store::DEFAULT_STORE_ID;
    }

    /**
     * @return int
     */
    public function getDefaultStoreId(): int
    {
        return Store::DEFAULT_STORE_ID;
    }
}
