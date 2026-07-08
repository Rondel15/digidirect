<?php

namespace Digidirect\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class OverrideMetaTags implements ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer){
        $product = $observer->getProduct();
        if (!$product->getMetaDescription()) {
            if ($product->getDescription()) {
                $description = html_entity_decode($product->getDescription());
                $product->setMetaDescription(substr(htmlentities(strip_tags($description)), 0, 160));
            }
        }
        //$product->setMetaDescription(strip_tags($product->getMetaDescription()));
    }
}