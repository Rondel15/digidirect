<?php

namespace Digidirect\AI\Model\Lib\Entity\Import\Stock\Processor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import;
use Magento\Framework\Event\ManagerInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Indexer\IndexerRegistry;

class Data extends AbstractEntity
{
    const SCOPE_STOCK = 'stock';

    const ROW_DATA_STOCK_ID = 'stock_id';
    const COLUMN_SKU = 'sku';
    const COLUMN_WEBSITE_ID = 'website_id';
    const COLUMN_QTY = 'qty';

    const ENTITY_TYPE_CODE = 'catalog_product';

    const ERROR_CODE_SKU_IS_INVALID = 'skuIsInvalid';
    const ERROR_CODE_SKU_IS_EMPTY = 'skuIsEmpty';
    const ERROR_CODE_STOCK_IS_INVALID = 'stockIsInvalid';
    const ERROR_CODE_WEBSITE_IS_INVALID = 'websiteIsInvalid';

    /**
     * @var array
     */
    protected $allStocks;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var ResourceProcessor
     */
    protected $resourceProcessor;

    /**
     * @var array
     */
    protected $metadataByInterface = [];

    /**
     * @var array
     */
    protected $stockById = [];

    /**
     * @var array
     */
    protected $stockByName = [];

    /**
     * @var array
     */
    protected $stockByWebsite = [];

    /**
     * @var int
     */
    protected $stocksCount = 0;

    /**
     * @var array
     */
    protected $productInfoBySku = [];

    /**
     * @var array
     */
    protected $correctedCombinations = [];

    /**
     * @var array
     */
    protected $validCombinations = [];

    /**
     * @var array
     */
    protected $notValidCombinations = [];

