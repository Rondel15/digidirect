<?php

namespace Mbs\BestSeller\Controller\Product;

class Sold extends \Magento\Framework\App\Action\Action
{
    protected $_productCollection;

    protected $stockFilter;
    
    protected $logger;
    
    protected $resourceConfigurable;
    
    protected $_reportCollectionFactory;
    
    protected $productRepository;
    
    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory $reportCollectionFactory,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->logger = $logger;
        $this->_productCollection= $productCollection;
        $this->stockFilter = $stockFilter;  
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->_reportCollectionFactory = $reportCollectionFactory;
        $this->request = $request;
        parent::__construct($context);
    }

    public function execute()
    {
        $productCollection = $this->getProductCollections();
        $this->logger->info('$productCollection->count(): ' . $productCollection->count());
        $this->logger->info('Function start!');
        $counter = 0;
        foreach ($productCollection as $product) {
            $counter++;
            $setSales = $this->getProductSales($product->getData('entity_id'), $product->getFinalPrice());
            $this->logger->info('$counter: ' . $counter . ', sku: ' . $product->getData('sku') . ", price: " . $product->getFinalPrice() . ", sales: " . $setSales);
            try {
                $prod = $this->productRepository->get($product->getData('sku'));
                $prod->setCustomAttribute('nb_sales', $setSales);
                $this->productRepository->save($prod);
            }catch(Exception $e) {
                $this->logger->info('Message: ' . $e->getMessage());
            }
        }
        $this->logger->info('Function end!');
        print_r('Done!');
    }
    
    public function getProductSales($productID, $price) {
        $SoldProducts = $this->_reportCollectionFactory->create();
        $SoldProdudctCOl = $SoldProducts->addOrderedQty(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'))->addAttributeToFilter('product_id', $productID);
        /* If does have any product id 
         * then return false
         */
        if(!$SoldProdudctCOl->count()):
            return 0;
        endif;
        $SoldProdudctCOl->getSelect()->__toString();
        $product = $SoldProdudctCOl->getFirstItem();
        $productSales = (int)$product->getData('ordered_qty') * $price;
        //$this->logger->info('getProductSales, ' . $productID . ', ' . $product->getData('ordered_qty') . ', ' . $price . ', ' . $productSales);
        return $productSales;
    }
    
    public function getProductCollections() {
        $cat = "";
        if($this->request->getParam('cat')){
            $cat = $this->request->getParam('cat');
        }
        $this->logger->info('$cat: ' . $cat);
        $collection = $this->_productCollection->create();
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addMinimalPrice()->addFinalPrice();
        //$collection->addAttributeToFilter("nb_sales", array("null" => false));
        if($this->request->getParam('cat')){
            $collection->addCategoriesFilter(['in' => $cat]);
        }
        return $collection;
    }
}