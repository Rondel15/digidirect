<?php

namespace Mbs\BestSeller\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Model\Quote\Item;
use Mbs\BestSeller\InvalidQuoteItem;
use Mbs\BestSeller\ProductSalesAttributeMissing;

class ProductSalesHandler
{
    const SALE_ATTRIBUTE = 'nb_sales';
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->eavConfig = $eavConfig;
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Item $item
     * @throws InvalidQuoteItem
     * @throws ProductSalesAttributeMissing
     */
    public function incrementSale(Item $item)
    {
        $product = $this->validateItem($item);
        $product->setData(self::SALE_ATTRIBUTE, $this->getNbSales($product)+1);
        $product->getResource()->saveAttribute($product, self::SALE_ATTRIBUTE);
    }

    /**
     * read how many sales the product has done relying the the product attribute nb_sales
     *
     */
    public function getNbSales(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if (!$salesAttribute = $this->loadSaleAttribute()) {
            return false;
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from($connection->getTableName($salesAttribute->getBackendTable()))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('value')
            ->where('attribute_id=?', $salesAttribute->getAttributeId())
            ->where('entity_id=?', $product->getId());

        $nbSales = (int)$connection->fetchOne($select);

        return $nbSales;
    }

    private function loadSaleAttribute()
    {
        try {
            $saleAttribute = $this->eavConfig->getAttribute(
                Product::ENTITY,
                self::SALE_ATTRIBUTE
            );

            if (!$saleAttribute->getId()) {
                $saleAttribute = false;
            }
        } catch (\Exception $e) {
            $saleAttribute = false;
        }

        return $saleAttribute;
    }

    /**
     * @param Item $item
     * @return Product|bool
     * @throws InvalidQuoteItem
     * @throws ProductSalesAttributeMissing
     */
    private function validateItem(Item $item)
    {
        $result = $item->getProduct();

        if (is_null($result->getSku())) {
            throw new InvalidQuoteItem();
        }

        if (!$this->loadSaleAttribute()) {
            throw new ProductSalesAttributeMissing();
        }

        return $result;
    }
}
