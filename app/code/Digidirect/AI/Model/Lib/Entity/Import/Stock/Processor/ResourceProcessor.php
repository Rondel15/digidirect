<?php

namespace Digidirect\AI\Model\Lib\Entity\Import\Stock\Processor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class ResourceProcessor extends Stock
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * ResourceProcessor constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param StockConfigurationInterface $stockConfiguration
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        StockConfigurationInterface $stockConfiguration,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $scopeConfig, $dateTime, $stockConfiguration, $storeManager, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * @var int
     */
    protected $firstStockId;

    /**
     * Get all stocks(by default magento it is only one)
     *
     * @return array
     */
    public function getAllStocks()
    {
        return $this->getConnection()->fetchAll(
            $this->getConnection()->select()->from($this->getMainTable())->order($this->getIdFieldName() . ' ASC')
        );
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $productLinkedField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        return $this->getConnection()->fetchAll(
            $this->getConnection()->select()->from(
                $this->getTable('catalog_product_entity'),
                [$productLinkedField, 'entity_id', 'type_id', 'sku']
            )
        );
    }

    /**
     * @param string $productId
     * @param string $stockId
     * @param string $websiteId
     * @return array
     */
    public function getStockForItem($productId, $stockId, $websiteId)
    {
        if (!$productId) {
            return [];
        }

        $stockId = $stockId ?: $this->getDefaultStockId();
        $websiteId = $websiteId ?: $this->stockConfiguration->getDefaultScopeId();
        $stockItemSelect = $this->getConnection()->select()
            ->from($this->getTable('cataloginventory_stock_item'))
            ->where($this->getConnection()->quoteInto('product_id = ?', $productId))
            ->where($this->getConnection()->quoteInto('stock_id = ?', $stockId))
            ->where($this->getConnection()->quoteInto('website_id = ?', $websiteId));
        return $this->getConnection()->fetchRow($stockItemSelect);
    }

    /**
     * @return string
     */
    protected function getDefaultStockId()
    {
        if (!$this->firstStockId) {
            $this->firstStockId = $this->getConnection()->fetchOne(
                $this->getConnection()->select()->from($this->getMainTable())->limit(1)
            );
        }

        return $this->firstStockId;
    }

    /**
     * @param string $productTypeId
     * @return bool
     */
    public function isQty($productTypeId)
    {
        return $this->stockConfiguration->isQty($productTypeId);
    }

    /**
     * @param array $entities
     * @return int
     */
    public function saveStockInfo($entities)
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getTable('cataloginventory_stock_item'),
            array_values($entities)
        );
    }
}
