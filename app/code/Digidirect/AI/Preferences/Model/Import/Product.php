<?php

namespace Digidirect\AI\Preferences\Model\Import;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\CatalogImportExport\Model\Export\Product as ProductExport;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Store\Model\Store;

/**
 * Class Product
 *
 * @property \Digidirect\AI\Preferences\Model\Import\Product\CategoryProcessor $categoryProcessor
 * @package Digidirect\AI\Model\Import
 */
class Product extends \Magento\CatalogImportExport\Model\Import\Product
{
    const YOUTUBE_API_LINK = 'https://www.googleapis.com/youtube/v3/videos?';

    const CATEGORIES_BEHAVIOR_APPEND = 'append';
    const CATEGORIES_BEHAVIOR_REPLACE = 'replace';

    /**
     * Column names that holds images files names.
     *
     * Note: the order of array items has a value in order to properly set 'position' value
     * of media gallery items.
     *
     * @var string[]
     */
    protected $_imagesArrayKeys = null;

    /**
     * @var \Magento\Framework\HTTP\LaminasClient
     */
    protected $httpClient;

    /**
     * @var \Magento\ProductVideo\Helper\Media
     */
    protected $mediaHelper;

    /**
     * @var Decoder
     */
    protected $jsonDecoder;

    /**
     * Product entity link field
     *
     * @var string
     */
    protected $productEntityLinkField;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Container for filesystem object.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Catalog config.
     *
     * @var CatalogConfig
     */
    protected $catalogConfig;

    /**
     * @var string
     */
    protected $productStoreId;

    /**
     * Product constructor.
     *
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ImportExport\Model\Import\Config $importConfig
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory
     * @param \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac
     * @param DateTime\TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     * @param \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor
     * @param \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor
     * @param \Magento\CatalogImportExport\Model\Import\Product\Validator $validator
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Framework\HTTP\LaminasClient $httpClient
     * @param \Magento\ProductVideo\Helper\Media $mediaHelper
     * @param StoreManagerInterface $storeManager
     * @param Decoder $jsonDecoder
     * @param array $data
     * @param array $dateAttrCodes
     * @param CatalogConfig|null $catalogConfig
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Framework\HTTP\LaminasClient $httpClient,
        \Magento\ProductVideo\Helper\Media $mediaHelper,
        StoreManagerInterface $storeManager,
        Decoder $jsonDecoder,
        array $data = [],
        array $dateAttrCodes = [],
        CatalogConfig $catalogConfig = null
    ) {
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator,
            $eventManager,
            $stockRegistry,
            $stockConfiguration,
            $stockStateProvider,
            $catalogData,
            $importConfig,
            $resourceFactory,
            $optionFactory,
            $setColFactory,
            $productTypeFactory,
            $linkFactory,
            $proxyProdFactory,
            $uploaderFactory,
            $filesystem,
            $stockResItemFac,
            $localeDate,
            $dateTime,
            $logger,
            $indexerRegistry,
            $storeResolver,
            $skuProcessor,
            $categoryProcessor,
            $validator,
            $objectRelationProcessor,
            $transactionManager,
            $taxClassProcessor,
            $scopeConfig,
            $productUrl,
            $data,
            $dateAttrCodes,
            $catalogConfig
        );
        $this->filesystem = $filesystem;
        $this->httpClient = $httpClient;
        $this->mediaHelper = $mediaHelper;
        $this->jsonDecoder = $jsonDecoder;
        $this->storeManager = $storeManager;
        $this->catalogConfig = $catalogConfig ?: ObjectManager::getInstance()->get(CatalogConfig::class);
    }

    /**
     * Whether a url key is needed to be change.
     *
     * @param array $rowData
     * @return bool
     */
    protected function isNeedToChangeUrlKey(array $rowData)
    {
        $urlKey = $this->getUrlKey($rowData);
        $productExists = $this->isSkuExist($rowData[self::COL_SKU]);
        $markedToEraseUrlKey = isset($rowData[self::URL_KEY]);
        // The product isn't new and the url key index wasn't marked for change.
        if (!$urlKey && $productExists && !$markedToEraseUrlKey) {
            // Seems there is no need to change the url key
            return false;
        }

        return true;
    }

