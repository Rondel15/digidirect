<?php
namespace Digidirect\CustomOptions\Helper;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

class CronCustomOption extends \Magento\Framework\Model\AbstractModel
{
    protected $_productOptions;
    protected $_productRepositoryInterface;
    protected $_productRepository;
    protected $_giftCardHelper;
    protected $_productOptionFactory;
    protected $_logger;

    public function __construct(
        \Magento\Catalog\Model\Product\Option $productOptions,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\Product $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_productOptions = $productOptions;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_productRepository = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productOptionFactory = $productOptionFactory;
        $this->_logger = $logger;
    }

    public function saveCustomOption(){
        $this->logger->info('saveCustomOption()');
        $catIds = array(2564,2567,2570,2573,812,308);
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter( 'price' , array('gt' => 100));
        $collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
        $collection->addAttributeToFilter('brand', array('neq' => 'LEICA'));
        $collection->addAttributeToFilter('brand', array('neq' => 'Leica'));
        $collection->addAttributeToFilter('brand', array('neq' => '139'));
        $collection->addAttributeToFilter('brand', array('neq' => 'Canon'));
        $collection->addAttributeToFilter('brand', array('neq' => 'CANON'));
        $collection->addAttributeToFilter('brand', array('neq' => '120'));
        $collection->addAttributeToFilter('brand', array('neq' => 'DJI'));
        $collection->addAttributeToFilter('brand', array('neq' => '353'));
        $collection->addAttributeToFilter('brand', array('neq' => 'Zhiyun-Tech'));
        $collection->addAttributeToFilter('brand', array('neq' => 'Zhiyun'));
        $collection->addAttributeToFilter('brand', array('neq' => '7125'));
        $collection->addAttributeToFilter('brand', array('neq' => '521'));
        $collection->addAttributeToFilter('stock_group', array('neq' => 'D1A1'));
        $collection->addCategoriesFilter(['nin' => $catIds]);
        //might check 'stock_status' => string '171'
        $x = 0;
        foreach ($collection as $item) {
            $this->_productRepositoryInterface->getById($item->getId());
            $this->_productRepository->load($item->getId());
            $exist = false;
            //check if the custom option exists
            foreach ($this->_productRepository->getOptions() as $option) {
                if($option)
                {
                    if ($option->getTitle() == 'digiProtect') { //&& $option->getGroupByType() == ProductCustomOptionInterface::OPTION_TYPE_FIELD
                        $exist = true;
                    }
                }

            }
            $sku = $this->_productRepository->getSku();

            if (!$exist) {

                $price = $this->_productRepository->getPrice();


                if($price > 1000 && $price < 2000)
                {
                    $optionsArray = [
                        'title' => 'digiProtect',
                        'type' => 'radio',
                        'is_require' => 0,
                        'sort_order' => 1,
                        'values' => [
                            [
                                'title' => '+ 3 years',
                                'price' => 134.95,
                                'price_type' => 'fixed',
                                'sku' => '138939',
                                'sort_order' => 1,
                            ]
                        ]
                    ];
                    $this->logger->info('SKU: ' . $sku . ', digiProtect: +3 years 134.95');
                }
                else if($price > 2000)
                {
                    $optionsArray = [
                        'title' => 'digiProtect',
                        'type' => 'radio',
                        'is_require' => 0,
                        'sort_order' => 1,
                        'values' => [
                            [
                                'title' => '+ 3 years',
                                'price' => 284.95,
                                'price_type' => 'fixed',
                                'sku' => '138940',
                                'sort_order' => 1,
                            ]
                        ]
                    ];
                    $this->logger->info('SKU: ' . $sku . ', digiProtect: +3 years 284.95');
                }
                else
                {
                    $optionsArray = [
                        'title' => 'digiProtect',
                        'type' => 'radio',
                        'is_require' => 0,
                        'sort_order' => 1,
                        'values' => [
                            [
                                'title' => '+ 3 years',
                                'price' => 89.95,
                                'price_type' => 'fixed',
                                'sku' => '139537',
                                'sort_order' => 1,
                            ]
                        ]
                    ];
                    $this->logger->info('SKU: ' . $sku . ', digiProtect: +3 years 89.95');
                }

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                try {

                    $this->_productRepository->setHasOptions(1);
                    $this->_productRepository->setCanSaveCustomOptions(true);
                    $product = $this->_productRepository;

                    $option = $objectManager->create(\Magento\Catalog\Model\Product\Option::class)
                        ->setProductId($product->getId())
                        ->setStoreId($product->getStoreId())
                        ->addData($optionsArray);
                    $option->save();
                    $product->addOption($option);

                    $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->save($product);

//                    $option = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product\Option');
//                    $this->_productRepository->setHasOptions(1);
//                    $this->_productRepository->setCanSaveCustomOptions(true);
//                    $product = $this->_productRepository;
//                    $option->setProductId($this->_productRepository->getData('row_id'))
//                        ->setStoreId($product->getStoreId())
//                        ->addData($optionsArray);
//                    $option->save();
//                    $product->addOption($option);
//                    $product->save();

                } catch (\Exception $exception) {
                    throw new \Magento\Framework\Exception\NoSuchEntityException(__('Something went wrong'));
                }
                $x++;
            }

        }

        return true;

    }

    public function deleteCustomOption(){

        $x = 0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $products = $objectManager->get('\Magento\Catalog\Model\Product')->getCollection();
        $productId = "";
        foreach ($products as $product) {
            $productId = $product->getId();
            //$product = $objectManager->get('\Magento\Catalog\Model\Product')->load($product->getId());
            $product = $objectManager->create('\Magento\Catalog\Model\Product')->load($productId);
            if ($product->getOptions()) {
                //echo "<br /> delete - " .$product->getId();
                foreach ($product->getOptions() as $opt) {
                   $opt->delete();
                }
                $x++;
            }

        }
        //echo "deleted ".$x;
        return true;

    }

}
