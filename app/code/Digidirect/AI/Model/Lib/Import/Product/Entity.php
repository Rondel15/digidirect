<?php

namespace Digidirect\AI\Model\Lib\Import\Product;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Swatches\Model\Swatch;
use Digidirect\AI\Model\Logger\Logger;
use Digidirect\AI\Model\Engine\Processor\Exception\ProcessException;
use Digidirect\AI\Model\Logger\LoggerInterface as AILoggerInterface;

/**
 * Class Entity
 *
 * @see \Digidirect\AI\Model\Lib\Entity\Import\Product\Product AND USE IT!
 * @deprecated
 * @package Digidirect\AI\Model\Lib\Import\Product
 */
class Entity extends Product implements EntityInterface
{
    /**********parameters********/
    const PARAM_UNASSIGN_WEBSITES = '_unassign_from_websites';
    const PARAM_DISABLE_URL_KEY_VALIDATION = '_disable_url_key_validation';

    /**
     * AILoggerInterface
     *
     * @var AILoggerInterface
     */
    protected $_aiLogger;
    
    /**
     * Column names that holds images files names
     *
     * @var string[]
     */
    protected $_imagesArrayKeys = [
        '_media_image',
        'image',
        'small_image',
        'thumbnail',
        'swatch_image',
        'featured_image'
    ];