    /**
     * @var StockStateProviderInterface
     */
    protected $stockStateProvider;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $defaultStockData = [
        'manage_stock' => 1,
        'use_config_manage_stock' => 1,
        'qty' => 0,
        'min_qty' => 0,
        'use_config_min_qty' => 1,
        'min_sale_qty' => 1,
        'use_config_min_sale_qty' => 1,
        'max_sale_qty' => 10000,
        'use_config_max_sale_qty' => 1,
        'is_qty_decimal' => 0,
        'backorders' => 0,
        'use_config_backorders' => 1,
        'notify_stock_qty' => 1,
        'use_config_notify_stock_qty' => 1,
        'enable_qty_increments' => 0,
        'use_config_enable_qty_inc' => 1,
        'qty_increments' => 0,
        'use_config_qty_increments' => 1,
        'is_in_stock' => 1,
        'low_stock_date' => null,
        'stock_status_changed_auto' => 0,
        'is_decimal_divided' => 0,
    ];

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ManagerInterface $manager
     * @param ResourceProcessor $resourceProcessor
     * @param StockStateProviderInterface $stockStateProvider
     * @param StockItemInterfaceFactory $stockItemInterfaceFactory
     * @param DateTime $dateTime
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ManagerInterface $manager,
        ResourceProcessor $resourceProcessor,
        StockStateProviderInterface $stockStateProvider,
        StockItemInterfaceFactory $stockItemInterfaceFactory,
        DateTime $dateTime,
        IndexerRegistry $indexerRegistry
    ) {
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator
        );
        $this->eventManager = $manager;
        $this->resourceProcessor = $resourceProcessor;
        $this->stockStateProvider = $stockStateProvider;
        $this->stockItemFactory = $stockItemInterfaceFactory;
        $this->dateTime = $dateTime;
        $this->indexerRegistry = $indexerRegistry;
        $this->initializeStocks();
        $this->initializeSku();
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;

        $sku = $this->get($rowData, self::COLUMN_SKU);
        if (!$sku) {
            $this->addRowError(self::ERROR_CODE_SKU_IS_EMPTY, $rowNum);
        } elseif (!$this->isSkuValid($sku)) {
            $this->addRowError(self::ERROR_CODE_SKU_IS_INVALID, $rowNum);
        }

        $stockId = $this->get($rowData, self::ROW_DATA_STOCK_ID);
        if ($stockId) {
            if (!$this->validateStockAndWebsite($rowData)) {
                $this->addRowError(self::ERROR_CODE_STOCK_IS_INVALID, $rowNum);
            }
        }

        $websiteId = $this->get($rowData, self::COLUMN_WEBSITE_ID);
        if ($websiteId && !$this->validateStockAndWebsite($rowData)) {
            $this->addRowError(self::ERROR_CODE_WEBSITE_IS_INVALID, $rowNum);
        }

        $this->_processedEntitiesCount++;
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param [] $rowData
     * @return bool
     */
    protected function validateStock($rowData)
    {
        $stockId = $this->get($rowData, self::ROW_DATA_STOCK_ID);
        $valid = !$stockId;

        if ($stockId) {
            if ($stock = $this->getStockById($stockId)) {
                $valid = true;
            } else {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * @param string $sku
     * @return bool
     */
    protected function isSkuValid($sku)
    {
        return isset($this->productInfoBySku[$sku]);
    }

    /**
     * @param [] $rowData
     * @return bool
     */
    protected function validateWebsite($rowData)
    {
        if (!($websiteId = $this->get($rowData, self::COLUMN_WEBSITE_ID))) {
            return true;
        }
        $availableWebsites = [];
        foreach ($this->stockById as $stockInfo) {
            $availableWebsites[] = $stockInfo[self::COLUMN_WEBSITE_ID];
        }

        return in_array($websiteId, $availableWebsites);
    }

    /**
     * If stock ID is specified
     * 1) validate if it exists
     *
     * If exists:
     *     -  check if website is specified:
     *         - if specified:
     *             1) check if multi store is enabled at all
     *             2) validate combination stock_id => website_id.
     *         - if not specified:
     *            1) return true and then website_id will be set up
     *
     *
     *
     * @param [] $rowData
     * @return bool
     */
    protected function validateStockAndWebsite($rowData)
    {
        $websiteId = $this->get($rowData, self::COLUMN_WEBSITE_ID);
        $stockId = $this->get($rowData, self::ROW_DATA_STOCK_ID);
        $stock = $this->getStockById($stockId);

        if ($this->getFromValidCombinations($stockId, $websiteId)) {
            return true;
        }

        if (!$stock && $stockId) {
            $errorMessage = sprintf(
                'Specified Stock ID {%s} does not exist',
                $stockId
            );

            if (!$this->isMultiStock()) {
                $errorMessage = sprintf(
                    'Specified Stock ID {%s} does not exist and multiple stocks functionality is not available.',
                    $stockId
                );
            }

            $this->getErrorAggregator()->addError(
                self::ERROR_CODE_STOCK_IS_INVALID,
                Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $errorMessage
            );

            $this->addToValidCombinations($this->getDefaultStockId(), null);
            return false;
        }

        $stockWebsite = $this->get($stock, self::COLUMN_WEBSITE_ID);
        if ($websiteId) {
            if ($this->supportsMultiStore()) {
                if ($websiteId == $stockWebsite) {
                    $this->addToValidCombinations($stockId, $websiteId);
                    return true;
                }
            }
        }
        $this->addToValidCombinations($stockId, $websiteId);
        return true;
    }

    /**
     * Memorize valid combinations
     *
     *
     * @param string $stockId
     * @param string $websiteId
     * @return void
     */
    protected function addToValidCombinations($stockId, $websiteId)
    {
        $this->validCombinations[$this->getHash($stockId, $websiteId)] = [
            self::COLUMN_WEBSITE_ID => $websiteId,
            self::ROW_DATA_STOCK_ID => $stockId,
        ];
    }

    /**
     * Memorize corrected combinations
     *
     * @param string $rowDataStockId
     * @param string $rowDataWebsiteId
     * @param string $correctedStockId
     * @param string $correctedWebsiteId
     * @return void
     */
    protected function addToCorrectedCombinations(
        $rowDataStockId,
        $rowDataWebsiteId,
        $correctedStockId,
        $correctedWebsiteId
    ) {
        $this->correctedCombinations[$this->getHash($rowDataStockId, $rowDataWebsiteId)] = [
            self::COLUMN_WEBSITE_ID => $correctedWebsiteId,
            self::ROW_DATA_STOCK_ID => $correctedStockId,
        ];
    }

    /**
     * @param string $rowDataStockId
     * @param string $rowDataWebsiteId
     * @return mixed|null
     */
    protected function getFromCorrectedCombinations($rowDataStockId, $rowDataWebsiteId)
    {
        return $this->correctedCombinations[$this->getHash($rowDataStockId, $rowDataWebsiteId)] ?? null;
    }

    /**
     * @param string $rowDataStockId
     * @param string $rowDataWebsiteId
     * @return mixed|null
     */
    protected function getFromValidCombinations($rowDataStockId, $rowDataWebsiteId)
    {
        return $this->validCombinations[$this->getHash($rowDataStockId, $rowDataWebsiteId)] ?? null;
    }

    /**
     * @param array ...$args
     * @return string
     */
    protected function getHash(... $args)
    {
        $hash = '';
        foreach ($args as $arg) {
            $hash .= serialize($arg);
        }

        return $hash;
    }

    /**
     * @return bool
     */
    protected function isMultiStock()
    {
        if ($this->stocksCount == 0) {
            $this->stocksCount = count($this->stockById);
        }

        return $this->stocksCount > 1;
    }

    /**
     * //TODO: when warehouses extension will be added - probably it will be possible
     *
     * @return bool
     */
    public function supportsMultiStore()
    {
        return false;
    }

    /**
     * @param array $rowData
     * @param string $key
     * @return bool
     */
    protected function check($rowData, $key)
    {
        return isset($rowData[$key]);
    }

    /**
     * @param array $rowData
     * @param string $key
     * @return null
     */
    protected function get($rowData, $key)
    {
        return $this->check($rowData, $key) ? $rowData[$key] : null;
    }

    /**
     * @return void
     */
    protected function _importData()
    {
        $this->saveStockData();
    }

    /**
     * @param string $sku
     * @return null
     */
    protected function getProductIdBySku($sku)
    {
        return $this->isSkuValid($sku) ? $this->productInfoBySku[$sku]['entity_id'] ?? null : null;
    }

    /**
     * Gather and save information about product entities.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function saveStockData()
    {
        $productIdsToReindex = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $previousType = null;
            $prevAttributeSet = null;
            $entities = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $row = [];

                /**
                 * Main settings
                 */
                $rowSku = $this->get($rowData, self::COLUMN_SKU);

                $productId = $this->getProductIdBySku($rowSku);
                $productIdsToReindex[] = $productId;

                $row[StockItemInterface::PRODUCT_ID] = $productId;
                $row[self::COLUMN_WEBSITE_ID] = $this->getWebsiteId($rowData);
                $row[self::ROW_DATA_STOCK_ID] = $this->getStockId($rowData);
                $row[self::COLUMN_QTY] = $rowData[self::COLUMN_QTY];

                $existingItem = $this->getExistingStockItem($row);

                $row = array_merge(
                    $this->defaultStockData,
                    array_intersect_key($existingItem, $this->defaultStockData),
                    array_intersect_key($rowData, $this->defaultStockData),
                    $row
                );

                if ($this->resourceProcessor->isQty(
                    $this->getProductBySku($rowSku, 'type_id')
                )
                ) {
                    $stockItem = $this->stockItemFactory->create();
                    $stockItem->setData($row);
                    $row['is_in_stock'] = $this->stockStateProvider->verifyStock($stockItem);
                    if ($this->stockStateProvider->verifyNotification($stockItem)) {
                        $row['low_stock_date'] = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            (new \DateTime())->getTimestamp()
                        );
                    }
                    $row['stock_status_changed_auto'] =
                        (int)!$this->stockStateProvider->verifyStock($stockItem);
                } else {
                    $row['qty'] = 0;
                }

                $entities[] = $row;

                if (null === $rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
            }

            if (!empty($entities)) {
                $this->resourceProcessor->saveStockInfo($entities);
            }

            foreach ($bunch as $rowNum => $rowData) {
                if ($this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    unset($bunch[$rowNum]);
                }
            }

            $this->eventManager->dispatch(
                'stock_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $bunch]
            );
        }
        if (!empty($productIdsToReindex)) {
            $indexer = $this->indexerRegistry->get('catalog_product_category');
            if ($productIdsToReindex && !$indexer->isScheduled()) {
                $indexer->reindexList($productIdsToReindex);
            }
        }
        return $this;
    }

    /**
     * @param [] $rowData
     * @return null
     */
    protected function getStockId($rowData)
    {
        return $this->processValidCombinationField($rowData, self::ROW_DATA_STOCK_ID);
    }

    /**
     * @param [] $rowData
     * @return int|null
     */
    protected function getWebsiteId($rowData)
    {
        return $this->processValidCombinationField($rowData, self::COLUMN_WEBSITE_ID);
    }

    /**
     * @param array $rowData
     * @param string $field
     * @return null
     */
    protected function processValidCombinationField($rowData, $field)
    {
        $validCombination = $this->getFromValidCombinations(
            $this->get($rowData, self::ROW_DATA_STOCK_ID),
            $this->get($rowData, self::COLUMN_WEBSITE_ID)
        );

        $result = $validCombination[$field] ?? null;
        if (!$result) {
            $defaultStock = $this->getDefaultStock();
            return $defaultStock[$field] ?? null;
        }

        return $result;
    }

    /**
     * @param [] $rowData
     * @return null
     *
     */
    protected function isStockIdSpecifiedForRow($rowData)
    {
        return !empty($this->get($rowData, self::ROW_DATA_STOCK_ID));
    }

    /**
     * @param string $interface
     * @return string
     */
    protected function getLinkField($interface)
    {
        return $this->getMetadataByInterface($interface)->getLinkField();
    }

    /**
     * @param string $interface
     * @return EntityMetadataInterface
     */
    protected function getMetadataByInterface($interface)
    {
        if (!isset($this->metadataByInterface[$interface])
            || !($this->metadataByInterface[$interface] instanceof EntityMetadataInterface)
        ) {
            $this->metadataByInterface[$interface] = $this->getMetadataPool()->getMetadata($interface);
        }

        return $this->metadataByInterface[$interface];
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return self::ENTITY_TYPE_CODE;
    }

    /**
     * @param int|string $stockId
     * @return null|array
     */
    protected function getStockById($stockId)
    {
        $this->initializeStocks();
        $stockId = trim(strtolower($stockId));
        $stockInformation = $this->stockById[$stockId] ?? null;
        if (!$stockInformation) {
            $stockInformation = $this->stockByName[$stockId] ?? null;
        }

        return $stockInformation ?: $this->getDefaultStock();
    }

    /**
     * Initialize stocks
     * Make it possible to find Stock Information by stock ID and stock Name (for future Warehouse functionality)
     *
     * @return void
     */
    protected function initializeStocks()
    {
        if (empty($this->stockById) && empty($this->stockByName)) {
            $stocks = $this->resourceProcessor->getAllStocks();

            foreach ($stocks as $stock) {
                $stockId = $stock[StockInterface::STOCK_ID];
                $stockName = strtolower($stock[StockInterface::STOCK_NAME]);
                $this->stockByName[$stockId] = $stock;
                $this->stockById[$stockName] = $stock;
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getDefaultStock()
    {
        return current($this->stockById);
    }

    /**
     * @return mixed
     */
    protected function getDefaultStockId()
    {
        $defaultStock = $this->getDefaultStock();
        return $defaultStock[self::COLUMN_WEBSITE_ID];
    }

    /**
     * Initialize products information
     *
     * @return void
     */
    protected function initializeSku()
    {
        foreach ($this->resourceProcessor->getProducts() as $productInfo) {
            $this->productInfoBySku[$productInfo[ProductInterface::SKU]] = $productInfo;
        }
    }

    /**
     * @param [] $rowData
     * @return array|bool
     */
    protected function getExistingStockItem($rowData)
    {
        $item = $this->resourceProcessor->getStockForItem(
            $this->get($rowData, StockItemInterface::PRODUCT_ID),
            $this->get($rowData, StockItemInterface::STOCK_ID),
            $this->get($rowData, self::COLUMN_WEBSITE_ID)
        );
        return is_array($item) ? $item : [];
    }

    /**
     * @param string $sku
     * @param null $specifiedKey
     * @return mixed|null
     */
    protected function getProductBySku($sku, $specifiedKey = null)
    {
        $result = $this->productInfoBySku[$sku];
        if ($specifiedKey) {
            $result = $result[$specifiedKey] ?? null;
        }

        return $result;
    }
}
