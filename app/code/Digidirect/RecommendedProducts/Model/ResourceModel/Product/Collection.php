<?php
/**
 *
 * @category    Digidirect
 * @package     Digidirect_RecommendedProducts
 */

namespace Digidirect\RecommendedProducts\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection{
    public function getRecommendedProduct()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $category = $categoryFactory->create()->load(767);
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
        $categoryProducts->getSelect()->orderRand();
       
        return $categoryProducts;
    }
}