<?php

namespace Digidirect\FreeGift\Plugin\ConfigurableProduct\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Image;
use Magento\ConfigurableProduct\Helper\Data as ConfigurableHelper;

class Data
{
    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var string
     */
    protected $_cartImageType;

    /**
     * @param Image $imageHelper
     * @param string $cartImageType
     */
    public function __construct(
        Image $imageHelper,
        $cartImageType = 'free_gift_cart_image'
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_cartImageType = $cartImageType;
    }

    /**
     * @param \Magento\ConfigurableProduct\Helper\Data $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    public function aroundGetGalleryImages(
        ConfigurableHelper $subject,
        \Closure $proceed,
        ProductInterface $product
    ) {
        $images = $proceed($product);
        foreach ($images as $image) {
            /** @var $image \Magento\Catalog\Model\Product\Image */
            $image->setData(
                $this->_cartImageType,
                $this->_imageHelper->init($product, $this->_cartImageType)->getUrl()
            );
        }
        return $images;
    }

    /**
     * @param \Magento\ConfigurableProduct\Helper\Data $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function aroundGetOptions(
        ConfigurableHelper $subject,
        \Closure $proceed,
        Product $currentProduct,
        array $allowedProducts
    ) {
        $options = $proceed($currentProduct, $allowedProducts);
        if (isset($options['images'])) {
            foreach ($allowedProducts as $product) {
                $productId = $product->getId();
                $images = $subject->getGalleryImages($product);
                if ($images) {
                    $counter = 0;
                    foreach ($images as $image) {
                        $options['images'][$productId][$counter++]['cart'] = $image->getData($this->_cartImageType);
                    }
                }
            }
        }
        return $options;
    }
}
