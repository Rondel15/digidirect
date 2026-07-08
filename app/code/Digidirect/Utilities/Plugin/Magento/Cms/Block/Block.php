<?php

namespace Digidirect\Utilities\Plugin\Magento\Cms\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Customer\Model\Context as CustomerContext;

/**
 * Plugin for auto caching cms block.
 * By default is is disabled.
 * Can be enabled in admin area
 */
class Block
{
    const CACHE_LIFETIME = 'cache_lifetime';
    const DEV_SECTION = 'dev';
    const P_S = '/';
    const CMS_BLOCK_PROCESSING = 'cms_block_processing';
    const CMS_BLOCK_PROCESSING_PATH = self::DEV_SECTION . self::P_S . self::CMS_BLOCK_PROCESSING . self::P_S;
    const USE_CMS_CACHE_BLOCK = 'use_cms_cache_block';
    const CMS_BLOCK_CACHE_LIFETIME = 'cms_block_cache_lifetime';

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreInterface|null
     */
    private $store;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Block constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->httpContext = $context;
    }

    /**
     * @param AbstractBlock $abstractBlock
     * @param array $result
     * @return array
     */
    public function afterGetCacheKeyInfo(AbstractBlock $abstractBlock, $result)
    {
        try {
            if (!$this->isCmsBlocksCacheEnabled()) {
                return $result;
            }
            if (!is_array($result)) {
                $result = [];
            }

            $data = $abstractBlock->getData();
            if(!is_array($data)) {
                $data = [];
            }

            foreach ($data as $key => $value) {
                if(!is_scalar($value)) {
                    continue;
                }
                $result['cache_key_' . $key] = $value;
            }

            $result['nil'] = $abstractBlock->getNameInLayout();
            $result['store_code'] = $this->getCurrentStore()->getCode();
            $result['store_id'] = $this->getCurrentStore()->getId();
            $result['is_logged_in'] = $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
        } catch (\Throwable $exception) {
            return $result;
        } finally {
            return $result;
        }
    }

    /**
     * @param AbstractBlock $abstractBlock
     * @return null
     */
    public function beforeToHtml(AbstractBlock $abstractBlock)
    {
        if (!$this->isCmsBlocksCacheEnabled()) {
            return null;
        }
        try {
            $cacheLifetime = $abstractBlock->getData(static::CACHE_LIFETIME);
            $isCacheLifetimeSet = is_numeric($cacheLifetime);
            if ($isCacheLifetimeSet) {
                return;
            }
            $abstractBlock->setData(static::CACHE_LIFETIME, $this->getCmsCacheBlockLifetime());
        } catch (\Throwable $exception) {
            return null;
        } finally {
            return null;
        }
    }

    /**
     * @return StoreInterface
     */
    private function getCurrentStore()
    {
        if (null === $this->store) {
            $this->store = $this->storeManager->getStore();
        }

        return $this->store;
    }

    /**
     * @return bool
     */
    private function isCmsBlocksCacheEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            static::CMS_BLOCK_PROCESSING_PATH . static::USE_CMS_CACHE_BLOCK
        );
    }

    /**
     * @return mixed
     */
    private function getCmsCacheBlockLifetime()
    {
        return (int)$this->scopeConfig->getValue(
            static::CMS_BLOCK_PROCESSING_PATH . static::CMS_BLOCK_CACHE_LIFETIME
        );
    }
}
