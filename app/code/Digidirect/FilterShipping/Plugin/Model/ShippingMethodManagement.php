<?php
namespace Digidirect\FilterShipping\Plugin\Model;

use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku;

class ShippingMethodManagement {
   
   public function __construct(
       GetSourceItemsBySku $getSourceItemsBySku
   ) {
       $this->getSourceItemsBySku = $getSourceItemsBySku;
   }

   public function afterEstimateByExtendedAddress($shippingMethodManagement, $output)
   {
       return $this->filterOutput($output);
   }
   private function filterOutput($output)
   {
       
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

       $items = $cart->getQuote()->getAllItems();

       $stockArray = [];
       
       foreach ($items as $item) {
           
           $qty = 0;
           $prodId = $item->getProductId();
           $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $product = $_objectManager->get('\Magento\Catalog\Model\Product')->load($prodId);

           $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());

           foreach ($sourceItems as $sourceItemId => $sourceItem) {

               $qty .= $sourceItem->getQuantity();
           }
           
           $stockArray[] = $qty;
       }
       
       $showMethod = [];
       foreach ($output as $shippingMethod) {
           
           if ($shippingMethod->getCarrierCode() == 'shipping') {
               $showMethod[] = $shippingMethod;
           }
           
       }
       if ($showMethod) {
           
           if(in_array(0, $stockArray)){
               return $showMethod;
           }
           
       }
       return $output;
     
       
       
   }
}