    /**
     * Gather and save information about product entities.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws LocalizedException
     */
    protected function _saveProducts()
    {
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;
        $entityLinkField = $this->getProductEntityLinkField();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $this->websitesCache = [];
            $this->categoriesCache = [];
            $tierPrices = [];
            $mediaGallery = [];
            $labelsForUpdate = [];
            $uploadedImages = [];
            $previousType = null;
            $prevAttributeSet = null;
            $existingImages = $this->getExistingImages($bunch);

            foreach ($bunch as $rowNum => $rowData) {
                //dispatch event to allow modify row data
                $transportObject = new \Magento\Framework\DataObject(['row_data' => $rowData]);
                $this->_eventManager->dispatch(
                    'digidirect_product_import_row_process_before',
                    ['transport_object' => $transportObject, 'product_import' => $this]
                );
                $rowData = $transportObject->getRowData();

                // reset category processor's failed categories array
                $this->categoryProcessor->clearFailedCategories();
                if (!empty($rowData[self::COL_STORE])) {
                    $this->productStoreId = $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE]);
                }
                $this->categoryProcessor->initCategories($this->productStoreId);

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                $urlKey = $this->getUrlKey($rowData);
                if (!empty($rowData[self::URL_KEY])) {
                    // If url_key column and its value were in the CSV file
                    $rowData[self::URL_KEY] = $urlKey;
                } elseif ($this->isNeedToChangeUrlKey($rowData)) {
                    // If url_key column was empty or even not declared in the CSV file but by the rules it is need to
                    // be setteed. In case when url_key is generating from name column we have to ensure that the bunch
                    // of products will pass for the event with url_key column.
                    $bunch[$rowNum][self::URL_KEY] = $rowData[self::URL_KEY] = $urlKey;
                }

                $rowSku = $rowData[self::COL_SKU];

                if (null === $rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                } elseif (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                    $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                }

