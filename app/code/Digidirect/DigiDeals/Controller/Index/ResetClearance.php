<?php

namespace Digidirect\DigiDeals\Controller\Index;

class ResetClearance extends \Magento\Framework\App\Action\Action
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
                $product->setCustomAttribute('clearance', false);
                $this->productRepository->save($product);
            } catch (Exception $ex) {
                $this->logger->info("remove clearance, " . $ex->getMessage());
                continue;
            }
        }
        echo "Clearance Reset Done!";
    }
    
    public function getProductCollections()
    {
        $collection = $this->_productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('clearance', true);
        return $collection;
    }

}