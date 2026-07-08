<?php

namespace Digidirect\DigiDeals\Controller\Index;

class Reset extends \Magento\Framework\App\Action\Action
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
                $product->setCustomAttribute('is_digideals', false);
                $this->productRepository->save($product);
            } catch (Exception $ex) {
                $this->logger->info("remove digiDeals, " . $ex->getMessage());
                continue;
            }
        }
        echo "digiDeals Reset Done!";
    }
    
    public function getProductCollections()
    {
        $collection = $this->_productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('is_digideals', true);
        return $collection;
    }
    
}