                // 1. Entity phase
                if ($this->isSkuExist($rowSku)) {
                    // existing row
                    if (isset($rowData['attribute_set_code'])) {
                        $attributeSetId = $this->catalogConfig->getAttributeSetId(
                            $this->getEntityTypeId(),
                            $rowData['attribute_set_code']
                        );

                        // wrong attribute_set_code was received
                        if (!$attributeSetId) {
                            throw new LocalizedException(
                                __(
                                    'Wrong attribute set code "%1", please correct it and try again.',
                                    $rowData['attribute_set_code']
                                )
                            );
                        }
                    } else {
                        $attributeSetId = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    }

                    $entityRowsUp[] = [
                        'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        'attribute_set_id' => $attributeSetId,
                        $entityLinkField => $this->getExistingSku($rowSku)[$entityLinkField]
                    ];
                } else {
                    if (!$productLimit || $productsQty < $productLimit) {
                        $entityRowsIn[strtolower($rowSku)] = [
                            'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                            'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                            'sku' => $rowSku,
                            'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                            'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                            'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        ];
                        $productsQty++;
                    } else {
                        $rowSku = null;
                        // sign for child rows to be skipped
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                }

                if (!array_key_exists($rowSku, $this->websitesCache)) {
                    $this->websitesCache[$rowSku] = [];
                }
                // 2. Product-to-Website phase
                if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                    $websiteCodes = explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]);
                    foreach ($websiteCodes as $websiteCode) {
                        $websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCode);
                        $this->websitesCache[$rowSku][$websiteId] = true;
                    }
                }

                // 3. Categories phase
                if (!array_key_exists($rowSku, $this->categoriesCache)) {
                    $this->categoriesCache[$rowSku] = [];
                }
                $rowData['rowNum'] = $rowNum;
                $categoryIds = $this->processRowCategories($rowData);
                foreach ($categoryIds as $id) {
                    $this->categoriesCache[$rowSku][$id] = true;
                }
                unset($rowData['rowNum']);

                // 4.1. Tier prices phase
                if (!empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty' => $rowData['_tier_price_qty'],
                        'value' => $rowData['_tier_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_tier_price_website'] ||
                        $priceIsGlobal ? 0 : $this->storeResolver->getWebsiteCodeToId($rowData['_tier_price_website']),
                    ];
                }

                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                // 5. Media gallery phase
                $disabledImages = [];
                list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
                $storeId = !empty($rowData[self::COL_STORE])
                    ? $this->getStoreIdByCode($rowData[self::COL_STORE])
                    : Store::DEFAULT_STORE_ID;
                if (isset($rowData['_media_is_disabled']) && strlen(trim($rowData['_media_is_disabled']))) {
                    $disabledImages = array_flip(
                        explode($this->getMultipleValueSeparator(), $rowData['_media_is_disabled'])
                    );
                    if (empty($rowImages)) {
                        foreach (array_keys($disabledImages) as $disabledImage) {
                            $rowImages[self::COL_MEDIA_IMAGE][] = $disabledImage;
                        }
                    }
                }
                $rowData[self::COL_MEDIA_IMAGE] = [];

                /*
                 * Note: to avoid problems with undefined sorting, the value of media gallery items positions
                 * must be unique in scope of one product.
                 */
                $position = 0;
                foreach ($rowImages as $column => $columnImages) {
                    foreach ($columnImages as $columnImageKey => $columnImage) {
                        if (!isset($uploadedImages[$columnImage])) {
                            // added ability import youtube videos
                            if ($this->isYoutubeHash($columnImage)) {
                                $videoData = $this->getYoutubeVideoData($columnImage);
                                if (!$videoData) {
                                    $this->addRowError(__('Youtube hash %1 is wrong', $columnImage), $rowNum);
                                    continue;
                                }
                                $thumbnail = $videoData['thumbnails']['standard']['url'] ?? '';
                                $uploadedFile = $this->uploadMediaFiles(trim($thumbnail), true);
                                $mediaGallery[$storeId][$rowSku][$uploadedFile] = [
                                    'attribute_id' => $this->getMediaGalleryAttributeId(),
                                    'label' => $rowLabels[$column][$columnImageKey] ?? '',
                                    'position' => ++$position,
                                    'disabled' => isset($disabledImages[$columnImage]) ? '1' : '0',
                                    'value' => $uploadedFile,
                                    'videoInfo' => [
                                        'img' => $uploadedFile,
                                        'link' => 'https://www.youtube.com/watch?v=' . $columnImage,
                                        'title' => $videoData['title'],
                                        'description' => $videoData['description'],
                                    ],
                                ];
                            } else {
                                $uploadedFile = $this->uploadMediaFiles($columnImage, true);
                                $uploadedFile = $uploadedFile ?: $this->getSystemFile($columnImage);
                                if ($uploadedFile) {
                                    $uploadedImages[$columnImage] = $uploadedFile;
                                } else {
                                    $this->addRowError(
                                        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                        $rowNum,
                                        null,
                                        null,
                                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                    );
                                }
                            }
                        } else {
                            $uploadedFile = $uploadedImages[$columnImage];
                        }

                        if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                            $rowData[$column] = $uploadedFile;
                        }

                        if ($uploadedFile && !isset($mediaGallery[$storeId][$rowSku][$uploadedFile])) {
                            if (isset($existingImages[$rowSku][$uploadedFile])) {
                                if (isset($rowLabels[$column][$columnImageKey])
                                    && $rowLabels[$column][$columnImageKey]
                                    != $existingImages[$rowSku][$uploadedFile]['label']
                                ) {
                                    $labelsForUpdate[] = [
                                        'label' => $rowLabels[$column][$columnImageKey],
                                        'imageData' => $existingImages[$rowSku][$uploadedFile],
                                    ];
                                }
                            } else {
                                if ($column == self::COL_MEDIA_IMAGE) {
                                    $rowData[$column][] = $uploadedFile;
                                }
                                $mediaGallery[$storeId][$rowSku][$uploadedFile] = [
                                    'attribute_id' => $this->getMediaGalleryAttributeId(),
                                    'label' => isset($rowLabels[$column][$columnImageKey])
                                        ? $rowLabels[$column][$columnImageKey] : '',
                                    'position' => ++$position,
                                    'disabled' => isset($disabledImages[$columnImage]) ? '1' : '0',
                                    'value' => $uploadedFile,
                                ];
                            }
                        }
                    }
                }

                // 6. Attributes phase
                $rowStore = (self::SCOPE_STORE == $rowScope)
                    ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                    : 0;
                $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                if (null !== $productType) {
                    $previousType = $productType;
                }
                if (isset($rowData[self::COL_ATTR_SET])) {
                    $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                }
                if (self::SCOPE_NULL == $rowScope) {
                    // for multiselect attributes only
                    if (null !== $prevAttributeSet) {
                        $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                    }
                    if (null === $productType && null !== $previousType) {
                        $productType = $previousType;
                    }
                    if (null === $productType) {
                        continue;
                    }
                }

                $productTypeModel = $this->_productTypeModels[$productType];
                if (!empty($rowData['tax_class_name'])) {
                    $rowData['tax_class_id'] =
                        $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                }

                $behaviorParam = $this->_parameters['behavior'] ?? null;

                if (($this->getBehavior() == Import::BEHAVIOR_APPEND && $behaviorParam !== Import::BEHAVIOR_ADD_UPDATE)
                    || empty($rowData[self::COL_SKU])
                ) {
                    $rowData = $productTypeModel->clearEmptyData($rowData);
                }

                $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    !$this->isSkuExist($rowSku)
                );
                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $this->retrieveAttributeByCode($attrCode);

                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds = [0];

                    if ('datetime' == $attribute->getBackendType()
                        && (
                            in_array($attribute->getAttributeCode(), $this->dateAttrCodes)
                            || $attribute->getIsUserDefined()
                        )
                    ) {
                        $attrValue = $this->dateTime->formatDate($attrValue, false);
                    } elseif ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            $this->_localeDate->date($attrValue)->getTimestamp()
                        );
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                        if (!$this->isSkuExist($rowSku)) {
                            $storeIds[] = 0;
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
            }

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    unset($bunch[$rowNum]);
                }
            }

            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_before',
                ['adapter' => $this, 'bunch' => $bunch]
            );

            $this->saveProductEntity(
                $entityRowsIn,
                $entityRowsUp
            )->_saveProductWebsites(
                $this->websitesCache
            )->_saveProductCategories(
                $this->categoriesCache
            )->_saveProductTierPrices(
                $tierPrices
            )->_saveMediaGallery(
                $mediaGallery
            )->_saveProductAttributes(
                $attributes
            )->updateMediaGalleryLabels(
                $labelsForUpdate
            );

            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $bunch]
            );
        }
        return $this;
    }

    /**
     * Rewritten to cast $sku to string
     *
     * @inheritdoc
     */
    protected function _saveProductAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $skuData) {
            $tableData = [];
            foreach ($skuData as $sku => $attributes) {
                $linkId = $this->_connection->fetchOne(
                    $this->_connection->select()
                        ->from(
                            $this->getResource()->getTable('catalog_product_entity'),
                            $this->getProductEntityLinkField()
                        )
                        ->where('sku = ?', (string)$sku)
                );

                foreach ($attributes as $attributeId => $storeValues) {
                    foreach ($storeValues as $storeId => $storeValue) {
                        $tableData[] = [
                            $this->getProductEntityLinkField() => $linkId,
                            'attribute_id' => $attributeId,
                            'store_id' => $storeId,
                            'value' => $storeValue,
                        ];
                    }
                }
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
        }

        return $this;
    }

    /**
     * Rewritten because this private method is used in _saveProductAttributes
     *
     * @inheritdoc
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }

        return $this->productEntityLinkField;
    }

    /**
     * @return array|\string[]
     */
    protected function getImagesArrayKeys()
    {
        if (empty($this->_imagesArrayKeys)) {
            $select = $this->getConnection()->select()
                ->from(['ea' => 'eav_attribute'], 'attribute_code')
                ->join(
                    ['eet' => 'eav_entity_type'],
                    "ea.entity_type_id = eet.entity_type_id AND eet.entity_type_code = 'catalog_product'",
                    []
                )
                ->where("ea.frontend_input = 'media_image'");
            $this->_imagesArrayKeys = array_merge(
                $this->getConnection()->fetchCol($select),
                ['_media_image', 'additional_videos']
            );
        }

        return $this->_imagesArrayKeys;
    }

    /**
     * @param array $rowData
     * @return array
     */
    public function getImagesFromRow(array $rowData)
    {
        $images = [];
        $labels = [];
        foreach ($this->getImagesArrayKeys() as $column) {
            $images[$column] = [];
            $labels[$column] = [];
            if (!empty($rowData[$column])) {
                $images[$column] = array_unique(
                    explode($this->getMultipleValueSeparator(), $rowData[$column])
                );
            }

            if (!empty($rowData[$column . '_label'])) {
                $labels[$column] = explode($this->getMultipleValueSeparator(), $rowData[$column . '_label']);

                if (count($labels[$column]) > count($images[$column])) {
                    $labels[$column] = array_slice($labels[$column], 0, count($images[$column]));
                }
            }
        }

        return $this->getImagesFromRowAdditionalAssignment($images, $labels);
    }

    /**
     * Checking of $images and $labels arrays and adding missing labels to $labels array
     *
     * @param array $images
     * @param array $labels
     * @return array
     */
    protected function getImagesFromRowAdditionalAssignment(array $images, array $labels)
    {
        $mediaKey = ProductExport::COL_MEDIA_IMAGE;
        if (isset($images[$mediaKey]) && isset($labels[$mediaKey])) {
            $additionalImages = $images[$mediaKey];
            $additionalImageLabels = $labels[$mediaKey];
            foreach ($images as $imgType => $imgList) {
                if ($imgType == $mediaKey) {
                    continue;
                }
                foreach ($imgList as $imgkey => $img) {
                    if (!isset($labels[$imgType][$imgkey]) && in_array($img, $additionalImages)) {
                        $key = array_search($img, $additionalImages);
                        if (isset($additionalImageLabels[$key])) {
                            $labels[$imgType][$imgkey] = $additionalImageLabels[$key];
                            unset($additionalImages[$key], $additionalImageLabels[$key]);
                        }
                    }
                }
            }
        }

        return [$images, $labels];
    }

    /**
     * @see \Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor::saveMediaGallery()
     * @param array $mediaGalleryData
     * @return $this
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        if (empty($mediaGalleryData)) {
            return $this;
        }
        $this->initMediaGalleryResources();
        $mediaGalleryDataGlobal = array_replace_recursive(...$mediaGalleryData);
        $imageNames = [];
        $multiInsertData = [];
        $valueToProductId = [];
        foreach ($mediaGalleryDataGlobal as $productSku => $mediaGalleryRows) {
            $productId = $this->skuProcessor->getNewSku($productSku)[$this->getProductEntityLinkField()];
            $insertedGalleryImgs = [];
            foreach ($mediaGalleryRows as $insertValue) {
                if (!in_array($insertValue['value'], $insertedGalleryImgs)) {
                    $mediaType = empty($insertValue['videoInfo']) ?
                        ImageEntryConverter::MEDIA_TYPE_CODE : ExternalVideoEntryConverter::MEDIA_TYPE_CODE;
                    $valueArr = [
                        'attribute_id' => $insertValue['attribute_id'],
                        'value' => $insertValue['value'],
                        'media_type' => $mediaType,
                    ];
                    $valueToProductId[$insertValue['value']][] = $productId;
                    $imageNames[] = $insertValue['value'];
                    $multiInsertData[] = $valueArr;
                    $insertedGalleryImgs[] = $insertValue['value'];
                }
            }
        }
        $oldMediaValues = $this->_connection->fetchAssoc(
            $this->_connection->select()->from($this->mediaGalleryTableName, ['value_id', 'value'])
                ->where('value IN (?)', $imageNames)
        );
        $this->_connection->insertOnDuplicate($this->mediaGalleryTableName, $multiInsertData);
        $newMediaSelect = $this->_connection->select()->from($this->mediaGalleryTableName, ['value_id', 'value'])
            ->where('value IN (?)', $imageNames);
        if (array_keys($oldMediaValues)) {
            $newMediaSelect->where('value_id NOT IN (?)', array_keys($oldMediaValues));
        }
        $newMediaValues = $this->_connection->fetchAssoc($newMediaSelect);
        foreach ($mediaGalleryData as $storeId => $storeMediaGalleryData) {
            $this->processMediaPerStore((int)$storeId, $storeMediaGalleryData, $newMediaValues, $valueToProductId);
        }
        return $this;
    }

    /**
     * @see \Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor::processMediaPerStore()
     * Save media gallery data per store.
     *
     * @param int $storeId
     * @param array $mediaGalleryData
     * @param array $newMediaValues
     * @param array $valueToProductId
     * @return void
     */
    protected function processMediaPerStore(
        int $storeId,
        array $mediaGalleryData,
        array $newMediaValues,
        array $valueToProductId
    ) {
        $multiInsertData = [];
        $dataForSkinnyTable = [];
        $dataVideoInsertData = [];
        foreach ($mediaGalleryData as $mediaGalleryRows) {
            foreach ($mediaGalleryRows as $insertValue) {
                foreach ($newMediaValues as $valueId => $values) {
                    if ($values['value'] == $insertValue['value']) {
                        $insertValue['value_id'] = $valueId;
                        $insertValue[$this->getProductEntityLinkField()]
                            = array_shift($valueToProductId[$values['value']]);
                        unset($newMediaValues[$valueId]);
                        break;
                    }
                }
                if (isset($insertValue['value_id'])) {
                    $valueArr = [
                        'value_id' => $insertValue['value_id'],
                        'store_id' => $storeId,
                        $this->getProductEntityLinkField() => $insertValue[$this->getProductEntityLinkField()],
                        'label' => $insertValue['label'],
                        'position' => $insertValue['position'],
                        'disabled' => $insertValue['disabled'],
                    ];
                    $multiInsertData[] = $valueArr;
                    $dataForSkinnyTable[] = [
                        'value_id' => $insertValue['value_id'],
                        $this->getProductEntityLinkField() => $insertValue[$this->getProductEntityLinkField()],
                    ];
                    if (!empty($insertValue['videoInfo']['link'])) {
                        $dataVideoInsertData[] = [
                            'value_id' => $insertValue['value_id'],
                            'store_id' => $storeId,
                            'url' => $insertValue['videoInfo']['link'],
                            'title' => $insertValue['videoInfo']['title'],
                            'description' => $insertValue['videoInfo']['description'],
                        ];
                    }
                }
            }
        }
        try {
            $this->_connection->insertOnDuplicate(
                $this->mediaGalleryValueTableName,
                $multiInsertData,
                ['value_id', 'store_id', $this->getProductEntityLinkField(), 'label', 'position', 'disabled']
            );
            $this->_connection->insertOnDuplicate(
                $this->mediaGalleryEntityToValueTableName,
                $dataForSkinnyTable,
                ['value_id']
            );
            if (!empty($dataVideoInsertData)) {
                $this->_connection->insertOnDuplicate(
                    $this->getResource()->getTable('catalog_product_entity_media_gallery_value_video'),
                    $dataVideoInsertData
                );
            }
        } catch (\Exception $e) {
            $this->_connection->delete(
                $this->mediaGalleryTableName,
                $this->_connection->quoteInto('value_id IN (?)', $newMediaValues)
            );
        }
    }

    /**
     * @param string $link
     * @return boolean
     */
    protected function isYoutubeHash($link)
    {
        return (bool)preg_match('#^(\w|-)+$#', $link);
    }

    /**
     * @param string $hash
     * @return null
     */
    protected function getYoutubeVideoData($hash)
    {
        $youtubeApiKey = $this->mediaHelper->getYouTubeApiKey();
        $url = self::YOUTUBE_API_LINK . http_build_query([
                'id' => $hash,
                'part' => 'snippet,contentDetails,statistics,status',
                'key' => $youtubeApiKey,
            ]);
        $this->httpClient->setUri($url);
        $this->httpClient->setMethod(\Zend_Http_Client::GET);
        $this->httpClient->setHeaders('Referer', $this->storeManager->getStore()->getBaseUrl());
        $response = $this->httpClient->request();
        $videoData = $this->jsonDecoder->decode($response->getBody())['items'][0]['snippet']
            ?? null;
        return $videoData;
    }

    /**
     * Try to find file by it's path.
     *
     * @param string $fileName
     * @return string
     */
    protected function getSystemFile($fileName)
    {
        $filePath = 'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . $fileName;
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $read */
        $read = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        return $read->isExist($filePath) && $read->isReadable($filePath) ? $fileName : '';
    }

    /**
     * Check if product exists for specified SKU
     *
     * @param string $sku
     * @return bool
     */
    protected function isSkuExist($sku)
    {
        $sku = strtolower($sku);
        return isset($this->_oldSku[$sku]);
    }

    /**
     * Get existing product data for specified SKU
     *
     * @param string $sku
     * @return array
     */
    protected function getExistingSku($sku)
    {
        return $this->_oldSku[strtolower($sku)];
    }

    /**
     * Update media gallery labels
     *
     * @param array $labels
     * @return void
     */
    protected function updateMediaGalleryLabels(array $labels)
    {
        if (empty($labels)) {
            return;
        }

        $this->initMediaGalleryResources();
        $insertData = [];
        foreach ($labels as $label) {
            $imageData = $label['imageData'];

            if ($imageData['label'] === null) {
                $insertData[] = [
                    'label' => $label['label'],
                    $this->getProductEntityLinkField() => $imageData[$this->getProductEntityLinkField()],
                    'value_id' => $imageData['value_id'],
                    'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ];
            } else {
                $this->_connection->update(
                    $this->mediaGalleryValueTableName,
                    [
                        'label' => $label['label'],
                    ],
                    [
                        $this->getProductEntityLinkField() . ' = ?' => $imageData[$this->getProductEntityLinkField()],
                        'value_id = ?' => $imageData['value_id'],
                        'store_id = ?' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                );
            }
        }

        if (!empty($insertData)) {
            $this->_connection->insertMultiple(
                $this->mediaGalleryValueTableName,
                $insertData
            );
        }
    }

    /**
     * Save product categories.
     *
     * @param array $categoriesData
     * @return $this
     */
    protected function _saveProductCategories(array $categoriesData)
    {
        $categoriesBehavior = $this->getCategoriesBehavior();
        if ($categoriesBehavior === null) {
            return parent::_saveProductCategories($categoriesData);
        }

        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductCategoryTable();
        }

        if ($categoriesData) {
            $categoriesIn = [];
            $delProductId = [];

            $positions = $this->getCategoryProductPositions($categoriesData, $tableName);
            foreach ($categoriesData as $delSku => $categories) {
                $productId = $this->skuProcessor->getNewSku($delSku)['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($categories) as $categoryId) {
                    $categoriesIn[] = [
                        'product_id' => $productId,
                        'category_id' => $categoryId,
                        'position' => $positions[$productId][$categoryId] ?? 1
                    ];
                }
            }
            if (self::CATEGORIES_BEHAVIOR_REPLACE === $categoriesBehavior) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('product_id IN (?)', $delProductId)
                );
            }
            if ($categoriesIn) {
                $this->_connection->insertOnDuplicate($tableName, $categoriesIn, ['product_id', 'category_id']);
            }
        }
        return $this;
    }

    /**
     * @return null|string
     */
    protected function getCategoriesBehavior()
    {
        $params = $this->getParameters();
        return $params['categories_behavior'] ?? null;
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function processRowCategories($rowData)
    {
        $categoriesString = empty($rowData[self::COL_CATEGORY]) ? '' : $rowData[self::COL_CATEGORY];
        $categoryIds = [];
        if (!empty($categoriesString)) {
            $categoryIds = $this->categoryProcessor->upsertCategories(
                $categoriesString,
                $this->getMultipleValueSeparator(),
                $this->productStoreId
            );
            foreach ($this->categoryProcessor->getFailedCategories() as $error) {
                $this->errorAggregator->addError(
                    AbstractEntity::ERROR_CODE_CATEGORY_NOT_VALID,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL,
                    $rowData['rowNum'],
                    self::COL_CATEGORY,
                    __('Category "%1" has not been created.', $error['category'])
                    . ' ' . $error['exception']->getMessage()
                );
            }
        }
        return $categoryIds;
    }

    /**
     * @param array $categoriesData
     * @param string $tableName
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getCategoryProductPositions(array $categoriesData, $tableName)
    {
        $productIds = [];
        foreach ($categoriesData as $sku => $categories) {
            $productIds[] = $this->skuProcessor->getNewSku($sku)['entity_id'];
        }
        $select = $this->_connection->select()->from($tableName)->where('product_id IN (?)', $productIds);
        $stmt = $this->getConnection()->query($select);
        $positions = [];
        while ($row = $stmt->fetch()) {
            $positions[$row['product_id']][$row['category_id']] = $row['position'];
        }
        return $positions;
    }
}
