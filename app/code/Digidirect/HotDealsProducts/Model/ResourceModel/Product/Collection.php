<?php
/**
 *
 * @category    Digidirect
 * @package     Digidirect_HotDealsProducts
 */

namespace Digidirect\HotDealsProducts\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection{
    public function getHotDealsProduct()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $category = $categoryFactory->create()->load(1814);
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
        $categoryProducts->getSelect()->orderRand();
       
        return $categoryProducts;
    }
}