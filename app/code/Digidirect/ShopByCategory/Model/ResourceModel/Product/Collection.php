<?php
/**
 *
 * @category    Digidirect
 * @package     Digidirect_ShopByCategory
 */

namespace Digidirect\ShopByCategory\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection{
    public function getProductCount($categoryId)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $category = $categoryFactory->create()->load($categoryId)->count();
       
        return $categoryProducts;
    }
}