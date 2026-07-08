<?php
namespace Digidirect\Pronto\Controller\Index;

use Digidirect\Pronto\Helper\Product;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;

class CategoryUpdate extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_productCollectionFactory;
    
    protected $categoryFactory;
    
    protected $categoryLinkRepository;
            
    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            Product $helper,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
            \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
            \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            \Magento\Catalog\Model\CategoryFactory $categoryFactory,
            CategoryLinkRepositoryInterface $categoryLinkRepository,
            array $data = [])
    {
            $this->helper = $helper;
            $this->_productCollectionFactory = $productCollectionFactory;
            $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
            $this->sourceItemFactory = $sourceItemFactory;
            $this->sourceDataBySku = $sourceDataBySku;
            $this->productStatus = $productStatus;
            $this->categoryFactory = $categoryFactory;
            $this->categoryLinkRepository = $categoryLinkRepository;
            return parent::__construct($context);
    }

    public function execute()
    {
        set_time_limit(300);
        if(isset($_GET["cat"])){
            
            $categoryId = $_GET["cat"];
            if(isset($_GET['p']))
            {
                $page_number = $_GET['p'];
                $size = $_GET['size'];
                $category = $this->categoryFactory->create()->load($categoryId);
                $categoryProducts = $category->getProductCollection()
                        ->addAttributeToSelect('sku')
                        ->setPageSize($size)
                        ->setCurPage($page_number);

                $isProductUnassigned = false;
                foreach ($categoryProducts as $product) {
                    $sku = $product->getSku();

                    try 
                    {
                        $isProductUnassigned = $this->categoryLinkRepository->deleteByIds($categoryId, $sku);
                        echo $sku." - ".$isProductUnassigned."<br>";
                    } catch (Exception $ex) {
                        echo $ex->getMessage();
                        echo $sku." - ".$isProductUnassigned."<br>";
                        continue;
                    }


                }
            }
            else
            {
                $categoryIds = array($categoryId);
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $categoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
                $prods = array(123274,134616,134220,135385,135750,100640,101229,100638,101244,137049,137329,128412,130694,137942,101245,126411,138638,101250,126969,135752,137042,101256,120012,101257,101248,128959,133746,114182,137053,137059,137062,120011,101247,126571,137050,123794,126415,126414,128411,137033,137037,137046,137054,137056,137130,129685,100641,137032,137039,137044,137063,138012,121944,120013,121933,121930,119882,101252,123788,126569,129367,135756,137034,137036,137041,137051,137055,137060,137061,137072,137075,137526,119883,121939,123786,101253,126413,123789,131924,135755,136658,136669,136679,137035,137048,137052,137070,137071,137073,137077,137129,120161,121942,121941,121934,121931,121936,120423,121940,120078,119575,121150,121152,101240,119116,126572,122241,123793,123782,123792,127207,127213,127208,127209,127210,127212,127211,127792,127795,127794,127799,127800,127801,127802,127803,130297,131812,131813,131814,131815,131816,131817,131818,131819,131820,131821,131822,131823,131824,123985,135751,135753,135754,135757,135758,135759,135760,136599,136600,136601,136602,136603,136604,136605,136606,136607,136608,136609,136610,136611,136613,136614,136615,136616,136618,136619,136620,136621,136622,136623,136624,136625,136626,136627,136628,136629,136630,136631,136632,136633,136634,136635,136636,136637,136638,136639,136640,136641,136642,136643,136644,136645,136646,136647,136648,136649,136650,136651,136652,136653,136654,136660,136661,136662,136663,136664,136665,136666,136667,136668,136670,136671,136672,136673,136674,136675,136676,136677,136678,136681,136682,136683,136684,136686,136687,136688,137038,137040,137043,137045,137047,137057,137058,137064,137065,137066,137067,137068,137069,137074,137076,137078,137079,137080,137081,137082,137083,137541,137796,137797,137798,137799,137800,137939,138949,139971);
                foreach ($prods as $sku) {
                    echo $sku."<br/>";
                    $categoryLinkRepository->assignProductToCategories($sku, $categoryIds);
                    //$this->categoryLinkRepository->assignProductToCategories($sku, $categoryId);
                }
            }
            
        }
        

        exit();
    }
}