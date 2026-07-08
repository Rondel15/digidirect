<?php

namespace Digidirect\ProductOverlay\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Digidirect\ProductOverlay\Model\ResourceModel\Overlays\CollectionFactory;
use Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\Status;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Digidirect\ProductOverlay\Model\Overlays;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Framework\App\Cache\Type\Block as BlockCache;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 *
 * @package Digidirect\ProductOverlay\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CURRENT_PRODUCT_REGISTRY = 'productoverlay_current_product';

    const MODULE_CONFIG_PREFIX = 'digidirect_productoverlay/';

    const XML_PATH_GENERAL_IMAGE_TYPE = 'general/image_type';
    const XML_PATH_GENERAL_IMAGE_MIME_TYPE = 'general/image_mime_type';

    const XML_PATH_ON_SALE_SALE_MIN = 'on_sale/sale_min';
    const XML_PATH_ON_SALE_SALE_MIN_PERCENT = 'on_sale/sale_min_percent';
    const XML_PATH_ON_SALE_ROUNDING = 'on_sale/rounding';
    const XML_PATH_NEW_IS_NEW = 'new/is_new';
    const XML_PATH_NEW_CREATION_DATE = 'new/creation_date';
    const XML_PATH_NEW_DAYS = 'new/days';
    const XML_PATH_MANAGEMENT = 'overlay_management/enable_management';
    const XML_PATH_ENABLE_SMART_CACHE = 'smart_cache/enable_cache_cleaner';
    const DISPLAY_PRODUCT = 'digidirect_productoverlay/display/product';
    const DISPLAY_CATEGORY = 'digidirect_productoverlay/display/category';

    const COMMA_SEPARATOR = ',';

    const MODE_CATEGORY = 'category';
    const MODE_PRODUCT = 'product';

    const PRODUCT_CACHE_KEY = 'catalog_product_';
    const OVERLAY_CACHE_KEY = 'product_overlay_';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_productTypeConfigurable;

    /**
     * File Uploader factory
     *
     * @var \Digidirect\ProductOverlay\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Digidirect\ProductOverlay\Model\ResourceModel\Overlays\Collection
     */
    protected $_overlayCollection;

    /**
     * Mime types
     *
     * @var array
     */
    protected $_mimeTypes = [
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
    ];

    /**
     * Default available image extensions
     *
     * @var array
     */
    protected $_defaultAllowedImageExtensions = [
        'jpg',
        'jpeg',
        'gif',
        'png',
    ];

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Data constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\ProductOverlay\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\ProductOverlay\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->layoutFactory = $layoutFactory;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_collectionFactory = $collectionFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_cacheManager = $cacheManager;
        $this->_httpContext = $httpContext;
        $this->_productRepository = $productRepository ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
    }

    /**
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        if ($storeId) {
            return $this->_scopeConfig->getValue(
                self::MODULE_CONFIG_PREFIX . $path,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->_scopeConfig->getValue(self::MODULE_CONFIG_PREFIX . $path);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $mode
     * @param null|array|\Digidirect\ProductOverlay\Model\ResourceModel\Overlays\Collection $overlays
     * @return array
     */
    public function getProductOverlays(
        \Magento\Catalog\Model\Product $product,
        $mode = self::MODE_CATEGORY,
        $overlays = null
    ) {
        /**
         * @var $overlay \Digidirect\ProductOverlay\Model\Overlays
         */
        if ($overlays === null) {
            $overlays = $this->getOverlayCollection();
        }

        $result = [];
        $applied = false;
        foreach ($overlays as $overlay) {
            if ($overlay->getIsSingle() && $applied) {
                continue;
            }

            $overlay->init($product, $mode);
            if ($overlay->isApplicable()) {
                $applied = true;
                $result[$overlay->getId()] = $overlay;
            } elseif ($overlay->getUseForParent()) {
                switch ($product->getTypeId()) {
                    case Configurable::TYPE_CODE:
                    case Grouped::TYPE_CODE:
                        $usedProds = $this->getUsedProducts($product);
                        foreach ($usedProds as $child) {
                            $overlay->init($child, $mode, $product);
                            if ($overlay->isApplicable()) {
                                $applied = true;
                                $result[$overlay->getId()] = $overlay;
                                break;
                            }
                        }
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $product
     * @param string $mode
     * @return string
     */
    public function renderProductOverlay($product = null, $mode = self::MODE_CATEGORY)
    {
        if (!$this->isEnabledSmartCacheCleaner()) {
            $productId = $product;
            if ($productId instanceof \Magento\Catalog\Model\Product) {
                $productId = $product->getId();
            }

            $productId = $productId !== null ? $productId : '';
            $cacheKey = implode('_', [
                self::OVERLAY_CACHE_KEY,
                $this->_httpContext->getValue(CustomerContext::CONTEXT_GROUP),
                $this->_httpContext->getValue(StoreManagerInterface::CONTEXT_STORE),
                $mode,
                $productId
            ]);

            $overlayHtml = $this->_cacheManager->load($cacheKey);
            if ($overlayHtml !== false) {
                return $overlayHtml;
            }
        }

        $overlayHtml = $this->_renderProductOverlay($product, $mode);

        if (!$this->isEnabledSmartCacheCleaner()) {
            $this->_cacheManager->save($overlayHtml, $cacheKey, [
                self::PRODUCT_CACHE_KEY . $productId,
                BlockCache::TYPE_IDENTIFIER,
                PageCache::TYPE_IDENTIFIER
            ]);
        }

        return $overlayHtml;
    }

    /**
     * @param \Magento\Catalog\Model\Product|int $product
     * @param string $mode
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _renderProductOverlay(
        $product = null,
        $mode = self::MODE_CATEGORY
    ) {
        $html = '';
        if (empty($product)) {
            return $html;
        }

        if (!$product instanceof \Magento\Catalog\Model\Product) {
            try {
                $product = $this->_productRepository->getById($product);
            } catch (LocalizedException $e) {
                return $html;
            }
        }

        $applicableInfo = [];
        $applied = false;
        /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
        foreach ($this->getOverlayCollection() as $overlay) {
            if ($overlay->getIsSingle() && $applied) {
                continue;
            }

            $overlay->init($product, $mode);
            if ($overlay->isApplicable()) {
                $applied = true;
                $overlay->setHideInConfigurable(false);
                if (in_array($product->getTypeId(), [Configurable::TYPE_CODE, Grouped::TYPE_CODE])) {
                    $overlay->setHideInConfigurable(!$overlay->getUseForParent());
                    $applicableInfo[$overlay->getId()][] = $product->getId();

                    $usedProds = $this->getUsedProducts($product);
                    foreach ($usedProds as $child) {
                        $overlay->init($child, $mode, $product);
                        if ($overlay->isApplicable()) {
                            $applied = true;
                            $applicableInfo[$overlay->getId()][] = $child->getId();
                        }
                    }
                } else {
                    $applicableInfo[$overlay->getId()][] = $product->getId();
                }
            } elseif (in_array($product->getTypeId(), [Configurable::TYPE_CODE, Grouped::TYPE_CODE])) {
                if ($product->getTypeId() == Grouped::TYPE_CODE && !$overlay->getUseForParent()) {
                    continue;
                }

                $usedProds = $this->getUsedProducts($product);
                foreach ($usedProds as $child) {
                    $overlay->init($child, $mode, $product);
                    if ($overlay->isApplicable()) {
                        $applied = true;
                        $applicableInfo[$overlay->getId()][] = $child->getId();
                    }
                }
            }
        }

        return $this->_generateSingleHtml($product, $applicableInfo);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $mode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getStockLabels(\Magento\Catalog\Model\Product $product, $mode)
    {
        $result = [];
        $applied = false;

        /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
        foreach ($this->getOverlayCollection() as $overlay) {
            if ($overlay->getIsSingle() && $applied) {
                continue;
            }
            if (($product->getTypeId() != Configurable::TYPE_CODE && $product->getTypeId() != Grouped::TYPE_CODE)) {
                $overlay->init($product, $mode);
                $overlay->setSkipCheckImage(true);
                $label = $overlay->getStockLabel();
                if ($label && $overlay->isApplicable()) {
                    $result[] = $label;
                }
            } elseif (($product->getTypeId() == Configurable::TYPE_CODE
                || $product->getTypeId() == Grouped::TYPE_CODE)) {
                $usedProds = $this->getUsedProducts($product);
                foreach ($usedProds as $child) {
                    $overlay->init($child, $mode, $product);
                    $overlay->setSkipCheckImage(true);
                    $label = $overlay->getStockLabel();
                    if ($label && $overlay->getUseForParent() && $overlay->isApplicable()) {
                        $result[$overlay->getId()] = $label;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getStockLabelsJson()
    {
        $product = $this->_registry->registry('product');
        $labels = [];
        if ($product) {
            $labels = $this->getStockLabels($product, self::MODE_PRODUCT);
        }
        return json_encode($labels);
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @return string
     */
    public function getApplicableSimples(\Magento\Catalog\Model\Product $product = null)
    {
        if (!$product) {
            $product = $this->_registry->registry('product');
        }

        $result = '';
        if ($product) {
            if (!$this->isEnabledSmartCacheCleaner()) {
                $cacheKey = implode('_', [
                    self::OVERLAY_CACHE_KEY,
                    'APPLICABLE_SIMPLES',
                    $this->_httpContext->getValue(CustomerContext::CONTEXT_GROUP),
                    $this->_httpContext->getValue(StoreManagerInterface::CONTEXT_STORE),
                    $product->getId()
                ]);

                if ($result = $this->_cacheManager->load($cacheKey)) {
                    return $result;
                }
            }

            $result = $this->_getApplicableSimples($product);

            if (!$this->isEnabledSmartCacheCleaner()) {
                $this->_cacheManager->save($result, $cacheKey, [
                    self::PRODUCT_CACHE_KEY . $product->getId(),
                    BlockCache::TYPE_IDENTIFIER,
                    PageCache::TYPE_IDENTIFIER
                ]);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getApplicableSimples(\Magento\Catalog\Model\Product $product)
    {
        $applicableSimple = [];
        $applicableSimpleArray = $this->getApplicableSimplesArray($product);

        foreach ($applicableSimpleArray as $childId => $overlays) {
            foreach ($overlays as $overlay) {
                $applicableSimple[$childId][] = [
                    'overlay_id' => $overlay->getId(),
                    'use_for_parent' => $overlay->getUseForParent(),
                    'stock_label' => $overlay->getStockLabel()
                ];
            }
        }
        if (!empty($applicableSimple)) {
            return json_encode($applicableSimple);
        }
        return '';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getApplicableSimplesArray(\Magento\Catalog\Model\Product $product)
    {
        $applicableSimple = [];
        $mode = 'product';
        /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
        foreach ($this->getOverlayCollection() as $overlay) {
            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped') {
                $usedProds = $this->getUsedProducts($product);
                foreach ($usedProds as $child) {
                    $overlay->init($child, $mode, $product);
                    $stockLabel = $overlay->getStockLabel();
                    if (!empty($stockLabel)) {
                        $overlay->setSkipCheckImage(true);
                    }

                    if ($overlay->isApplicable()) {
                        $applicableSimple[$child->getId()][] = $overlay;
                    }
                }
            }
        }
        return $applicableSimple;
    }

    /**
     * @return \Digidirect\ProductOverlay\Model\ResourceModel\Overlays\Collection
     */
    public function getOverlayCollection()
    {
        if ($this->_overlayCollection === null) {
            /** @var \Digidirect\ProductOverlay\Model\ResourceModel\Overlays\Collection $collection */
            $this->_overlayCollection = $this->_collectionFactory->create();
            $this->_overlayCollection
                ->addStatusFilter(Status::STATUS_ENABLED)
                ->addStoreFilter($this->_storeManager->getStore()->getId());
        }
        return $this->_overlayCollection;
    }

    /**
     * Generate block with overlay configuration
     *
     * @param \Digidirect\ProductOverlay\Model\Overlays $overlay
     * @return mixed
     */
    protected function _generateHtml(\Digidirect\ProductOverlay\Model\Overlays $overlay)
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            'Digidirect\ProductOverlay\Block\Overlay',
            'digidirect.productoverlay',
            ['data' => []]
        );
        $html = $block->setOverlay($overlay)->toHtml();

        return $html;
    }

    /**
     * Generate block with overlay configuration by product with children
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $applicableInfo
     * @return string
     */
    protected function _generateSingleHtml(\Magento\Catalog\Model\Product $product, array $applicableInfo)
    {
        $html = '';
        $overlaysCollection = $this->getOverlayCollection();
        foreach ($applicableInfo as $overlayId => $products) {
            $overlay = $overlaysCollection->getItemById($overlayId);
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(
                'Digidirect\ProductOverlay\Block\Overlay',
                'digidirect.productoverlay',
                ['data' => [
                    'product' => $product,
                    'applicable_product_ids' => $products,
                ]]
            );
            $html .= $block->setOverlay($overlay)->toHtml();
        }
        return $html;
    }

    /**
     * Return url with magento path
     *
     * @param string $name
     * @return string
     */
    public function getImageUrl($name)
    {
        $path = $this->getOverlayImagePath();
        if (file_exists($path . $name) && $name != "") {
            $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $path . 'digidirect/productoverlay/' . $name;
        }

        return '';
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getUsedProducts(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getTypeId() == 'configurable') {
            return $this->_productTypeConfigurable->getUsedProducts($product);
        } else { // product is grouped
            return $product->getTypeInstance(true)->getAssociatedProducts($product);
        }
    }

    /**
     * @param null|int $storeId
     * @return array
     */
    public function getValidMimeTypes($storeId = null)
    {
        $chosenTypes = [];
        $typesFromConfig = $this->getImageTypesByConfig(self::XML_PATH_GENERAL_IMAGE_MIME_TYPE, $storeId);
        foreach ($typesFromConfig as $t) {
            if ($key = array_search($t, $this->_mimeTypes)) {
                $chosenTypes[] = $this->_mimeTypes[$key];
            }
        }

        return $chosenTypes ? $chosenTypes : $this->_mimeTypes;
    }

    /**
     * @param null|int $storeId
     * @return array
     */
    public function getAllowedImageExtensions($storeId = null)
    {
        $typesFromConfig = $this->getImageTypesByConfig(self::XML_PATH_GENERAL_IMAGE_TYPE, $storeId);

        return $typesFromConfig ? $typesFromConfig : $this->_defaultAllowedImageExtensions;
    }

    /**
     * @param string $field
     * @return \Digidirect\ProductOverlay\Model\File\Uploader|null
     */
    public function getFileForUpload($field)
    {
        try {
            /** @var $file \Digidirect\ProductOverlay\Model\File\Uploader */
            $file = $this->_fileUploaderFactory->create(['fileId' => $field]);
        } catch (\Exception $e) {
            $file = null;
        }

        return $file;
    }

    /**
     * @return string
     */
    public function getOverlayImagePath()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('digidirect/productoverlay/');
    }

    /**
     * @param string $config
     * @param null|int $storeId
     * @return array
     */
    public function getImageTypesByConfig($config, $storeId = null)
    {
        $imageTypes = [];
        $typesString = $this->getModuleConfig($config, $storeId);

        if ($typesString) {
            $types = explode(self::COMMA_SEPARATOR, $typesString);
            foreach ($types as $type) {
                if ($type = trim($type)) {
                    $imageTypes[] = mb_strtolower($type);
                }
            }
        }

        return array_unique($imageTypes);
    }

    /**
     * @param string $time
     * @return string
     */
    public function formatTime($time)
    {
        return substr($time, 0, 5);
    }

    /**
     * @return bool
     */
    public function isEnabledOverlayManagement()
    {
        return $this->scopeConfig->isSetFlag(
            self::MODULE_CONFIG_PREFIX . self::XML_PATH_MANAGEMENT
        );
    }

    /**
     * @return bool
     */
    public function isEnabledSmartCacheCleaner()
    {
        return $this->scopeConfig->isSetFlag(
            self::MODULE_CONFIG_PREFIX . self::XML_PATH_ENABLE_SMART_CACHE
        );
    }

    /**
     * @param string $mode
     * @return mixed
     */
    public function getContainerPath($mode)
    {
        if ($mode == Overlays::POSITION_MODE_CATEGORY) {
            $path = $this->scopeConfig->getValue(self::DISPLAY_CATEGORY, ScopeInterface::SCOPE_STORES);
        } else {
            $path = $this->scopeConfig->getValue(self::DISPLAY_PRODUCT, ScopeInterface::SCOPE_STORES);
        }

        return $path;
    }
}
