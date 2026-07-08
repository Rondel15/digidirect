<?php
namespace Digidirect\ProductOverlay\Cron;

use Digidirect\ProductOverlay\Helper\Data as Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\FlagManager;
use Magento\Framework\Serialize\Serializer\Json;

class CacheCleaner
{
    const FLAG_KEY = 'digidirect_productoverlay_cache_data';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FlagManager
     */
    protected $flagManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var CacheContext
     */
    protected $cacheContext;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * CacheInvalidator constructor.
     * @param Helper $helper
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FlagManager $flagManager
     * @param Json $json
     * @param CacheInterface $cache
     * @param State $state
     * @param CacheContext $cacheContext
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Helper $helper,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FlagManager $flagManager,
        Json $json,
        CacheInterface $cache,
        State $state,
        CacheContext $cacheContext,
        ManagerInterface $eventManager
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->flagManager = $flagManager;
        $this->json = $json;
        $this->cache = $cache;
        $this->state = $state;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->helper->isEnabledSmartCacheCleaner()) {
            return;
        }

        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            function () {
                $this->cleanOverlaysCache();
            }
        );
    }

    /**
     * @return void
     */
    protected function cleanOverlaysCache()
    {
        if (!$this->helper->getOverlayCollection()->getSize()) {
            return;
        }

        $currentOverlays = [];
        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($products->getItems() as $product) {
            /** @var \Magento\Catalog\Model\Product $product $product */
            $productOverlays = $this->helper->getProductOverlays($product);
            foreach ($productOverlays as $productOverlay) {
                /* @var $productOverlay \Digidirect\ProductOverlay\Model\Overlays */
                $currentOverlays[$productOverlay->getId()][] = $product->getId();
            }
        }

        $oldOverlays = $this->getStoredIds();
        if (empty($oldOverlays) && empty($currentOverlays)) {
            return;
        }

        $productIdsToClean = $this->getProductIdsToCleanCache($oldOverlays, $currentOverlays);
        if (!empty($productIdsToClean)) {
            foreach ($productIdsToClean as $productIdToInvalidate) {
                $this->cache->clean('catalog_product_' . $productIdToInvalidate);
            }
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIdsToClean);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }

        $this->flagManager->saveFlag(self::FLAG_KEY, $this->json->serialize($currentOverlays));
    }

    /**
     * @param array $oldOverlays
     * @param array $currentOverlays
     * @return array
     */
    protected function getProductIdsToCleanCache(array $oldOverlays, array $currentOverlays)
    {
        $dataToIClean = array_merge_recursive(
            $this->arrayDiffRecursive($oldOverlays, $currentOverlays),
            $this->arrayDiffRecursive($currentOverlays, $oldOverlays)
        );

        $productIdsToClean = [];
        foreach ($dataToIClean as $overlayId => $productIds) {
            $productIdsToClean = array_merge($productIdsToClean, $productIds);
        }

        if (!empty($productIdsToClean)) {
            $productIdsToClean = array_unique($productIdsToClean);
        }

        return $productIdsToClean;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function arrayDiffRecursive(array $array1, array $array2)
    {
        $difference = [];
        foreach ($array1 as $overlayId => $productIds) {
            if (!isset($array2[$overlayId])) {
                $difference[$overlayId] = $productIds;
            } else {
                foreach ($productIds as $productId) {
                    if (!in_array($productId, $array2[$overlayId])) {
                        $difference[$overlayId][] = $productId;
                    }
                }
            }
        }
        return $difference;
    }

    /**
     * @return array
     */
    protected function getStoredIds()
    {
        $oldOverlays = $this->flagManager->getFlagData(self::FLAG_KEY);
        if (!empty($oldOverlays)) {
            return $this->json->unserialize($oldOverlays);
        } else {
            return [];
        }
    }
}
