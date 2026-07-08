<?php

namespace Digidirect\ProductOverlay\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Model\Product;

class AbstractProductPlugin
{
    const DATA_PRODUCT_KEY = 'product';

    /**
     * @param AbstractProduct $subject
     * @param ImageBlock      $resultImage
     * @param Product         $product
     * @param array|null      $attributes
     *
     * @return ImageBlock
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetImage(
        AbstractProduct $subject,
        ImageBlock $resultImage,
        Product $product,
        $attributes = []
    ) {
        $resultImage->setData(self::DATA_PRODUCT_KEY, $product);

        return $resultImage;
    }
}
