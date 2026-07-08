<?php

namespace Digidirect\ProductOverlay\Model\Overlay\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class AttributeManager
 * @package Digidirect\ProductOverlay\Model\Overlay\Product
 */
class AttributeManager
{
    const PRODUCT_OVERLAY_ATTRIBUTE_CODE = 'overlay_id';

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $action;

    /**
     * AttributeManager constructor.
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Action $action
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Action $action
    ) {
        $this->productCollection = $productCollectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->action = $action;
    }

    /**
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface $entity
     * @param null|int $store
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($entity, $store = null)
    {
        /** @var $entity \Digidirect\ProductOverlay\Api\Data\OverlayInterface */
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->productCollection->create();
        $productCollection->addStoreFilter($store);
        $productCollection->addAttributeToFilter(
            self::PRODUCT_OVERLAY_ATTRIBUTE_CODE,
            ['finset' => $entity->getOverlayId()]
        );
        return $productCollection;
    }

    /**
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay
     * @return $this
     */
    public function clearProductAttributeValue($overlay)
    {
        $stores = $overlay->getStores();
        foreach ($stores as $storeId) {
            $productCollection = $this->getProductCollection($overlay, $storeId);
            foreach ($productCollection as $product) {
                $overlayIds = $product->getOverlayId();
                if ($overlayIds) {
                    $overlayIds = explode(',', $overlayIds);
                    if (in_array($overlay->getOverlayId(), $overlayIds)) {
                        $overlayIds = array_flip($overlayIds);
                        unset($overlayIds[$overlay->getOverlayId()]);
                        $overlayIds = implode(',', array_flip($overlayIds));
                        $this->action->updateAttributes(
                            [$product->getId()],
                            [self::PRODUCT_OVERLAY_ATTRIBUTE_CODE => $overlayIds],
                            $storeId
                        );
                    }
                }
            }
        }

        return $this;
    }
}
