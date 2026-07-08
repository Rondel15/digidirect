<?php

namespace Digidirect\ProductOverlay\Ui\DataProvider\Product\Listing\Collector;

use Digidirect\ProductOverlay\Model\Overlays;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Digidirect\ProductOverlay\Helper\Data;

/**
 * Class Overlay
 * @package Digidirect\ProductOverlay\Ui\DataProvider\Product\Listing\Collector
 */
class Overlay implements ProductRenderCollectorInterface
{
    /**
     * @var Data
     */
    protected $productOverlayHelper;

    /**
     * Overlay constructor.
     * @param Data $productOverlayHelper
     */
    public function __construct(
        Data $productOverlayHelper
    ) {
        $this->productOverlayHelper = $productOverlayHelper;
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $extensionAttributes = $productRender->getExtensionAttributes();
        $collection = $this->productOverlayHelper->getProductOverlays($product);
        $overlays = [];
        if (!empty($collection)) {
            foreach ($collection as $overlay) {
                /** @var Overlays $overlay */
                $overlay->setImageSrc($this->productOverlayHelper->getImageUrl($overlay->getValue('img')));
                $overlay->setContainerPath($this->productOverlayHelper->getContainerPath($overlay->getMode()));
                $overlay->setCssClass($overlay->getCssClass());
                $overlays[] = $overlay->getData();
            }

            $extensionAttributes->setOverlays($overlays);
            $productRender->setExtensionAttributes($extensionAttributes);
        }
    }
}