    /**
     * Prod Attr Col Fac
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $_prodAttrColFac;

    /**
     * Swatch Attributes
     *
     * @var []
     */
    protected $_swatchAttributes = [];

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Entity constructor.
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
     * @param Import\Config $importConfig
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param Product\OptionFactory $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param Product\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory
     * @param \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac
     * @param DateTime\TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param Product\StoreResolver $storeResolver
     * @param Product\SkuProcessor $skuProcessor
     * @param Product\CategoryProcessor $categoryProcessor
     * @param Product\Validator $validator
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param Product\TaxClassProcessor $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param Service\DataSource $dataSource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param AILoggerInterface $logger
     * @param [] $data
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
        Product\StoreResolver $storeResolver,
        Product\SkuProcessor $skuProcessor,
        Product\CategoryProcessor $categoryProcessor,
        Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        Product\TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Digidirect\AI\Model\Lib\Import\Product\Service\DataSource $dataSource,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        AILoggerInterface $aiLogger,
        array $data = []
    ) {
        $this->errorAggregator = $errorAggregator;
        $this->_prodAttrColFac = $prodAttrColFac;
        $data['option_entity'] = $optionFactory->create(
            ['data' => ['product_entity' => $this, 'data_source_model' => $dataSource]]
        );
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
            $data
        );

        $this->_initSwatchAttributes();
        $this->_aiLogger = $aiLogger;
        $this->_dataSourceModel = $dataSource;     // define our own source model
    }

    /**
     * Save products
     * @return $this
     */
    public function saveProducts()
    {
        $this->_initSkus();
        $this->_validatedRows = null;
        $this->getErrorAggregator()->clear();
        $this->_processSelectAttributes();
        $this->_saveProductsData();
        $this->_dataSourceModel->clear();
        return $this;
    }

    /**
     * Reinit SKUs
     * @return $this
     */
    protected function _initSkus()
    {
        $entityTypeModels = $this->getProductTypeModels();
        $this->getSkuProcessor()->setTypeModels($entityTypeModels);
        $this->_oldSku = $this->skuProcessor->reloadOldSkus()->getOldSkus();
        return $this;
    }

    /**
     * Init swatch attributes
     * @return $this
     */
    protected function _initSwatchAttributes()
    {
        if (!empty($this->_swatchAttributes)) {
            return $this;
        }

        $collection = $this->_prodAttrColFac->create();
        $collection->getSelect()->where('additional_table.additional_data LIKE ?', '%swatch_input_type%');
        $attributes = $collection->getItems();
        foreach ($attributes as $attribute) {
            $this->_swatchAttributes[$attribute->getId()] = $attribute;
        }

        return $this;
    }

    /**
     * Delete products method
     * @throws \Exception
     * @return $this
     */
    public function deleteProduct()
    {
        $this->_deleteProducts();
        return $this;
    }

    /**
     * Get entity type code
     * @return string
     */
    public function getEntityTypeCode()
    {
        return \Magento\Catalog\Model\Product::ENTITY;
    }

    /**
     * Wrapping for throw exception
     * @param string $msg
     * @throws ProcessException
     * @return void
     */
    protected function _throwException($msg)
    {
        throw new ProcessException($msg);
    }

    /**
     * Set Products Data
     *
     * @param [] $data
     * @return $this
     */
    public function setProductsData(array $data)
    {
        $this->countItemsCreated = 0;
        $this->countItemsUpdated = 0;
        $this->_dataSourceModel->setBunch($data);
        return $this;
    }

    /**
     * Get SKU processor
     * @return Product\SkuProcessor
     */
    public function getSkuProcessor()
    {
        return $this->skuProcessor;
    }

    /**
     * Add non-existed options for select attributes
     *
     * @return $this
     */
    protected function _processSelectAttributes()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $entityTypeModel = $this->retrieveProductTypeByName($rowData[self::COL_TYPE]);
                if (!$entityTypeModel) {
                    continue;
                }
                foreach ($rowData as $attrCode => $attrValue) {
                    $attrParams = $entityTypeModel->retrieveAttribute($attrCode, $rowData[Product::COL_ATTR_SET]);
                    if (!(isset($attrParams['type'])
                        && in_array($attrParams['type'], ['select', 'boolean', 'multiselect']))
                    ) {
                        continue;
                    }
                    $values = explode(Product::PSEUDO_MULTI_LINE_SEPARATOR, $rowData[$attrCode]);
                    foreach ($values as $value) {
                        if (isset($attrParams['options'][strtolower($value)]) || empty($value)) {
                            continue;
                        }
                        if (isset($rowData[self::COL_PRODUCT_WEBSITES])) {
                            $storeIds = $this->storeResolver->getWebsiteCodeToStoreIds(
                                $rowData[self::COL_PRODUCT_WEBSITES]
                            );
                            $newOption = $this->_createAttributeOption($attrParams, $value, array_values($storeIds));
                        } else {
                            $newOption = $this->_createAttributeOption($attrParams, $value);
                        }

                        $newOptionValue = strtolower($newOption['attribute_value']);
                        foreach ($this->getProductTypeModels() as $productTypeModel) {
                            $productTypeModel->addAttributeOption(
                                $attrParams['code'],
                                $newOptionValue,
                                $newOption['option_id']
                            );
                        }
                        AbstractType::$commonAttributesCache[$attrParams['id']]['options'][$newOptionValue]
                            = $newOption['option_id'];
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get attribute option max position
     *
     * @param int $attributeId
     * @return string
     */
    protected function _getAttributeOptionMaxPosition($attributeId)
    {
        $select = $this->_connection->select()
            ->from($this->_connection->getTableName('eav_attribute_option'), ['MAX(sort_order) as max_position'])
            ->where('attribute_id = ?', $attributeId);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Create attribute option
     * @param [] $attrParams
     * @param string $attributeValue
     * @param [] $storeIds
     * @return []
     */
    protected function _createAttributeOption(
        &$attrParams,
        $attributeValue,
        $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID]
    ) {
        $maxPosition = $this->_getAttributeOptionMaxPosition($attrParams['id']);
        $data = [
            'attribute_id' => $attrParams['id'],
            'sort_order' => ++$maxPosition
        ];

        $optionTable = $this->_connection->getTableName('eav_attribute_option');
        $this->_connection->insert($optionTable, $data);
        $optionId = $this->_connection->lastInsertId($optionTable);

        $optionValueTable = $this->_connection->getTableName('eav_attribute_option_value');
        $this->_connection->delete($optionValueTable, ['option_id = ?' => $optionId]);
        $optionValueSwatchTable = $this->_connection->getTableName('eav_attribute_option_swatch');
        $this->_connection->delete($optionValueSwatchTable, ['option_id = ?' => $optionId]);

        if (!in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeIds)) {   // also save for default store
            array_push($storeIds, \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        }
        foreach ($storeIds as $storeId) {
            $dataValue = [
                'option_id' => $optionId,
                'store_id' => $storeId,
                'value' => $attributeValue,
            ];

            $this->_connection->insert($optionValueTable, $dataValue);

            if (isset($this->_swatchAttributes[$attrParams['id']])) {
                $swatchDataString = $this->_swatchAttributes[$attrParams['id']]->getAdditionalData();
                $swatchData = unserialize($swatchDataString);
                if (empty($attributeValue)) {
                    $type = Swatch::SWATCH_TYPE_EMPTY;
                } elseif ($swatchData['swatch_input_type'] == Swatch::SWATCH_INPUT_TYPE_VISUAL) {
                    if ($swatchData['use_product_image_for_swatch'] == 1) {
                        $type = Swatch::SWATCH_TYPE_EMPTY;
                    } else {
                        $type = Swatch::SWATCH_TYPE_VISUAL_COLOR;
                    }
                } else {
                    $type = Swatch::SWATCH_TYPE_TEXTUAL;
                }
                $dataValue['type'] = $type;
                $this->_connection->insert($optionValueSwatchTable, $dataValue);
            }
        }

        $newOption = ['attribute_value' => $attributeValue, 'option_id' => $optionId];
        return $newOption;
    }

    /**
     * Get product type models
     *
     * @return []
     */
    public function getProductTypeModels()
    {
        return $this->_productTypeModels;
    }

    /**
     * Get resource
     *
     * @return \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel
     */
    public function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = $this->_resourceFactory->create();
        }
        return $this->_resource;
    }

    /**
     * Save products. Digidirect changes: url rewrites only for new products
     *
     * @return \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel
     * @SuppressWarnings(PHPMD.LongMehtod)
     */
    protected function _saveProducts()
    {
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;
        $newProducts = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $this->websitesCache = [];
            $this->categoriesCache = [];
            $tierPrices = [];
            $mediaGallery = [];
            $uploadedImages = [];
            $previousType = null;
            $prevAttributeSet = null;
            $existingImages = $this->getExistingImages($bunch);

            foreach ($bunch as $rowNum => $rowData) {
                try {
                    if (!$this->validateRow($rowData, $rowNum)) {
                        $errors = [];
                        foreach ($this->getErrorAggregator()->getErrorByRowNumber($rowNum) as $error) {
                            $errors[] = $error->getErrorMessage();
                        }
                        $this->_getAiLogger()->notice(
                            __('Row "%1" does not valid and skipped.', $rowNum),
                            ['errors' => $errors, 'rowData' => $rowData],
                            Logger::LOG_PLACE_FILE_AND_DB
                        );
                        continue;
                    }
                    
                    if ($this->getErrorAggregator()->hasToBeTerminated()) {
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        $this->_getAiLogger()->notice(
                            __('Row "%1" has to be terminated and skipped. Please check critical errors', $rowNum),
                            $rowData,
                            Logger::LOG_PLACE_FILE_AND_DB
                        );
                        continue;
                    }
                    $rowScope = $this->getRowScope($rowData);
    
                    $rowSku = $rowData[self::COL_SKU];
    
                    if (null === $rowSku) {
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        $this->_getAiLogger()->notice(
                            __('Row "%1" has not sku and skipped.', $rowNum),
                            $rowData,
                            Logger::LOG_PLACE_FILE_AND_DB
                        );
                        continue;
                    } elseif (self::SCOPE_STORE == $rowScope) {
                        // set necessary data from SCOPE_DEFAULT row
                        $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                        $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                        $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                    }
    
                    // 1. Entity phase
                    if (isset($this->_oldSku[$rowSku])) {
                        // existing row
                        $entityRowsUp[] = [
                            'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                            'attribute_set_id' => $this->_oldSku[$rowSku]['attr_set_id'],
                            $this->getProductEntityLinkField()
                                         => $this->_oldSku[$rowSku][$this->getProductEntityLinkField()],
                        ];
                    } else {
                        $newProducts[] = $rowData;
                        if (!$productLimit || $productsQty < $productLimit) {
                            $entityRowsIn[$rowSku] = [
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
                            $this->_getAiLogger()->notice(
                                __('Row "%1" product limit condition, row skipped.', $rowNum),
                                $rowData,
                                Logger::LOG_PLACE_FILE_AND_DB
                            );
                            continue;
                        }
                    }
    
                    if (!array_key_exists($rowSku, $this->websitesCache)) {
                        $this->websitesCache[$rowSku] = [];
                    }
                    // 2. Product-to-Website phase
                    if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                        $websiteCodes = explode(
                            $this->getMultipleValueSeparator(),
                            $rowData[self::COL_PRODUCT_WEBSITES]
                        );
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
    
                    // 4.1. Tier prices phase
                    if (!empty($rowData['_tier_price_website'])) {
                        $tierPrices[$rowSku][] = [
                            'all_groups'        => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                            'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                            self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                            'qty'               => $rowData['_tier_price_qty'],
                            'value'             => $rowData['_tier_price_price'],
                            'website_id'        => self::VALUE_ALL == $rowData['_tier_price_website'] || $priceIsGlobal
                                ? 0
                                : $this->storeResolver->getWebsiteCodeToId(
                                    $rowData['_tier_price_website']
                                ),
                        ];
                    }
    
                    if (!$this->validateRow($rowData, $rowNum)) {
                        $errors = [];
                        foreach ($this->getErrorAggregator()->getErrorByRowNumber($rowNum) as $error) {
                            $errors[] = $error->getErrorMessage();
                        }
                        $this->_getAiLogger()->notice(
                            __('Row "%1" does not valid and skipped.', $rowNum),
                            ['errors' => $errors, 'rowData' => $rowData],
                            Logger::LOG_PLACE_FILE_AND_DB
                        );
                        continue;
                    }
    
                    // 5. Media gallery phase
                    $disabledImages = [];
                    list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
                    if (isset($rowData['_media_is_disabled'])) {
                        $disabledImages = array_flip(
                            explode($this->getMultipleValueSeparator(), $rowData['_media_is_disabled'])
                        );
                    }
                    $rowData[self::COL_MEDIA_IMAGE] = [];
                    foreach ($rowImages as $column => $columnImages) {
                        foreach ($columnImages as $position => $columnImage) {
                            if (!isset($uploadedImages[$columnImage])) {
                                $uploadedFile = $this->uploadMediaFiles(trim($columnImage), true);
                                if ($uploadedFile) {
                                    $uploadedImages[$columnImage] = $uploadedFile;
                                } else {
                                    // @codingStandardsIgnoreStart
                                    $this->addRowError(
                                        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                        $rowNum,
                                        $column,
                                        __(
                                            'Image "%1" could not be downloaded - resource due to timeout or access permissions(Row Sku: "%2").',
                                            $columnImage,
                                            $rowSku
                                        ),
                                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                    );
                                    // @codingStandardsIgnoreEnd
                                }
                            } else {
                                $uploadedFile = $uploadedImages[$columnImage];
                            }
    
                            if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                                $rowData[$column] = $uploadedFile;
                            }
    
                            $imageNotAssigned = !isset($existingImages[$rowSku][$uploadedFile]);
    
                            if ($uploadedFile && $imageNotAssigned) {
                                if ($column == self::COL_MEDIA_IMAGE) {
                                    $rowData[$column][] = $uploadedFile;
                                }
                                $mediaGallery[$rowSku][] = [
                                    'attribute_id' => $this->getMediaGalleryAttributeId(),
                                    'label'        => isset($rowLabels[$column][$position])
                                        ? $rowLabels[$column][$position] : '',
                                    'position'     => $position + 1,
                                    'disabled'     => isset($disabledImages[$columnImage]) ? '1' : '0',
                                    'value'        => $uploadedFile,
                                ];
                                $existingImages[$rowSku][$uploadedFile] = true;
                            }
                        }
                    }
    
                    // 6. Attributes phase
                    $rowStore = (self::SCOPE_STORE == $rowScope)
                        ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                        : 0;
                    $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                    if (null === $productType) {
                        $previousType = $productType;
                    }
                    if (isset($rowData[self::COL_ATTR_SET])) {
                        $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                    }
                    if (self::SCOPE_NULL == $rowScope) {
                        // for multiselect attributes only
                        if (null === $prevAttributeSet) {
                            $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                        }
                        if ((null === $productType) && (null === $previousType)) {
                            $productType = $previousType;
                        }
                        if (null === $productType) {
                            $this->_getAiLogger()->notice(
                                __('Row "%1" product type is null, row skipped.', $rowNum),
                                $rowData,
                                Logger::LOG_PLACE_FILE_AND_DB
                            );
                            continue;
                        }
                    }
    
                    $productTypeModel = $this->_productTypeModels[$productType];
                    if (!empty($rowData['tax_class_name'])) {
                        $rowData['tax_class_id'] =
                            $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                    }
    
                    if (empty($rowData[self::COL_SKU])) {
                        $rowData = $productTypeModel->clearEmptyData($rowData);
                    }
    
                    $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                        $rowData,
                        !isset($this->_oldSku[$rowSku])
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
    
                        if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
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
                            if (!isset($this->_oldSku[$rowSku])) {
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
                } catch (\Throwable $e) {
                    $this->_getAiLogger()->warning(
                        __('Row "%1" skipped. Error appear: %2', $rowNum, $e->getMessage()),
                        $rowData,
                        Logger::LOG_PLACE_FILE_AND_DB
                    );
                }
            }

            if ($aggregatorErrors = $this->getErrorAggregator()->getAllErrors()) {
                $errors = [];
                foreach ($this->getErrorAggregator()->getAllErrors() as $error) {
                    if ($error->getErrorLevel() == ProcessingError::ERROR_LEVEL_CRITICAL) {
                        $errors[] = [
                            'rowNum' => $error->getRowNumber(),
                            'ErrorMessage' => $error->getErrorMessage()
                        ];
                    }
                }
                if (!empty($errors)) {
                    $this->_getAiLogger()->critical(
                        __('Found critical errors.'),
                        $errors,
                        Logger::LOG_PLACE_FILE_AND_DB
                    );
                }
            }

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
            );

            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $newProducts]
            );
        }
        return $this;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Save product attributes.
     *
     * @param [] $attributesData
     * @return $this
     */
    protected function _saveProductAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $skuData) {
            $tableData = [];
            $deleteData = [];
            $linkField = $this->getProductEntityLinkField();
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
                        if (empty($storeValue)) {
                            $deleteData[$attributeId][$storeId][$linkId] = true;
                        } else {
                            $tableData[] = [
                                $this->getProductEntityLinkField() => $linkId,
                                'attribute_id' => $attributeId,
                                'store_id' => $storeId,
                                'value' => $storeValue,
                            ];
                        }
                    }
                }
            }

            if (!empty($deleteData)) {
                foreach ($deleteData as $attributeId => $deleteStoreData) {
                    foreach ($deleteData as $storeId => $linkFields) {
                        $this->_connection->delete(
                            $tableName,
                            [
                                $linkField . ' IN (?)' => array_keys($linkFields),
                                'attribute_id = ?' => $attributeId,
                                'store_id = ?' => $storeId
                            ]
                        );
                    }
                }
            }

            if (!empty($tableData)) {
                $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
            }
        }
        return $this;
    }

    /**
     * Save product websites.
     *
     * @param array $websiteData
     * @return $this
     */
    protected function _saveProductWebsites(array $websiteData)
    {
        static $tableName = null;
        $params = $this->getParameters();
        $needRemoveWebsites = !empty($params[self::PARAM_UNASSIGN_WEBSITES])
            ? $params[self::PARAM_UNASSIGN_WEBSITES]
            : false;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductWebsiteTable();
        }
        if ($websiteData) {
            $websitesData = [];
            $delProductId = [];

            foreach ($websiteData as $delSku => $websites) {
                $productId = $this->skuProcessor->getNewSku($delSku)['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($websites) as $websiteId) {
                    $websitesData[] = ['product_id' => $productId, 'website_id' => $websiteId];
                }
            }
            if (Import::BEHAVIOR_APPEND != $this->getBehavior() || $needRemoveWebsites) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('product_id IN (?)', $delProductId)
                );
            }
            if ($websitesData) {
                $this->_connection->insertOnDuplicate($tableName, $websitesData);
            }
        }

        return $this;
    }

    /**
     * Get Images Array Keys
     *
     * @return \string[]
     */
    public function getImagesArrayKeys()
    {
        return $this->_imagesArrayKeys;
    }

    /**
     * Get logger
     *
     * @return AILoggerInterface
     */
    protected function _getAiLogger()
    {
        return $this->_aiLogger;
    }

    /**
     * Rewrite private method fo allow disable validation url key
     *
     * {@inheritdoc}
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        $rowScope = $this->getRowScope($rowData);

        // BEHAVIOR_DELETE and BEHAVIOR_REPLACE use specific validation logic
        if (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
        }
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                $this->addRowError(ValidatorInterface::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                return false;
            }
            return true;
        }

        if (!$this->validator->isValid($rowData)) {
            foreach ($this->validator->getMessages() as $message) {
                $this->addRowError($message, $rowNum, $this->validator->getInvalidAttribute());
            }
        }

        $sku = $rowData[self::COL_SKU];
        if (null === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
        } elseif (false === $sku) {
            $this->addRowError(ValidatorInterface::ERROR_ROW_IS_ORPHAN, $rowNum);
        } elseif (self::SCOPE_STORE == $rowScope
            && !$this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
        ) {
            $this->addRowError(ValidatorInterface::ERROR_INVALID_STORE, $rowNum);
        }

        // SKU is specified, row is SCOPE_DEFAULT, new product block begins
        $this->_processedEntitiesCount++;

        $sku = $rowData[self::COL_SKU];

        if (isset($this->_oldSku[$sku])) {
            // can we get all necessary data from existent DB product?
            // check for supported type of existing product
            if (isset($this->_productTypeModels[$this->_oldSku[$sku]['type_id']])) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    $this->prepareNewSkuData($sku)
                );
            } else {
                $this->addRowError(ValidatorInterface::ERROR_TYPE_UNSUPPORTED, $rowNum);
                // child rows of legacy products with unsupported types are orphans
                $sku = false;
            }
        } else {
            // validate new product type and attribute set
            if (!isset($rowData[self::COL_TYPE]) || !isset($this->_productTypeModels[$rowData[self::COL_TYPE]])) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID_TYPE, $rowNum);
            } elseif (!isset($rowData[self::COL_ATTR_SET])
                || !isset($this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]])
            ) {
                $this->addRowError(ValidatorInterface::ERROR_INVALID_ATTR_SET, $rowNum);
            } elseif ($this->skuProcessor->getNewSku($sku) === null) {
                $this->skuProcessor->addNewSku(
                    $sku,
                    [
                        'row_id' => null,
                        'entity_id' => null,
                        'type_id' => $rowData[self::COL_TYPE],
                        'attr_set_id' => $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]],
                        'attr_set_code' => $rowData[self::COL_ATTR_SET],
                    ]
                );
            }
            if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                $sku = false;
            }
        }

        if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
            $newSku = $this->skuProcessor->getNewSku($sku);
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $newSku['attr_set_code'];

            $rowAttributesValid = $this->_productTypeModels[$newSku['type_id']]->isRowValid(
                $rowData,
                $rowNum,
                !isset($this->_oldSku[$sku])
            );
            if (!$rowAttributesValid && self::SCOPE_DEFAULT == $rowScope) {
                // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                $sku = false;
            }
        }
        // validate custom options
        $this->getOptionEntity()->validateRow($rowData, $rowNum);

        if ($this->isNeedToValidateUrlKey($rowData)) {
            $urlKey = $this->getUrlKey($rowData);
            $storeCodes = empty($rowData[self::COL_STORE_VIEW_CODE])
                ? array_flip($this->storeResolver->getStoreCodeToId())
                : explode($this->getMultipleValueSeparator(), $rowData[self::COL_STORE_VIEW_CODE]);
            foreach ($storeCodes as $storeCode) {
                $storeId = $this->storeResolver->getStoreCodeToId($storeCode);
                $productUrlSuffix = $this->getProductUrlSuffix($storeId);
                $urlPath = $urlKey . $productUrlSuffix;
                if (empty($this->urlKeys[$storeId][$urlPath])
                    || ($this->urlKeys[$storeId][$urlPath] == $rowData[self::COL_SKU])
                ) {
                    $this->urlKeys[$storeId][$urlPath] = $rowData[self::COL_SKU];
                    $this->rowNumbers[$storeId][$urlPath] = $rowNum;
                } else {
                    $this->addRowError(ValidatorInterface::ERROR_DUPLICATE_URL_KEY, $rowNum);
                }
            }
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Rewrite private method fo allow disable validation url key
     *
     * {@inheritdoc}
     */
    private function prepareNewSkuData($sku)
    {
        $data = [];
        foreach ($this->_oldSku[$sku] as $key => $value) {
            $data[$key] = $value;
        }

        $data['attr_set_code'] = $this->_attrSetIdToName[$this->_oldSku[$sku]['attr_set_id']];

        return $data;
    }

    /**
     * Rewrite private method fo allow disable validation url key
     *
     * {@inheritdoc}
     */
    private function isNeedToValidateUrlKey($rowData)
    {
        $params = $this->getParameters();

        if (!empty($params[self::PARAM_DISABLE_URL_KEY_VALIDATION])) {
            //we do not want validate url key - it already specified as correct
            return false;
        }

        return (!empty($rowData[self::URL_KEY]) || !empty($rowData[self::COL_NAME]))
        && (empty($rowData[self::COL_VISIBILITY])
            || $rowData[self::COL_VISIBILITY]
            !== (string)Visibility::getOptionArray()[Visibility::VISIBILITY_NOT_VISIBLE]);
    }
}
