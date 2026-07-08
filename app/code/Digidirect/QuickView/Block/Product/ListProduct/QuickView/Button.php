<?php
namespace Digidirect\QuickView\Block\Product\ListProduct\QuickView;

/**
 * Class Button
 * @package Digidirect\QuickView\Block\Product\ListProduct\QuickView
 */
class Button extends \Magento\Framework\View\Element\Template implements \Magento\Catalog\Block\Product\AwareInterface
{
    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        return $this->setData('product', $product);
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        $productUrl = '';
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getProduct();
        if ($product && $product->getId()) {
            $productUrl = $product->getProductUrl();
        }
        return $productUrl;
    }
}
