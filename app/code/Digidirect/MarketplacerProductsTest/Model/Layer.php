<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MyCustomCollection
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Digidirect\MarketplacerProductsTest\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Layer extends \Magento\Catalog\Model\Layer
{
    protected $logger;
    
    protected $categoryRepo;
    
    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\CategoryRepository $categoryRepo,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->categoryRepo = $categoryRepo;
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }
    
    public function getStateKey()
    {
        if (!$this->_stateKey) {
            $this->_stateKey = $this->stateKeyGenerator->toString($this->getCurrentCategory());
        }
        //$this->logger->info("this->_stateKey: " . $this->_stateKey); 
        return $this->_stateKey;
    }
    
    public function getProductCollection()
    {
        $defaultCategory = 2;
        $productIdsArray = [];
        
        if (isset($this->_productCollections[$defaultCategory])) {
            $collection = $this->_productCollections[$defaultCategory];
        } else {
            $category = $this->categoryRepo->get($defaultCategory, 1);
            $collection = $this->collectionProvider->getCollection($category);
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $collection->addAttributeToFilter("marketplacer_seller", array("neq" => 20329));
            $this->prepareProductCollection($collection);
            $this->_productCollections[$defaultCategory] = $collection;
        }
        
        /*foreach ($collection as $product) {
            $this->logger->info("Product: " . $product->getId());
            if (!in_array($product->getId(), $productIdsArray))  {
                array_push($productIdsArray, $product->getId());
            }
        }
        
        if (isset($this->_productCollections[$defaultCategory])) {
            $collection = $this->_productCollections[$defaultCategory];
        } else {
            $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $collection->addAttributeToFilter("entity_id", ["in"=>$productIdsArray]);
        }*/
        
        return $collection;
    }
    
}
