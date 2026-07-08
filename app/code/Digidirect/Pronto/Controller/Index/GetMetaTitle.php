<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\Product;

class GetMetaTitle extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_productCollectionFactory;

    protected $productRepository;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            Product $helper,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
            \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
            \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            array $data = [])
    {
            $this->helper = $helper;
            $this->_productCollectionFactory = $productCollectionFactory;
            $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
            $this->sourceItemFactory = $sourceItemFactory;
            $this->sourceDataBySku = $sourceDataBySku;
            $this->productStatus = $productStatus;
            $this->productRepository = $productRepository;
            return parent::__construct($context);
    }

    public function execute(){
        
        set_time_limit(600);
        $page_number = 1;
        
        if(isset($_GET["p"])){
            $page_number = $_GET["p"];
        }
        
        /*Get in stock product collection*/
        $collection = $this->_productCollectionFactory->create()->addFieldToSelect('*')
            ->setPageSize(1000) // only get 10 products
            ->setCurPage($page_number)  // first page (means limit 0,10)
            ->setFlag('has_stock_status_filter', false);
        
        $counter = 0;

        foreach ($collection as $key => $product) {
            $sku = $product->getSku();
            $productName = $product->getName();
            $meta_title = $product->getMetaTitle();
            
            echo $productName . " - "  . $sku . " <br />" . $meta_title . "<br /><br />";
            $counter++;
        }
        
        echo "Total updated SKUs: " . $counter;

        exit();
    }
}
