<?php

namespace Digidirect\Utilities\Helper\Frontend;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Stock extends AbstractHelper
{
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Stock constructor.
     *
     * @param Context $context
     * @param StockRegistryInterface $registry
     */
    public function __construct(Context $context, StockRegistryInterface $registry)
    {
        $this->stockRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @param int $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId)
    {
        return $this->stockRegistry->getStockItem($productId);
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function getManageStock($productId)
    {
        return $this->getStockItem($productId)->getManageStock();
    }

    /**
     * @param int $productId
     * @return bool|int
     */
    public function getIsInStock($productId)
    {
        return $this->getStockItem($productId)->getIsInStock();
    }
}
