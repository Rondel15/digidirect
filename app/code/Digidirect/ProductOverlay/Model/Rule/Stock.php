<?php
namespace Digidirect\ProductOverlay\Model\Rule;

use Digidirect\ProductOverlay\Model\Overlays;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\Product;
use Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\StockStatus;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Class Stock
 *
 * @package Digidirect\ProductOverlay\Model\Rule
 *
 */
class Stock implements ProcessorInterface
{
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var []
     */
    protected $productsSkip;

    /**
     * Stock constructor.
     *
     * @param StockRegistryInterface $stockRegistry
     * @param array $mapping
     * @param array $productTypesToSkip
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        array $mapping = [],
        array $productTypesToSkip = [
        Configurable::TYPE_CODE,
        Grouped::TYPE_CODE
        ]
    ) {
        $this->mapping = $mapping;
        $this->stockRegistry = $stockRegistry;
        $this->productsSkip = $productTypesToSkip;
    }

    /**
     * @param Overlays $overlay
     * @return bool
     */
    public function isApplicable(Overlays $overlay)
    {
        /**
         * @var $product Product
         */
        $stockStatus = $overlay->getStockStatus();
        return $this->checkRule($overlay, $stockStatus);
    }

    /**
     * @param Overlays $overlays
     * @param int $stockStatus
     * @return bool
     */
    protected function checkRule(Overlays $overlays, $stockStatus)
    {
        $callback = $this->mapping[$stockStatus] ?? null;
        if (null === $callback || !is_callable([$this, $callback])) {
            return false;
        }

        return $this->$callback($overlays);
    }

    /**
     * @param Overlays $overlay
     * @return bool
     */
    protected function checkIsInStock(Overlays $overlay)
    {
        $stockStatus = $overlay->getStockStatus();
        $product = $overlay->getProduct();
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $inStock = $stockItem->getIsInStock() ? StockStatus::IN_STOCK : StockStatus::OUT_OF_STOCK;
        if ($inStock != $stockStatus) {
            return false;
        }

        return true;
    }

    /**
     * @param Overlays $overlay
     * @return bool
     */
    protected function checkQuantity(Overlays $overlay)
    {
        if ($this->restrictByProductType($overlay)) {
            return false;
        }
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $overlay->getProduct();
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());

        $quantity = $stockItem->getQty();
        $stockFrom = $overlay->getStockFrom();
        $stockTo = $overlay->getStockTo();

        if (!$stockItem->getIsInStock()) {
            if (!$stockFrom || $stockFrom == 0) {
                return true;
            } else {
                return false;
            }
        }

        if ($stockFrom && $stockTo) {
            return ($quantity >= $stockFrom) && ($quantity <= $stockTo);
        }

        if ($stockFrom) {
            return $quantity >= $stockFrom;
        }

        if ($stockTo) {
            return $quantity <= $stockTo;
        }

        return true;
    }

    /**
     * @param Overlays $overlay
     * @return bool
     */
    protected function restrictByProductType(Overlays $overlay)
    {
        return in_array($overlay->getProduct()->getTypeId(), $this->productsSkip);
    }
}
