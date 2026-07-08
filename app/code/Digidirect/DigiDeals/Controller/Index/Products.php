<?php

namespace Digidirect\DigiDeals\Controller\Index;

class Products extends \Magento\Framework\App\Action\Action
{
    protected $_productCollection;
    
    protected $logger;
    
    protected $productRepository;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->logger = $logger;
        $this->_productCollection= $productCollection;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $productCollection = $this->getProductCollections();
        foreach ($productCollection as $product) {
            try {
                $product->setCustomAttribute('is_digideals', true);
                $this->productRepository->save($product);
            } catch (Exception $ex) {
                $this->logger->info("add digiDeals, " . $ex->getMessage());
                continue;
            }
        }
        echo "digiDeals Products Done!";
    }
    
    public function getProductCollections()
    {
        $collection = $this->_productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('marketplacer_seller', 20329);
        $collection->addAttributeToFilter('is_digideals', array('neq' => true));
        $collection->addMinimalPrice()->addFinalPrice();
        $collection->getSelect()->where("price_index.final_price < price_index.price")->orderRand();
        return $collection;
    }
    
}