<?php
/**
 *
 * @category    Digidirect
 * @package     Digidirect_YouMayAlsoLikeProducts
 */

namespace Digidirect\YouMayAlsoLikeProducts\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection{
    public function getYouMayAlsoLikeProduct()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        $category = $categoryFactory->create()->load(767);
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
        $categoryProducts->getSelect()->orderRand()->limit(6);
       
        return $categoryProducts;
    }
}