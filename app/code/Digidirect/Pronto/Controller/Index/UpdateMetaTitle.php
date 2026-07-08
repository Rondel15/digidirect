<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\Product;

class UpdateMetaTitle extends \Magento\Framework\App\Action\Action
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
       
        $limit = 1000;

        if(isset($_GET["limit"])){
            $limit = $_GET["limit"];
        }
       
        $store = false;

        if(isset($_GET["store"])){
            $store = true;
        }

        /*Get in stock product collection*/
        $collection = $this->_productCollectionFactory->create()->addFieldToSelect('*')
            ->setPageSize($limit) // only get 10 products
            ->setCurPage($page_number)  // first page (means limit 0,10)
            ->setFlag('has_stock_status_filter', false);

        $concat = " | Buy at digiDirect";
        $counter = 0;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //$storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');

        foreach ($collection as $key => $product) {
            $productId = $product->getId();

            $sku = $product->getSku();
            $productName = $product->getName();
            $meta_title = $product->getMetaTitle();

            $existing_meta_title = $productName . $concat;
           
            if($meta_title != $existing_meta_title){
                $meta_title = $productName . $concat;
               
                $productRepo = $this->productRepository->get($sku);
               
                if($store == true){
                    $productRepo->setStoreId(0); // Comment out for digidirectAu store view update
                }

                $productRepo->setMetaTitle($meta_title);
                $this->productRepository->save($productRepo);

                echo $productName . " - "  . $sku . " <br />" . $meta_title . " - Updated <br /><br />";
                $counter++;
            }
            else{
                echo $productName . " - "  . $sku . " <br />" . $meta_title . " OKAY<br /><br />";
                $counter++;
            }
        }

        exit();
    }
}
