<?php

namespace Digidirect\Catalog\Plugin;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\Registry;

class ProductViewPlugin
{
    protected $pageConfig;
    protected $registry;

    public function __construct(PageConfig $pageConfig, Registry $registry)
    {
        $this->pageConfig = $pageConfig;
        $this->registry = $registry;
    }

    public function beforeSetLayout(ProductView $subject)
    {
        // Retrieve the current product from the registry
        $product = $this->registry->registry('current_product');

        if ($product) {
            // Check if the meta description is empty
            $metaTitle = $product->getMetaTitle();
            if (empty($metaTitle)) {
                // Set the product name as the meta description
                $productName = $product->getName();
                $Title = "Buy ".$productName. " | digiDirect";
                $this->pageConfig->setMetaTitle($Title);
            }

            // Check if the meta description is empty
            $metaDescription = $product->getMetaDescription();
            if (empty($metaDescription)) {
                // Set the product name as the meta description
                $productName = $product->getName();
                $desc = "Shop ".$productName." online at digiDirect - the camera, computer & electrical experts. Afterpay Available.";
                $this->pageConfig->setDescription($desc);
            }
        }


    }
}
