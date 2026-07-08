<?php

namespace Digidirect\Pronto\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\Data\CategoryTreeInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;

class Product extends AbstractHelper
{
    const BRAND_ATTRIBUTE_CODE = 'brand';
    /**
     * @var Curl
     */
    protected $curl;
    protected $productRepository;
    protected $attributeOptions = [];
    protected $rootCategoryName;
    protected $failedCategories = [];
    protected $categoryLinkManagement;
    protected $categoryLinkRepository;
    protected $logger;
    protected $_reportCollectionFactory;

    public function __construct(
        Curl $curl,
        JsonSerializer $jsonSerializer,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Digidirect\CustomLog\Logger\Logger $logger,
        ProductResource $productResource,
        CategoryFactory $categoryFactory,
        CategoryLinkManagementInterface $categoryLinkManagement,
        CategoryLinkRepositoryInterface $categoryLinkRepository,
        CategoryManagementInterface $categoryManagement,
        \Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory $reportCollectionFactory
    ){
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->sourceItemsBySku = $sourceItemsBySku;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->productResource = $productResource;
        $this->categoryFactory = $categoryFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->categoryManagement = $categoryManagement;
        $this->_reportCollectionFactory = $reportCollectionFactory;
    }


    public function productSync()
    {
        //set_time_limit(600);
        $startItem = 0;
        $lastCode = 0;

        $this->logger->info('Pronto Product Sync - start item: '.$startItem);

        $this->attributeOptions = $this->getOptionHash('brand');
        $parentID = 2; // default category
        $getCategoryList = $this->getSubCategoryByParentID($parentID);

        //echo 'Pronto Product Sync - start item: '.$startItem."<br/>";
        //$url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem;//.$startitem; //test
        //live port :8084
        $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem;
        $username = 'clint.mercado';
        $password = '849cd5080faff5ce';
        $jsonData = '{}';

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Accept", "application/json");
        $this->curl->addHeader("compcode", "DIG"); //live
        $this->curl->addHeader("user", "ewaveapi");
        $this->curl->addHeader("token", "904241bdbf10efa9");

        //$this->curl->addHeader("compcode", "UA1"); //test
        //$this->curl->addHeader("user", "clint.mercado");
        //$this->curl->addHeader("token", "849cd5080faff5ce");
        // get method
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
        $this->curl->get($url);

        $result = $this->curl->getBody();

        $json = $this->jsonSerializer->unserialize($result);

        //date_update
        foreach ($json['stockmaster']['stockcode'] as $prod)
        {
            $forLogs = "";
            if(!isset($prod['code']))
            {
                exit;
            }

            $lastCode = $prod['code'];

            try {

                $forLogs .= "SKU ".$prod['code']."\n";
                $product = $this->productRepository->get($prod['code']);
//                $product->setStockStatus($prod['stk-stock-status']);
////                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
////                $product->setName($prodname);
                $price = 0;
//                $tax = 10;
                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
                {
                    //$product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
                    //$tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
                    //$forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
                }
                else
                {
                    //$product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
                    //$tax = $prod['pricing']['price-region']['prc-tax-rate'];
                    //$forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
                }
//
//                $pricetocost = floatval($price);
//                $tax = floatval($tax);
//                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                if(isset($prod['stk-replacement-cost']))
//                {
//
//                    $cost = $prod['stk-replacement-cost'];
//                    if($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
//                    {
//                        if(isset($prod['stk-current-buy']))
//                        {
//                            $cost = $prod['stk-current-buy'];
//                            if ($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
//                            {
//                                if(isset($prod['whse-avg-cost-swhs']))
//                                {
//                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price
//
//                                    if ($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
//                                    {
//                                        $pricetocost = floatval($price);
//                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                                    }
//                                }
//
//                            }
//                        }
//                    }
//                }
//
//                echo "cost price ".$cost."<br/>";
//                $product->setCustomAttribute('cost', $cost);
//
//                $marketplacesprice = 0;
//                if(isset($prod['pricing']['price-region'][0]['prc-break-price-4-inc']))
//                {
//                    $marketplacesprice = $prod['pricing']['price-region'][0]['prc-break-price-4-inc'];
//                    if(empty($marketplacesprice))
//                    {
//                        $marketplacesprice = 0;
//                    }
//                }
//                else
//                {
//                    if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
//                    {
//                        $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
//                        if(empty($marketplacesprice))
//                        {
//                            $marketplacesprice = 0;
//                        }
//                    }
//                }
//                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
//                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);
//
//                $endis = "Enabled = 0";
                if($prod['stk-condition-code'] == 'O')
                {
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

                }
                else
                {
                    //web flag
                    //if blank, set to disable
                    if($prod['stk-user-only-alpha4-1'] == '')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'N')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'W')
                    {
                        $isNda = $product->getIsNda();
                        if($isNda)
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                            $endis = 'disabled';
                        }
                        else
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $endis = 'enabled';
                        }

                    }
                    else {

                        $isNda = $product->getIsNda();
                        if($isNda)
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                            $endis = 'disabled';
                        }
                        else
                        {
                            //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            //$endis = 'enabled';
                        }
                        //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

                    }

                }
//                $forLogs .= $endis."\n";
//                //check stk-user-only-alpha4-1 if pre order "P" or awaiting stock "A"
//                if($prod['stk-user-only-alpha4-1'] == 'A')
//                {
//                    //$product->setData('awaiting_product', '1');
//                    $product->setCustomAttribute('awaiting_product', '1');
//                    $awaiting = "Awaiting Product = 1";
//                }
//                else {
//                    //$product->setData('awaiting_product', '0');
//                    $product->setCustomAttribute('awaiting_product', '0');
//                    $awaiting = "Awaiting Product = 0";
//                }

                //stk-user-only-alpha4-3 is_qantas_product
//                if(isset($prod['stk-user-only-alpha4-3']))
//                {
//                    if($prod['stk-user-only-alpha4-3'] == "Q")
//                    {
//                        $product->setCustomAttribute('is_qantas_product', '1');
//                    }
//                    else
//                    {
//                        $product->setCustomAttribute('is_qantas_product', '0');
//                    }
//                }


//                if(isset($prod['stock-division']))
//                {
//                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
//                }
//
//                if(isset($prod['stock-department']))
//                {
//                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
//                }
//
//                if(isset($prod['stock-category']))
//                {
//                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
//                }
//
//                if(isset($prod['stock-class']))
//                {
//                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
//                }

                //$forLogs .= $awaiting."\n";
                //set to pre order
                if($prod['stk-user-only-alpha4-1'] == 'P')
                {
                    //$product->setData('awaiting_product', '1');
                    //$product->setCustomAttribute('pre_order', '1');
                    //$product->setCustomAttribute('preorder', '1');
                    $product->setCustomAttribute('pre_order_status', '1');
                    $forLogs .= "Pre Order 1 \n";
                    //echo "pre_order 1  <br/>";
                }

                //set brands
                if($prod['stk-brand-desc'] == 'digiSeconds')
                {
                    if(isset($prod['d2brand']))
                    {
                        $brandName = strtolower($prod['d2brand']);
                    }
                    else
                    {
                        $brandName = strtolower($prod['stk-brand']);
                    }
                }
                else
                {
                    $brandName = strtolower($prod['stk-brand']);
                }
                $forLogs .= $brandName."\n";
                if($brandName == "thinktank")
                {
                    $brandName = "think tank";
                }
                if($brandName == "peak")
                {
                    $brandName = "peak design";
                }
                if($brandName == "3lt")
                {
                    $brandName = "3 legged thing";
                }
                if(isset($this->attributeOptions[strtolower($brandName)]))
                {
                    $brandCode = $this->attributeOptions[strtolower($brandName)];
                    $product->setBrand($brandCode);
                }

                //disabled, got separate sync for SOH
//                if(isset($prod['warehouse']['whse']))
//                {
//                    foreach ($prod['warehouse']['whse'] as $qt)
//                    {
//                        if(is_array($qt))
//                        {
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($qt['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($qt['qty_available']);
//                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
//                            $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                        }
//                        else
//                        {
//                            // to handle single warehouse
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
//                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
//                            $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                        }
//
//
//                    }
//                }


//                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
//                $product->setCustomAttribute('stock_group', $prod['stock-group']);
//
//                if(isset($prod['qff-store-product-name']))
//                {
//                    $product->setCustomAttribute('qff_store_product_name', $prod['qff-store-product-name']);
//                }
//                if(isset($prod['qff-store-price']))
//                {
//                    $product->setCustomAttribute('qff_store_price', $prod['qff-store-price']);
//                }

//                if($prod['stk-condition-code'] == 'T')
//                {
//                    $stock_condition = 181;
//                }
//                else if ($prod['stk-condition-code'] == 'O')
//                {
//                    $stock_condition = 179;
//                }
//                else
//                {
//                    $stock_condition = 183;
//                }
//
//                $product->setCustomAttribute('stock_condition', $stock_condition);
                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['stk-sort-analysis-code']);
                }
                else if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
                }
                else {
                    $product->setCustomAttribute('item_condition', " ");
                }
                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
                {
                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
                }
                else {
                    $product->setCustomAttribute('item_rating', " ");
                }

                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
                {
                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
                }
                else {
                    $product->setCustomAttribute('d2desc', " ");
                }

                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
                {
                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
                }
                else {
                    $product->setCustomAttribute('d2newsku', " ");
                }

                if(isset($prod['stk-storage-type-flag']))
                {
                    if($prod['stk-storage-type-flag'] == 'H')
                    {
                        $product->setCustomAttribute('dangerous_goods', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('dangerous_goods', '0');
                    }

                    if($prod['stk-storage-type-flag'] == 'B')
                    {
                        $product->setCustomAttribute('bulky_item', 1);
                    }
                    else
                    {
                        $product->setCustomAttribute('bulky_item', 0);
                    }

                }
                else
                {
                    $product->setCustomAttribute('dangerous_goods', '0');
                    $product->setCustomAttribute('bulky_item', 0);

                }

                $product->setCustomAttribute('marketplacer_seller', 20329);

                $productSales = $this->getProductSales($product->getId(), $price);
                $product->setCustomAttribute('nb_sales', $productSales); //bestseller attribute for sorting

                $today = date('Y-m-d');
                $product->setCustomAttribute('date_update', $today);
                //magento bug, need to save first then assign categories
                $this->productRepository->save($product);

                $parent = "";
                $subcat1 = "";
                $subcat2 = "";
                $subcat3 = "";

                //set categories
                $categoryIds = array();
                $catList = "";
                $productCategoryIds = $product->getCategoryIds();
                $shouldupdate = false;


                if (count($getCategoryList))
                {
                    foreach ($getCategoryList as $id => $category)
                    {
                        //digiSeconds
                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }

                        //digiSeconds
                        if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                        {
                            if($prod['stk-sort-analysis-code'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['stk-sort-analysis-code'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                        }
                        else if(isset($prod['d2lvl1']))
                        {
                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }

                        if($prod['web-category1'] == 'Cameras')
                        {
                            $prod['web-category1'] = 'Digital Cameras';
                        }

                        if($category['name'] == $prod['web-category1'])
                        {
                            if($parent == "")
                            {
                                if($category['parent_id'] == '2')
                                {
                                    $parent = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
                                    $categoryIds[] = $category['id'];
                                }

                            }

                        }
                        if(isset($prod['web-category2']))
                        {
                            if($prod['web-category2'] == 'Gaming')
                            {
                                $prod['web-category2'] = 'Gaming Products';
                            }

                            if($category['name'] == $prod['web-category2'])
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category3']))
                        {
                            if($prod['web-category3'] == 'Fujifilm Instant Cameras')
                            {
                                $prod['web-category3'] = 'Fujifilm Instant Instax Cameras';
                            }

                            if($prod['web-category3'] == 'Cables & Adaptors')
                            {
                                $prod['web-category3'] = 'Computer Cables & Adaptors';
                            }

                            if($prod['web-category3'] == 'Cases Covers & Bags')
                            {
                                $prod['web-category3'] = 'Laptop Cases, Covers & Bags';
                            }

                            if($prod['web-category3'] == 'Chargers')
                            {
                                $prod['web-category3'] = 'Laptop Chargers';
                            }

                            if($prod['web-category3'] == 'Hubs & Docks')
                            {
                                $prod['web-category3'] = 'Computer Hubs & Docks';
                            }

                            if($prod['web-category3'] == 'Webcams')
                            {
                                $prod['web-category3'] = 'Computer Webcams';
                            }

                            if($prod['web-category3'] == 'Console Accessories')
                            {
                                $prod['web-category3'] = 'Console Gaming Accessories';
                            }

                            if($prod['web-category3'] == 'Consoles')
                            {
                                $prod['web-category3'] = 'Gaming Consoles';
                            }

                            if($prod['web-category3'] == 'Business')
                            {
                                $prod['web-category3'] = 'Business Laptops';
                            }

                            if($prod['web-category3'] == 'Home & Student')
                            {
                                $prod['web-category3'] = 'Home & Student Laptops';
                            }

                            if($prod['web-category3'] == 'Monitor Accessories')
                            {
                                $prod['web-category3'] = 'Computer Monitor Accessories';
                            }

                            if($prod['web-category3'] == 'Monitor Mounts & Stands')
                            {
                                $prod['web-category3'] = 'Monitor Arms, Mounts & Stands';
                            }

                            if($prod['web-category3'] == 'Monitors')
                            {
                                $prod['web-category3'] = 'Computer Monitors';
                            }

                            if($prod['web-category3'] == 'Ink')
                            {
                                $prod['web-category3'] = 'Printer Ink';
                            }

                            if($prod['web-category3'] == 'Paper')
                            {
                                $prod['web-category3'] = 'Photo Printing Papers';
                            }

                            if($prod['web-category3'] == 'Shredders')
                            {
                                $prod['web-category3'] = 'Paper Shredders';
                            }

                            if($prod['web-category3'] == 'Light Meters')
                            {
                                $prod['web-category3'] = 'Light Meters for Cameras';
                            }

                            if($category['name'] == $prod['web-category3'])
                            {
                                if($category['parent_id'] == $subcat1)
                                {
                                    $subcat2 = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category4']))
                        {

                            if($category['name'] == $prod['web-category4'])
                            {
                                if($category['parent_id'] == $subcat2)
                                {
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                    }
                }
                //echo $catList."<br>";
                $forLogs .= $catList."\n";
                //comment out for now until bugged category is fixed May 6, 2024
                if (count($categoryIds)) {

                    $forLogs .= "Categories: ".$catList."\n";
                    //echo "update categories: ".$catList."<br />";
                    try
                    {
                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        try
                        {
                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $forLogs .=   $e->getMessage();
                        }
                    }

                    //$product->setCategoryIds($categoryIds);
                }


            } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                continue;
                //insert new product
//                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
//                $prodname = trim($prodname," ");
//                $forLogs .= "Product Name: ".$prodname."\n";
//                $forLogs .= "SKU: ".$prod['code']."\n";
//                $product = $this->productFactory->create();
//                $product->setSku($prod['code']);
//                $product->setName($prodname);
//                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
//                $product->setVisibility(4);
//                $product->setAttributeSetId(4);
//
//                $price = 0;
//                $tax = 10;
//                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
//                {
//                    $product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
//                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
//                    $tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
//                    $forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
//                }
//                else
//                {
//                    $product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
//                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
//                    $tax = $prod['pricing']['price-region']['prc-tax-rate'];
//                    $forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
//                }
//
//                $pricetocost = floatval($price);
//                $tax = floatval($tax);
//                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                if(isset($prod['stk-replacement-cost']))
//                {
//
//                    $cost = $prod['stk-replacement-cost'];
//                    if($cost == '0' || $cost == '')
//                    {
//                        if(isset($prod['stk-current-buy']))
//                        {
//                            $cost = $prod['stk-current-buy'];
//                            if ($cost == '0' || $cost == '')
//                            {
//                                if(isset($prod['whse-avg-cost-swhs']))
//                                {
//                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price
//
//                                    if ($cost == '0' || $cost == '')
//                                    {
//                                        $pricetocost = floatval($price);
//                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                                    }
//                                }
//
//
//                            }
//                        }
//                    }
//                }
//
//                $product->setCustomAttribute('cost', $cost);
//
//                $marketplacesprice = 0;
//                if(isset($prod['pricing']['price-region'][0]['prc-break-price-4-inc']))
//                {
//                    $marketplacesprice = $prod['pricing']['price-region'][0]['prc-break-price-4-inc'];
//                    if(empty($marketplacesprice))
//                    {
//                        $marketplacesprice = 0;
//                    }
//                }
//                else
//                {
//                    if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
//                    {
//                        $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
//                        if(empty($marketplacesprice))
//                        {
//                            $marketplacesprice = 0;
//                        }
//                    }
//                }
//                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
//                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);
//
//                //set brand
//                if($prod['stk-brand-desc'] == 'digiSeconds')
//                {
//                    if(isset($prod['d2brand']))
//                    {
//                        $brandName = strtolower($prod['d2brand']);
//                    }
//                    else
//                    {
//                        $brandName = strtolower($prod['stk-brand']);
//                    }
//                }
//                else
//                {
//                    $brandName = strtolower($prod['stk-brand']);
//                }
//                $forLogs .= "Brand: ".$brandName."\n";
//                if(isset($this->attributeOptions[strtolower($brandName)]))
//                {
//                    $brandCode = $this->attributeOptions[strtolower($brandName)];
//                    $product->setBrand($brandCode);
//                }
//
//                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
////                // If desired, you can set a tax class like so:
////                //$product->setCustomAttribute('tax_class_id', $taxClassId);
//
//                $toUrl = $prodname;
//                $toUrl = preg_replace('/[+]/', "plus", $toUrl);
//                $urltext = preg_replace('#[^0-9a-z]+#i', '-', $toUrl);
//                $urltext = strtolower($urltext);
//                $product->setUrlKey($urltext);
//
//                // set gtin and apn
//                $barcode1 = "";
//                $barcode2 = "";
//                $barcode3 = "";
//                $barcode4 = "";
//                if(isset($prod['gtins']['gtin'])) {
//                    //set barcode
//                    if (count($prod['gtins']['gtin']) == count($prod['gtins']['gtin'], COUNT_RECURSIVE)) {
//                        $forLogs .= "barcode1 ".$prod['gtins']['gtin']['id']."\n";
//                        $barcode1 = $prod['gtins']['gtin']['id'];
//
//                    } else {
//                        $x = 1;
//                        foreach ($prod['gtins']['gtin'] as $gtin) {
//                            switch ($x)
//                            {
//                                case 1:
//                                    $barcode1 = $gtin['id'];
//                                    break;
//                                case 2:
//                                    $barcode2 = $gtin['id'];
//                                    break;
//                                case 3:
//                                    $barcode3 = $gtin['id'];
//                                    break;
//                                case 4:
//                                    $barcode4 = $gtin['id'];
//                                    break;
//                                default:
//
//                            }
//                            $x++;
//                        }
//                    }
//                }
//
//                $product->setCustomAttribute('barcode1',$barcode1);
//                $product->setCustomAttribute('barcode2',$barcode2);
//                $product->setCustomAttribute('barcode3',$barcode3);
//                $product->setCustomAttribute('barcode4',$barcode4);
//                $forLogs .= "barcode1 ".$barcode1."\n";
//                $forLogs .= "barcode2 ".$barcode2."\n";
//                $forLogs .= "barcode3 ".$barcode3."\n";
//                $forLogs .= "barcode4 ".$barcode4."\n";
//
//                if(isset($prod['warehouse']['whse']))
//                {
//
//                    foreach ($prod['warehouse']['whse'] as $qt)
//                    {
//                        if(is_array($qt))
//                        {
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($qt['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($qt['qty_available']);
//                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
//                            $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                        }
//                        else
//                        {
//                            // to handle single warehouse
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
//                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
//                            $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                        }
//
//                    }
//                }
//
//                //disable first. this might be causing issue on sync
////                $sourceItem = $this->sourceItemFactory->create();
////                $sourceItem->setSourceCode('default');
////                $sourceItem->setSku($prod['code']);
////                $sourceItem->setStatus(1);
////                $sourceItem->setQuantity(0);
////                $forLogs .="default - 0 \n";
////                $this->sourceItemsSaveInterface->execute([$sourceItem]);
//
//                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
//                $product->setCustomAttribute('stock_group', $prod['stock-group']);
//                $product->setCustomAttribute('qff_base', $prod['qff-base-points-per-dollar']);
//                $product->setCustomAttribute('qff_bonus_points', $prod['qff-bonus-points-per-dollar']);
//
//                if(isset($prod['qff-store-product-name']))
//                {
//                    $product->setCustomAttribute('qff_store_product_name', $prod['qff-store-product-name']);
//                }
//                if(isset($prod['qff-store-price']))
//                {
//                    $product->setCustomAttribute('qff_store_price', $prod['qff-store-price']);
//                }
//
//                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
//                if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
//                {
//                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
//                }
//                else {
//                    $product->setCustomAttribute('item_condition', " ");
//                }
//                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
//                {
//                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
//                }
//                else {
//                    $product->setCustomAttribute('item_rating', " ");
//                }
//
//                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
//                {
//                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
//                }
//                else {
//                    $product->setCustomAttribute('d2desc', " ");
//                }
//
//                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
//                {
//                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
//                }
//                else {
//                    $product->setCustomAttribute('d2newsku', " ");
//                }
//
//                if(isset($prod['stk-storage-type-flag']))
//                {
//                    if($prod['stk-storage-type-flag'] == 'H')
//                    {
//                        $product->setCustomAttribute('dangerous_goods', '1');
//                    }
//                    else
//                    {
//                        $product->setCustomAttribute('dangerous_goods', '0');
//                    }
//
//                    if($prod['stk-storage-type-flag'] == 'B')
//                    {
//                        $product->setCustomAttribute('bulky_item', 1);
//                    }
//                    else
//                    {
//                        $product->setCustomAttribute('bulky_item', 0);
//                    }
//
//                }
//                else
//                {
//                    $product->setCustomAttribute('dangerous_goods', '0');
//                    $product->setCustomAttribute('bulky_item', 0);
//                }
//
//                $product->setCustomAttribute('marketplacer_seller', 20329);
//
//                $today = date('Y-m-d');
//                $product->setCustomAttribute('date_update', $today);
//                $product->setCustomAttribute('is_nda', 1);
//
//                if(isset($prod['stock-division']))
//                {
//                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
//                }
//
//                if(isset($prod['stock-department']))
//                {
//                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
//                }
//
//                if(isset($prod['stock-category']))
//                {
//                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
//                }
//
//                if(isset($prod['stock-class']))
//                {
//                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
//                }
//
//                $this->productRepository->save($product);
//
//                $parent = "";
//                $subcat1 = "";
//                $subcat2 = "";
//                $subcat3 = "";
//
//                //set categories
//                $categoryIds = array();
//                $catList = "";
//                $productCategoryIds = $product->getCategoryIds();
//                $shouldupdate = false;
//
//
//                if (count($getCategoryList))
//                {
//                    foreach ($getCategoryList as $id => $category)
//                    {
//                        //digiSeconds
//                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
//                        {
//                            $catList .= $category['name'] . " - " .$category['id']." : ";
//                            $categoryIds[] = $category['id'];
//                        }
//
//                        //digiSeconds
//                        if(isset($prod['d2lvl1']))
//                        {
//                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
//                            {
//                                $catList .= $category['name'] . " - " .$category['id']." : ";
//                                $categoryIds[] = $category['id'];
//                            }
//
//                            //digiSeconds
//                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
//                            {
//                                $catList .= $category['name'] . " - " .$category['id']." : ";
//                                $categoryIds[] = $category['id'];
//                            }
//
//                            //digiSeconds
//                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
//                            {
//                                $catList .= $category['name'] . " - " .$category['id']." : ";
//                                $categoryIds[] = $category['id'];
//                            }
//                        }
//
//
//                        if($prod['web-category1'] == 'Cameras')
//                        {
//                            $prod['web-category1'] = 'Digital Cameras';
//                        }
//
//                        if($category['name'] == $prod['web-category1'])
//                        {
//                            if($parent == "")
//                            {
//                                if($category['parent_id'] == '2')
//                                {
//                                    $parent = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//
//                            }
//
//                        }
//                        if(isset($prod['web-category2']))
//                        {
//                            if($prod['web-category2'] == 'Gaming')
//                            {
//                                $prod['web-category2'] = 'Gaming Products';
//                            }
//
//                            if($category['name'] == $prod['web-category2'])
//                            {
//                                if($category['parent_id'] == $parent)
//                                {
//                                    $subcat1 = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
//                            {
//                                if($category['parent_id'] == $parent)
//                                {
//                                    $subcat1 = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                        }
//                        if(isset($prod['web-category3']))
//                        {
//
//                            if($prod['web-category3'] == 'Fujifilm Instant Cameras')
//                            {
//                                $prod['web-category3'] = 'Fujifilm Instant Instax Cameras';
//                            }
//
//                            if($prod['web-category3'] == 'Cables & Adaptors')
//                            {
//                                $prod['web-category3'] = 'Computer Cables & Adaptors';
//                            }
//
//                            if($prod['web-category3'] == 'Cases Covers & Bags')
//                            {
//                                $prod['web-category3'] = 'Laptop Cases, Covers & Bags';
//                            }
//
//                            if($prod['web-category3'] == 'Chargers')
//                            {
//                                $prod['web-category3'] = 'Laptop Chargers';
//                            }
//
//                            if($prod['web-category3'] == 'Hubs & Docks')
//                            {
//                                $prod['web-category3'] = 'Computer Hubs & Docks';
//                            }
//
//                            if($prod['web-category3'] == 'Webcams')
//                            {
//                                $prod['web-category3'] = 'Computer Webcams';
//                            }
//
//                            if($prod['web-category3'] == 'Console Accessories')
//                            {
//                                $prod['web-category3'] = 'Console Gaming Accessories';
//                            }
//
//                            if($prod['web-category3'] == 'Consoles')
//                            {
//                                $prod['web-category3'] = 'Gaming Consoles';
//                            }
//
//                            if($prod['web-category3'] == 'Business')
//                            {
//                                $prod['web-category3'] = 'Business Laptops';
//                            }
//
//                            if($prod['web-category3'] == 'Home & Student')
//                            {
//                                $prod['web-category3'] = 'Home & Student Laptops';
//                            }
//
//                            if($prod['web-category3'] == 'Monitor Accessories')
//                            {
//                                $prod['web-category3'] = 'Computer Monitor Accessories';
//                            }
//
//                            if($prod['web-category3'] == 'Monitor Mounts & Stands')
//                            {
//                                $prod['web-category3'] = 'Monitor Arms, Mounts & Stands';
//                            }
//
//                            if($prod['web-category3'] == 'Monitors')
//                            {
//                                $prod['web-category3'] = 'Computer Monitors';
//                            }
//
//                            if($prod['web-category3'] == 'Ink')
//                            {
//                                $prod['web-category3'] = 'Printer Ink';
//                            }
//
//                            if($prod['web-category3'] == 'Paper')
//                            {
//                                $prod['web-category3'] = 'Photo Printing Papers';
//                            }
//
//                            if($prod['web-category3'] == 'Shredders')
//                            {
//                                $prod['web-category3'] = 'Paper Shredders';
//                            }
//
//                            if($prod['web-category3'] == 'Light Meters')
//                            {
//                                $prod['web-category3'] = 'Light Meters for Cameras';
//                            }
//
//                            if($category['name'] == $prod['web-category3'])
//                            {
//                                if($category['parent_id'] == $subcat1)
//                                {
//                                    $subcat2 = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                        }
//                        if(isset($prod['web-category4']))
//                        {
//
//                            if($category['name'] == $prod['web-category4'])
//                            {
//                                if($category['parent_id'] == $subcat2)
//                                {
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                        }
//                    }
//                }
//                //echo $catList."<br>";
//                $forLogs .= $catList."\n";
//                //comment out for now until bugged category is fixed May 6, 2024
//                if (count($categoryIds)) {
//
//                    $forLogs .= "Categories: ".$catList."\n";
//                    //echo "update categories: ".$catList."<br />";
//                    try
//                    {
//                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
//                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
//                        try
//                        {
//                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
//                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
//                            $forLogs .=   $e->getMessage();
//                        }
//                    }
//                }

            }

            $this->logger->info($forLogs);

        }

        if(isset($json['response']['status']) && $json['response']['status'] == 'FAIL')
        {
            $this->logger->info($json['response']['message']);

        }

        //$this->logger->info($forLogs);

        $this->productSyncContinue($lastCode);

    }

    public function productSyncContinue($startItem)
    {

        $lastCode = $startItem;
        $this->attributeOptions = $this->getOptionHash('brand');

        $parentID = 2; // default category
        $getCategoryList = $this->getSubCategoryByParentID($parentID);

        $this->logger->info('Pronto Product Sync - start item: '.$startItem);
        //echo 'Pronto Product Sync - start item: '.$startItem."<br/>";
        //$this->logger->info('Pronto Product Sync - start item: '.$startItem);
        //$url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem;//.$startitem; //test
        //live port :8084
        $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem;
        $username = 'clint.mercado';
        $password = '849cd5080faff5ce';
        $jsonData = '{}';

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Accept", "application/json");
        $this->curl->addHeader("compcode", "DIG"); //live
        $this->curl->addHeader("user", "ewaveapi");
        $this->curl->addHeader("token", "904241bdbf10efa9");

        //$this->curl->addHeader("compcode", "UA1"); //test
        //$this->curl->addHeader("user", "clint.mercado");
        //$this->curl->addHeader("token", "849cd5080faff5ce");
        // get method
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
        $this->curl->get($url);

        $result = $this->curl->getBody();

        $json = $this->jsonSerializer->unserialize($result);

        foreach ($json['stockmaster']['stockcode'] as $prod)
        {
            $forLogs = "";

            if(!isset($prod['code']))
            {
                exit;
            }

            $lastCode = $prod['code'];
            try {

                $forLogs .= "SKU ".$prod['code']."\n";
                $product = $this->productRepository->get($prod['code']);
                //$product->setStockStatus($prod['stk-stock-status']);
//                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
//                $product->setName($prodname);
                $price = 0;
                $tax = 10;
                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
                {
                    //$product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
                    //$tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
                    //$forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
                }
                else
                {
                    //$product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
                    //$tax = $prod['pricing']['price-region']['prc-tax-rate'];
                    //$forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
                }

//                $pricetocost = floatval($price);
//                $tax = floatval($tax);
//                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                if(isset($prod['stk-replacement-cost']))
//                {
//
//                    $cost = $prod['stk-replacement-cost'];
//                    if($cost == '0' || $cost == '')
//                    {
//                        if(isset($prod['stk-current-buy']))
//                        {
//                            $cost = $prod['stk-current-buy'];
//                            if ($cost == '0' || $cost == '')
//                            {
//                                if(isset($prod['whse-avg-cost-swhs']))
//                                {
//                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price
//
//                                    if ($cost == '0' || $cost == '')
//                                    {
//                                        $pricetocost = floatval($price);
//                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                                    }
//                                }
//
//
//                            }
//                        }
//                    }
//                }
//
//                $product->setCustomAttribute('cost', $cost);
//
//                $marketplacesprice = 0;
//                if(isset($prod['pricing']['price-region'][0]['prc-break-price-4-inc']))
//                {
//                    $marketplacesprice = $prod['pricing']['price-region'][0]['prc-break-price-4-inc'];
//                    if(empty($marketplacesprice))
//                    {
//                        $marketplacesprice = 0;
//                    }
//                }
//                else
//                {
//                    if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
//                    {
//                        $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
//                        if(empty($marketplacesprice))
//                        {
//                            $marketplacesprice = 0;
//                        }
//                    }
//                }
//                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
//                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);

                $forLogs .= "Stock Condition ".$prod['stk-condition-code']."\n";
                //$endis = "Enabled = 0";
                if($prod['stk-condition-code'] == 'O')
                {
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

                }
                else
                {
                    //web flag
                    //if blank, set to disable
                    if($prod['stk-user-only-alpha4-1'] == '')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'N')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'W')
                    {
                        $isNda = $product->getIsNda();
                        if($isNda)
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                            $endis = 'disabled';
                        }
                        else
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            //$endis = 'enabled';
                        }

                    }
                    else {
//                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
//                        $endis = "Enabled = 1";
                    }

                }
                //$forLogs .= $endis."\n";
                //comment out for now 13-05-24
                //check stk-user-only-alpha4-1 if pre order "P" or awaiting stock "A"
                if($prod['stk-user-only-alpha4-1'] == 'A')
                {
                    //$product->setData('awaiting_product', '1');
                    $product->setCustomAttribute('awaiting_product', '1');
                    $awaiting = "Awaiting Product = 1";
                }
                else {
                    //$product->setData('awaiting_product', '0');
                    $product->setCustomAttribute('awaiting_product', '0');
                    $awaiting = "Awaiting Product = 0";
                }
                $forLogs .= $awaiting."\n";

                if(isset($prod['stock-division']))
                {
                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
                }

                if(isset($prod['stock-department']))
                {
                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
                }

                if(isset($prod['stock-category']))
                {
                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
                }

                if(isset($prod['stock-class']))
                {
                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
                }

                //set to pre order
                if($prod['stk-user-only-alpha4-1'] == 'P')
                {
                    //$product->setData('awaiting_product', '1');
                    //$product->setCustomAttribute('pre_order', '1');
                    //$product->setCustomAttribute('preorder', '1');
                    $product->setCustomAttribute('pre_order_status', '1');
                    $forLogs .= "Pre Order 1 \n";
                    //echo "pre_order 1  <br/>";
                }

                //set brands
                if($prod['stk-brand-desc'] == 'digiSeconds')
                {
                    if(isset($prod['d2brand']))
                    {
                        $brandName = strtolower($prod['d2brand']);
                    }
                    else
                    {
                        $brandName = strtolower($prod['stk-brand']);
                    }
                }
                else
                {
                    $brandName = strtolower($prod['stk-brand']);
                }
                $forLogs .= $brandName."\n";
                if($brandName == "thinktank")
                {
                    $brandName = "think tank";
                }
                if($brandName == "peak")
                {
                    $brandName = "peak design";
                }
                if($brandName == "3lt")
                {
                    $brandName = "3 legged thing";
                }
                if(isset($this->attributeOptions[strtolower($brandName)]))
                {
                    $brandCode = $this->attributeOptions[strtolower($brandName)];
                    $product->setBrand($brandCode);
                }

                //disabled, got separate sync for SOH
//                if(isset($prod['warehouse']['whse']))
//                {
//                    foreach ($prod['warehouse']['whse'] as $qt)
//                    {
//                        if(is_array($qt))
//                        {
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($qt['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($qt['qty_available']);
//                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
//                            $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                        }
//                        else
//                        {
//                            // to handle single warehouse
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
//                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
//                            $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                        }
//                    }
//                }

//                $sourceItem = $this->sourceItemFactory->create();
//                $sourceItem->setSourceCode('default');
//                $sourceItem->setSku($prod['code']);
//                $sourceItem->setStatus(1);
//                $sourceItem->setQuantity(0);
//                $forLogs .="default - 0 \n";
//                $this->sourceItemsSaveInterface->execute([$sourceItem]);


//                if($prod['stk-condition-code'] == 'T')
//                {
//                    $stock_condition = 181;
//                }
//                else if ($prod['stk-condition-code'] == 'O')
//                {
//                    $stock_condition = 179;
//                }
//                else
//                {
//                    $stock_condition = 183;
//                }
//                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
//                $product->setCustomAttribute('stock_group', $prod['stock-group']);
//                $product->setCustomAttribute('stock_condition', $stock_condition);

                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['stk-sort-analysis-code']);
                }
                else if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
                }
                else {
                    $product->setCustomAttribute('item_condition', " ");
                }
                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
                {
                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
                }
                else {
                    $product->setCustomAttribute('item_rating', " ");
                }

                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
                {
                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
                }
                else {
                    $product->setCustomAttribute('d2desc', " ");
                }

                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
                {
                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
                }
                else {
                    $product->setCustomAttribute('d2newsku', " ");
                }

                if(isset($prod['stk-storage-type-flag']))
                {
                    if($prod['stk-storage-type-flag'] == 'H')
                    {
                        $product->setCustomAttribute('dangerous_goods', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('dangerous_goods', '0');
                    }

                    if($prod['stk-storage-type-flag'] == 'B')
                    {
                        $product->setCustomAttribute('bulky_item', 1);
                    }
                    else
                    {
                        $product->setCustomAttribute('bulky_item', 0);
                    }

                }
                else
                {
                    $product->setCustomAttribute('dangerous_goods', '0');
                    $product->setCustomAttribute('bulky_item', 0);
                }

                $product->setCustomAttribute('marketplacer_seller', 20329);

                $productSales = $this->getProductSales($product->getId(), $price);
                $product->setCustomAttribute('nb_sales', $productSales); //bestseller attribute for sorting

                $today = date('Y-m-d');
                $product->setCustomAttribute('date_update', $today);

                $this->productRepository->save($product);
                //echo "update ".$lastCode ."<br/>";

                //comment out. put in different cron
                $parent = "";
                $subcat1 = "";
                $subcat2 = "";
                $subcat3 = "";

                //set categories
                $categoryIds = array();
                $catList = "";
                $productCategoryIds = $product->getCategoryIds();
                $shouldupdate = false;


                if (count($getCategoryList))
                {
                    foreach ($getCategoryList as $id => $category)
                    {
                        //digiSeconds
                        //group codes, division, department DS
                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }

                        //digiSeconds
                        if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                        {
                            if($prod['stk-sort-analysis-code'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['stk-sort-analysis-code'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                        }
                        else if(isset($prod['d2lvl1']))
                        {
                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }

                        if($prod['web-category1'] == 'Digital Cameras')
                        {
                            $prod['web-category1'] = 'Cameras';
                        }

                        if($category['name'] == $prod['web-category1'])
                        {
                            if($parent == "")
                            {
                                if($category['parent_id'] == '2')
                                {
                                    $parent = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
                                    $categoryIds[] = $category['id'];
                                }

                            }

                        }
                        if(isset($prod['web-category2']))
                        {
                            if($prod['web-category2'] == 'Gaming')
                            {
                                $prod['web-category2'] = 'Gaming Products';
                            }

                            if($category['name'] == $prod['web-category2'])
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category3']))
                        {
                            if($prod['web-category3'] == 'Fujifilm Instant Cameras')
                            {
                                $prod['web-category3'] = 'Fujifilm Instant Instax Cameras';
                            }

                            if($prod['web-category3'] == 'Cables & Adaptors')
                            {
                                $prod['web-category3'] = 'Computer Cables & Adaptors';
                            }

                            if($prod['web-category3'] == 'Cases Covers & Bags')
                            {
                                $prod['web-category3'] = 'Laptop Cases, Covers & Bags';
                            }

                            if($prod['web-category3'] == 'Chargers')
                            {
                                $prod['web-category3'] = 'Laptop Chargers';
                            }

                            if($prod['web-category3'] == 'Hubs & Docks')
                            {
                                $prod['web-category3'] = 'Computer Hubs & Docks';
                            }

                            if($prod['web-category3'] == 'Webcams')
                            {
                                $prod['web-category3'] = 'Computer Webcams';
                            }

                            if($prod['web-category3'] == 'Console Accessories')
                            {
                                $prod['web-category3'] = 'Console Gaming Accessories';
                            }

                            if($prod['web-category3'] == 'Consoles')
                            {
                                $prod['web-category3'] = 'Gaming Consoles';
                            }

                            if($prod['web-category3'] == 'Business')
                            {
                                $prod['web-category3'] = 'Business Laptops';
                            }

                            if($prod['web-category3'] == 'Home & Student')
                            {
                                $prod['web-category3'] = 'Home & Student Laptops';
                            }

                            if($prod['web-category3'] == 'Monitor Accessories')
                            {
                                $prod['web-category3'] = 'Computer Monitor Accessories';
                            }

                            if($prod['web-category3'] == 'Monitor Mounts & Stands')
                            {
                                $prod['web-category3'] = 'Monitor Arms, Mounts & Stands';
                            }

                            if($prod['web-category3'] == 'Monitors')
                            {
                                $prod['web-category3'] = 'Computer Monitors';
                            }

                            if($prod['web-category3'] == 'Ink')
                            {
                                $prod['web-category3'] = 'Printer Ink';
                            }

                            if($prod['web-category3'] == 'Paper')
                            {
                                $prod['web-category3'] = 'Photo Printing Papers';
                            }

                            if($prod['web-category3'] == 'Shredders')
                            {
                                $prod['web-category3'] = 'Paper Shredders';
                            }

                            if($prod['web-category3'] == 'Light Meters')
                            {
                                $prod['web-category3'] = 'Light Meters for Cameras';
                            }


                            if($category['name'] == $prod['web-category3'])
                            {
                                if($category['parent_id'] == $subcat1)
                                {
                                    $subcat2 = $category['id'];
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category4']))
                        {

                            if($category['name'] == $prod['web-category4'])
                            {
                                if($category['parent_id'] == $subcat2)
                                {
                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                    }
                }
                //echo $catList."<br>";
                $forLogs .= $catList."\n";
                //comment out for now until bugged category is fixed May 6, 2024
                if (count($categoryIds)) {

                    $forLogs .= "Categories: ".$catList."\n";
                    //echo "update categories: ".$catList."<br />";
                    try
                    {
                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        try
                        {
                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $forLogs .=   $e->getMessage();
                        }
                    }
                }


            } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                continue;
//                //insert new product
//                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
//                $prodname = trim($prodname," ");
//                $forLogs .= "Product Name: ".$prodname."\n";
//                $forLogs .= "SKU: ".$prod['code']."\n";
//                $product = $this->productFactory->create();
//                $product->setSku($prod['code']);
//                $product->setName($prodname);
//                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
//                $product->setVisibility(4);
//                $product->setAttributeSetId(4);
//                $price = 0;
//                $tax = 10;
//                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
//                {
//                    $product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
//                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
//                    $tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
//                    $forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
//                }
//                else
//                {
//                    $product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
//                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
//                    $tax = $prod['pricing']['price-region']['prc-tax-rate'];
//                    $forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
//                }
//
//
//                $pricetocost = floatval($price);
//                $tax = floatval($tax);
//                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                if(isset($prod['stk-replacement-cost']))
//                {
//
//                    $cost = $prod['stk-replacement-cost'];
//                    if($cost == '0' || $cost == '')
//                    {
//                        if(isset($prod['stk-current-buy']))
//                        {
//                            $cost = $prod['stk-current-buy'];
//                            if ($cost == '0' || $cost == '')
//                            {
//                                if(isset($prod['whse-avg-cost-swhs']))
//                                {
//                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price
//
//                                    if ($cost == '0' || $cost == '')
//                                    {
//                                        $pricetocost = floatval($price);
//                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
//                                    }
//                                }
//
//
//                            }
//                        }
//                    }
//                }
//
//                $product->setCustomAttribute('cost', $cost);
//
//                $marketplacesprice = 0;
//                if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
//                {
//                    $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
//                    if(empty($marketplacesprice))
//                    {
//                        $marketplacesprice = 0;
//                    }
//                }
//                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
//                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);
//
//                //set brands
//                if($prod['stk-brand-desc'] == 'digiSeconds')
//                {
//                    if(isset($prod['d2brand']))
//                    {
//                        $brandName = strtolower($prod['d2brand']);
//                    }
//                    else
//                    {
//                        $brandName = strtolower($prod['stk-brand']);
//                    }
//                }
//                else
//                {
//                    $brandName = strtolower($prod['stk-brand']);
//                }
//                $forLogs .= "Brand: ".$brandName."\n";
//                if(isset($this->attributeOptions[strtolower($brandName)]))
//                {
//                    $brandCode = $this->attributeOptions[strtolower($brandName)];
//                    $product->setBrand($brandCode);
//                }
//
//                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
////                // If desired, you can set a tax class like so:
////                //$product->setCustomAttribute('tax_class_id', $taxClassId);
//                $toUrl = $prodname;
//                $toUrl = preg_replace('/[+]/', 'plus', $toUrl);
//                $urltext = preg_replace('#[^0-9a-z]+#i', '-', $toUrl);
//                $urltext = strtolower($urltext);
//                $product->setUrlKey($urltext);
//
//                // set gtin and apn
//                $barcode1 = "";
//                $barcode2 = "";
//                $barcode3 = "";
//                $barcode4 = "";
//                if(isset($prod['gtins']['gtin'])) {
//                    //set barcode
//                    if (count($prod['gtins']['gtin']) == count($prod['gtins']['gtin'], COUNT_RECURSIVE)) {
//                        $forLogs .= "barcode1 ".$prod['gtins']['gtin']['id']."\n";
//                        $barcode1 = $prod['gtins']['gtin']['id'];
//
//                    } else {
//                        $x = 1;
//                        foreach ($prod['gtins']['gtin'] as $gtin)
//                        {
//                            switch ($x)
//                            {
//                                case 1:
//                                    $barcode1 = $gtin['id'];
//                                    break;
//                                case 2:
//                                    $barcode2 = $gtin['id'];
//                                    break;
//                                case 3:
//                                    $barcode3 = $gtin['id'];
//                                    break;
//                                case 4:
//                                    $barcode4 = $gtin['id'];
//                                    break;
//                                default:
//
//                            }
//                            $x++;
//                        }
//                    }
//                }
//                //work around to set
//                $product->setCustomAttribute('barcode1',$barcode1);
//                $product->setCustomAttribute('barcode2',$barcode2);
//                $product->setCustomAttribute('barcode3',$barcode3);
//                $product->setCustomAttribute('barcode4',$barcode4);
//                $forLogs .= "barcode1 ".$barcode1."\n";
//                $forLogs .= "barcode2 ".$barcode2."\n";
//                $forLogs .= "barcode3 ".$barcode3."\n";
//                $forLogs .= "barcode4 ".$barcode4."\n";
//
//                if(isset($prod['warehouse']['whse']))
//                {
//
//                    foreach ($prod['warehouse']['whse'] as $qt)
//                    {
//                        if(is_array($qt))
//                        {
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($qt['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($qt['qty_available']);
//                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
//                            try {
//                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                                //return true;
//                            } catch (\Exception $e) {
//                                echo "error source ". $e->getMessage();
//                            }
//                        }
//                        else
//                        {
//                            // to handle single warehouse
//                            $sourceItem = $this->sourceItemFactory->create();
//                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
//                            $sourceItem->setSku($prod['code']);
//                            $sourceItem->setStatus(1);
//                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
//                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
//                            try {
//                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
//                                //return true;
//                            } catch (\Exception $e) {
//                                echo "error source ". $e->getMessage();
//                            }
//                        }
//
//                    }
//                }
//
////                $sourceItem = $this->sourceItemFactory->create();
////                $sourceItem->setSourceCode('default');
////                $sourceItem->setSku($prod['code']);
////                $sourceItem->setStatus(1);
////                $sourceItem->setQuantity(0);
////                $forLogs .="default - 0 \n";
////                $this->sourceItemsSaveInterface->execute([$sourceItem]);
//
//                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
//                $product->setCustomAttribute('stock_group', $prod['stock-group']);
//                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
//                if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
//                {
//                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
//                }
//                else {
//                    $product->setCustomAttribute('item_condition', " ");
//                }
//                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
//                {
//                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
//                }
//                else {
//                    $product->setCustomAttribute('item_rating', " ");
//                }
//
//                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
//                {
//                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
//                }
//                else {
//                    $product->setCustomAttribute('d2desc', " ");
//                }
//
//                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
//                {
//                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
//                }
//                else {
//                    $product->setCustomAttribute('d2newsku', " ");
//                }
//
//                if(isset($prod['stk-storage-type-flag']))
//                {
//                    if($prod['stk-storage-type-flag'] == 'H')
//                    {
//                        $product->setCustomAttribute('dangerous_goods', '1');
//                    }
//                    else
//                    {
//                        $product->setCustomAttribute('dangerous_goods', '0');
//                    }
//
//                    if($prod['stk-storage-type-flag'] == 'B')
//                    {
//                        $product->setCustomAttribute('bulky_item', 1);
//                    }
//                    else
//                    {
//                        $product->setCustomAttribute('bulky_item', 0);
//                    }
//
//                }
//                else
//                {
//                    $product->setCustomAttribute('dangerous_goods', '0');
//                    $product->setCustomAttribute('bulky_item', 0);
//                }
//
//                $product->setCustomAttribute('marketplacer_seller', 20329);
//
//                $today = date('Y-m-d');
//                $product->setCustomAttribute('date_update', $today);
//                $product->setCustomAttribute('is_nda', 1);
//
//                if(isset($prod['stock-division']))
//                {
//                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
//                }
//
//                if(isset($prod['stock-department']))
//                {
//                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
//                }
//
//                if(isset($prod['stock-category']))
//                {
//                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
//                }
//
//                if(isset($prod['stock-class']))
//                {
//                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
//                }
//
//                $this->productRepository->save($product);
//
//                $parent = "";
//                $subcat1 = "";
//                $subcat2 = "";
//                $subcat3 = "";
//
//                //set categories
//                $categoryIds = array();
//                $catList = "";
//                $productCategoryIds = $product->getCategoryIds();
//                $shouldupdate = false;
//
//                if (count($getCategoryList))
//                {
//                    foreach ($getCategoryList as $id => $category)
//                    {
//                        //digiSeconds
//                        if($prod['stk-brand-desc'] == 'digiSeconds' && $category['name'] == 'digiSeconds')
//                        {
//                            $catList .= $category['name'] . " - " .$category['id']." : ";
//                            $categoryIds[] = $category['id'];
//                        }
//
//                        //digiSeconds
//                        if(isset($prod['d2lvl1']))
//                        {
//                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
//                            {
//                                $catList .= $category['name'] . " - " .$category['id']." : ";
//                                $categoryIds[] = $category['id'];
//                            }
//
//                            //digiSeconds
//                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
//                            {
//                                $catList .= $category['name'] . " - " .$category['id']." : ";
//                                $categoryIds[] = $category['id'];
//                            }
//
//                            //digiSeconds
//                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
//                            {
//                                $catList .= $category['name'] . " - " .$category['id']." : ";
//                                $categoryIds[] = $category['id'];
//                            }
//                        }
//
//                        if($prod['web-category1'] == 'Cameras')
//                        {
//                            $prod['web-category1'] = 'Digital Cameras';
//                        }
//
//                        if($category['name'] == $prod['web-category1'])
//                        {
//                            if($parent == "")
//                            {
//                                if($category['parent_id'] == '2')
//                                {
//                                    $parent = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//
//                            }
//
//                        }
//                        if(isset($prod['web-category2']))
//                        {
//                            if($prod['web-category2'] == 'Gaming')
//                            {
//                                $prod['web-category2'] = 'Gaming Products';
//                            }
//
//                            if($category['name'] == $prod['web-category2'])
//                            {
//                                if($category['parent_id'] == $parent)
//                                {
//                                    $subcat1 = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
//                            {
//                                if($category['parent_id'] == $parent)
//                                {
//                                    $subcat1 = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                        }
//                        if(isset($prod['web-category3']))
//                        {
//
//                            if($prod['web-category3'] == 'Fujifilm Instant Cameras')
//                            {
//                                $prod['web-category3'] = 'Fujifilm Instant Instax Cameras';
//                            }
//
//                            if($prod['web-category3'] == 'Cables & Adaptors')
//                            {
//                                $prod['web-category3'] = 'Computer Cables & Adaptors';
//                            }
//
//                            if($prod['web-category3'] == 'Cases Covers & Bags')
//                            {
//                                $prod['web-category3'] = 'Laptop Cases, Covers & Bags';
//                            }
//
//                            if($prod['web-category3'] == 'Chargers')
//                            {
//                                $prod['web-category3'] = 'Laptop Chargers';
//                            }
//
//                            if($prod['web-category3'] == 'Hubs & Docks')
//                            {
//                                $prod['web-category3'] = 'Computer Hubs & Docks';
//                            }
//
//                            if($prod['web-category3'] == 'Webcams')
//                            {
//                                $prod['web-category3'] = 'Computer Webcams';
//                            }
//
//                            if($prod['web-category3'] == 'Console Accessories')
//                            {
//                                $prod['web-category3'] = 'Console Gaming Accessories';
//                            }
//
//                            if($prod['web-category3'] == 'Consoles')
//                            {
//                                $prod['web-category3'] = 'Gaming Consoles';
//                            }
//
//                            if($prod['web-category3'] == 'Business')
//                            {
//                                $prod['web-category3'] = 'Business Laptops';
//                            }
//
//                            if($prod['web-category3'] == 'Home & Student')
//                            {
//                                $prod['web-category3'] = 'Home & Student Laptops';
//                            }
//
//                            if($prod['web-category3'] == 'Monitor Accessories')
//                            {
//                                $prod['web-category3'] = 'Computer Monitor Accessories';
//                            }
//
//                            if($prod['web-category3'] == 'Monitor Mounts & Stands')
//                            {
//                                $prod['web-category3'] = 'Monitor Arms, Mounts & Stands';
//                            }
//
//                            if($prod['web-category3'] == 'Monitors')
//                            {
//                                $prod['web-category3'] = 'Computer Monitors';
//                            }
//
//                            if($prod['web-category3'] == 'Ink')
//                            {
//                                $prod['web-category3'] = 'Printer Ink';
//                            }
//
//                            if($prod['web-category3'] == 'Paper')
//                            {
//                                $prod['web-category3'] = 'Photo Printing Papers';
//                            }
//
//                            if($prod['web-category3'] == 'Shredders')
//                            {
//                                $prod['web-category3'] = 'Paper Shredders';
//                            }
//
//                            if($prod['web-category3'] == 'Light Meters')
//                            {
//                                $prod['web-category3'] = 'Light Meters for Cameras';
//                            }
//
//                            if($category['name'] == $prod['web-category3'])
//                            {
//                                if($category['parent_id'] == $subcat1)
//                                {
//                                    $subcat2 = $category['id'];
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                        }
//                        if(isset($prod['web-category4']))
//                        {
//
//                            if($category['name'] == $prod['web-category4'])
//                            {
//                                if($category['parent_id'] == $subcat2)
//                                {
//                                    //echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
//                                    $catList .=$category['name'] . " - " .$category['id']." : ";
//                                    $categoryIds[] = $category['id'];
//                                }
//                            }
//                        }
//                    }
//                }
//                //echo $catList."<br>";
//                $forLogs .= $catList."\n";
//                //comment out for now until bugged category is fixed May 6, 2024
//                if (count($categoryIds)) {
//
//                    $forLogs .= "Categories: ".$catList."\n";
//                    //echo "update categories: ".$catList."<br />";
//                    try
//                    {
//                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
//                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
//                        try
//                        {
//                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
//                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
//                            $forLogs .=   $e->getMessage();
//                        }
//                    }
//                }

            }

            $this->logger->info($forLogs);
        }

        if(isset($json['response']['status']) && $json['response']['status'] == 'FAIL')
        {
            $this->logger->info($json['response']['message']);;
        }

        //$this->logger->info($forLogs);

        if($startItem == $lastCode)
        {
            return true;
        }

        $this->productSyncContinue($lastCode);
    }

    public function productProntoBulk($startItem, $endItem)
    {

        set_time_limit(1500);
        $lastCode = 0;

        $this->attributeOptions = $this->getOptionHash('brand');
        echo "start <br/>";
        $parentID = 2; // default category
        $getCategoryList = $this->getSubCategoryByParentID($parentID);

        $this->logger->info('Pronto Product Sync - start item: '.$startItem);
        //echo 'Pronto Product Sync - start item: '.$startItem."<br/>";
        //$url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem.'&end-item='.$endItem;
        //live port :8084
        $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem.'&end-item='.$endItem;

        if($endItem == '0')
        {
            $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem;
        }

        $username = 'clint.mercado';
        $password = '849cd5080faff5ce';
        $jsonData = '{}';

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Accept", "application/json");
        $this->curl->addHeader("compcode", "DIG"); //live
        $this->curl->addHeader("user", "ewaveapi");
        $this->curl->addHeader("token", "904241bdbf10efa9");

        //$this->curl->addHeader("compcode", "UA1"); //test
        //$this->curl->addHeader("user", "clint.mercado");
        //$this->curl->addHeader("token", "849cd5080faff5ce");
        // get method
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
        $this->curl->get($url);

        $result = $this->curl->getBody();

        $json = $this->jsonSerializer->unserialize($result);

        //var_dump($json);
        $count = 0;
        foreach ($json['stockmaster']['stockcode'] as $prod)
        {
            $forLogs = "";

            if(!isset($prod['code']))
            {
                exit;
            }

            $lastCode = $prod['code'];

            try
            {
                //product update
                $forLogs .= "SKU ".$prod['code']."\n";
                echo "SKU ".$prod['code']."\n";
                $product = $this->productRepository->get($prod['code']);
                //set name, price, stock status
                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
                $product->setMetaTitle($prodname);
//                $product->setName($prodname);
                $price = 0;
                $product->setStockStatus($prod['stk-stock-status']);
                $tax = 10;
                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
                {
                    $product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
                }
                else
                {
                    $product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region']['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
                }


                $pricetocost = floatval($price);
                $tax = floatval($tax);
                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                if(isset($prod['stk-replacement-cost']))
                {

                    $cost = $prod['stk-replacement-cost'];
                    if($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
                    {
                        if(isset($prod['stk-current-buy']))
                        {
                            $cost = $prod['stk-current-buy'];
                            if ($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
                            {
                                if(isset($prod['whse-avg-cost-swhs']))
                                {
                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price

                                    if ($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
                                    {
                                        $pricetocost = floatval($price);
                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                                    }
                                }

                            }
                        }
                    }
                }

                echo "cost price ".$cost."<br/>";
                $product->setCustomAttribute('cost', $cost);

                $marketplacesprice = 0;
                if(isset($prod['pricing']['price-region'][0]['prc-break-price-4-inc']))
                {
                    $marketplacesprice = $prod['pricing']['price-region'][0]['prc-break-price-4-inc'];
                    if(empty($marketplacesprice))
                    {
                        $marketplacesprice = 0;
                    }
                }
                else
                {
                    if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
                    {
                        $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
                        if(empty($marketplacesprice))
                        {
                            $marketplacesprice = 0;
                        }
                    }
                }
                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);
                echo $marketplacesprice. " marketplacesprice <br/>";
                $endis = "nochange";
                echo $prod['stk-user-only-alpha4-1']." <br>";
                echo "Stock Condition " .$prod['stk-condition-code']." <br>";
                if($prod['stk-condition-code'] == 'O')
                {
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    $endis = 'disabled';
                }
                else
                {
                    //web flag
                    //if blank, set to disable
                    if($prod['stk-user-only-alpha4-1'] == '')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                        $endis = 'disabled';
                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'W')
                    {
                        $isNda = $product->getIsNda();
                        if($isNda)
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                            $endis = 'disabled';
                        }
                        else
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $endis = 'enabled';
                        }

                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'N')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    else {
                        //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                        $endis = 'as is';
                    }

                }

                echo $endis." <br/>";

//                if($prod['stk-user-only-alpha4-1'] == 'A')
//                {
//                    //$product->setData('awaiting_product', '1');
//                    $product->setCustomAttribute('awaiting_product', '1');
//                    echo "awaiting 1  <br/>";
//                }
//                else {
//                    //$product->setData('awaiting_product', '0');
//                    $product->setCustomAttribute('awaiting_product', '0');
//                    echo "awaiting 0  <br/>";
//                }

                //stk-user-only-alpha4-3 is_qantas_product
                if(isset($prod['stk-user-only-alpha4-3']))
                {
                    if($prod['stk-user-only-alpha4-3'] == "Q")
                    {
                        $product->setCustomAttribute('is_qantas_product', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('is_qantas_product', '0');
                    }
                }

                if(isset($prod['stock-division']))
                {
                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
                }

                if(isset($prod['stock-department']))
                {
                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
                }

                if(isset($prod['stock-category']))
                {
                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
                }

                if(isset($prod['stock-class']))
                {
                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
                }

                if($prod['stk-user-only-alpha4-1'] == 'P')
                {
                    //$product->setData('awaiting_product', '1');
                    //$product->setCustomAttribute('pre_order', '1');
                    //$product->setCustomAttribute('preorder', '1');
                    $product->setCustomAttribute('pre_order_status', '1');
                    echo "pre_order 1  <br/>";
                }
                //set brand
                //digiSeconds brand
                echo $prod['stk-brand-desc'] ."<br/>";
                if($prod['stk-brand-desc'] == 'digiSeconds')
                {
                    if(isset($prod['d2brand']))
                    {
                        $brandName = strtolower($prod['d2brand']);
                    }
                    else
                    {
                        $brandName = strtolower($prod['stk-brand']);
                    }
                }
                else
                {
                    $brandName = strtolower($prod['stk-brand']);
                }
                $forLogs .= $brandName."\n";
                if($brandName == "thinktank")
                {
                    $brandName = "think tank";
                }
                if($brandName == "peak")
                {
                    $brandName = "peak design";
                }
                if($brandName == "3lt")
                {
                    $brandName = "3 legged thing";
                }
                if(isset($this->attributeOptions[strtolower($brandName)]))
                {
                    $brandCode = $this->attributeOptions[strtolower($brandName)];
                    $product->setBrand($brandCode);
                }

                if(isset($prod['warehouse']['whse']))
                {
                    foreach ($prod['warehouse']['whse'] as $qt)
                    {
                        if(is_array($qt))
                        {
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($qt['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($qt['qty_available']);
                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }
                        else
                        {
                            // to handle single warehouse
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }


                    }
                }

//                $sourceItem = $this->sourceItemFactory->create();
//                $sourceItem->setSourceCode('default');
//                $sourceItem->setSku($prod['code']);
//                $sourceItem->setStatus(1);
//                $sourceItem->setQuantity(0);
//                $forLogs .="default - 0 \n";
//                $this->sourceItemsSaveInterface->execute([$sourceItem]);

                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
                $product->setCustomAttribute('qff_base', $prod['qff-base-points-per-dollar']);
                $product->setCustomAttribute('qff_bonus_points', $prod['qff-bonus-points-per-dollar']);

                if(isset($prod['qff-store-product-name']))
                {
                    echo $prod['qff-store-product-name'] . " qff-store-product-name<br/>";

                    $product->setCustomAttribute('qff_store_product_name', $prod['qff-store-product-name']);
                }
                if(isset($prod['qff-store-price']))
                {
                    $product->setCustomAttribute('qff_store_price', $prod['qff-store-price']);
                    echo "set qff-store-price <br/>";
                    echo $prod['qff-store-price'] . "<br/>";
                }

                if($prod['stk-condition-code'] == 'T')
                {
                    $stock_condition = 181;
                }
                else if ($prod['stk-condition-code'] == 'O')
                {
                    $stock_condition = 179;
                }
                else
                {
                    $stock_condition = 183;
                }
                $product->setCustomAttribute('stock_condition', $stock_condition);
                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['stk-sort-analysis-code']);
                }
                else if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
                }
                else {
                    $product->setCustomAttribute('item_condition', " ");
                }
                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
                {
                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
                }
                else {
                    $product->setCustomAttribute('item_rating', " ");
                }

                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
                {
                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
                }
                else {
                    $product->setCustomAttribute('d2desc', " ");
                }

                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
                {
                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
                }
                else {
                    $product->setCustomAttribute('d2newsku', " ");
                }


                if(isset($prod['stk-storage-type-flag']))
                {
                    if($prod['stk-storage-type-flag'] == 'H')
                    {
                        $product->setCustomAttribute('dangerous_goods', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('dangerous_goods', '0');
                    }

                    if($prod['stk-storage-type-flag'] == 'B')
                    {
                        $product->setCustomAttribute('bulky_item', 1);
                    }
                    else
                    {
                        $product->setCustomAttribute('bulky_item', 0);
                    }

                }
                else
                {
                    $product->setCustomAttribute('dangerous_goods', '0');
                    $product->setCustomAttribute('bulky_item', 0);
                }

                $product->setCustomAttribute('marketplacer_seller', 20329);

                $productSales = $this->getProductSales($product->getId(), $price);
                $product->setCustomAttribute('nb_sales', $productSales); //bestseller attribute for sorting

                $today = date('Y-m-d');
                $product->setCustomAttribute('date_update', $today);
                echo $today . "<br>";
                $this->productRepository->save($product);
                //echo "update ".$lastCode ."<br/>";

                $parent = "";
                $subcat1 = "";
                $subcat2 = "";
                $subcat3 = "";

                //set categories
                $categoryIds = array();
                $catList = "";
                $productCategoryIds = $product->getCategoryIds();
                $shouldupdate = false;


                if (count($getCategoryList))
                {
                    foreach ($getCategoryList as $id => $category)
                    {
                        //digiSeconds
                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }
                        else if($prod['stk-brand-desc'] == 'digiSeconds' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }

                        //digiSeconds
                        if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                        {
                            if($prod['stk-sort-analysis-code'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['stk-sort-analysis-code'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                        }
                        else if(isset($prod['d2lvl1']))
                        {
                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }



                        if($category['name'] == $prod['web-category1'])
                        {
                            if($parent == "")
                            {
                                if($category['parent_id'] == '2')
                                {
                                    $parent = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
                                    $categoryIds[] = $category['id'];
                                }

                            }

                        }
                        if(isset($prod['web-category2']))
                        {
                            if($category['name'] == $prod['web-category2'])
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category3']))
                        {
                            if($category['name'] == $prod['web-category3'])
                            {
                                if($category['parent_id'] == $subcat1)
                                {
                                    $subcat2 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category4']))
                        {

                            if($category['name'] == $prod['web-category4'])
                            {
                                if($category['parent_id'] == $subcat2)
                                {
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                    }
                }
                //echo $catList."<br>";
                $forLogs .= $catList."\n";
                //comment out for now until bugged category is fixed May 6, 2024
                if (count($categoryIds)) {

                    $forLogs .= "Categories: ".$catList."\n";
                    //echo "update categories: ".$catList."<br />";
                    try
                    {
                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        try
                        {
                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            echo $e->getMessage();
                        }
                    }
                }


            } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                //insert new product
                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
                $forLogs .= "Product Name: ".$prodname."\n";
                $forLogs .= "SKU: ".$prod['code']."\n";
                $product = $this->productFactory->create();
                $product->setSku($prod['code']);
                $product->setName($prodname);
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $product->setVisibility(4);
                $product->setAttributeSetId(4);
                $product->setMetaTitle($prodname);

                $tax = 10;
                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
                {
                    $product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
                }
                else
                {
                    $product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region']['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
                }


                $pricetocost = floatval($price);
                $tax = floatval($tax);
                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                if(isset($prod['stk-replacement-cost']))
                {

                    $cost = $prod['stk-replacement-cost'];
                    if($cost == '0' || $cost == '')
                    {
                        if(isset($prod['stk-current-buy']))
                        {
                            $cost = $prod['stk-current-buy'];
                            if ($cost == '0' || $cost == '')
                            {
                                if(isset($prod['whse-avg-cost-swhs']))
                                {
                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price

                                    if ($cost == '0' || $cost == '')
                                    {
                                        $pricetocost = floatval($price);
                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                                    }
                                }


                            }
                        }
                    }
                }

                $product->setCustomAttribute('cost', $cost);

                $marketplacesprice = 0;
                if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
                {
                    $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
                    if(empty($marketplacesprice))
                    {
                        $marketplacesprice = 0;
                    }
                }
                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);

                //set brand
                //digiSeconds brand
                if($prod['stk-brand-desc'] == 'digiSeconds')
                {
                    if(isset($prod['d2brand']))
                    {
                        $brandName = strtolower($prod['d2brand']);
                    }
                    else
                    {
                        $brandName = strtolower($prod['stk-brand']);
                    }
                }
                else
                {
                    $brandName = strtolower($prod['stk-brand']);
                }
                $forLogs .= "Brand: ".$brandName."\n";
                if(isset($this->attributeOptions[strtolower($brandName)]))
                {
                    $brandCode = $this->attributeOptions[strtolower($brandName)];
                    $product->setBrand($brandCode);
                }

                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
//                // If desired, you can set a tax class like so:
//                //$product->setCustomAttribute('tax_class_id', $taxClassId);
                $toUrl = $prodname;
                $toUrl = preg_replace('/[+]/', 'plus', $toUrl);
                $urltext = preg_replace('#[^0-9a-z]+#i', '-', $toUrl);
                $urltext = strtolower($urltext);
                $product->setUrlKey($urltext);

                // set gtin and apn
                $barcode1 = "";
                $barcode2 = "";
                $barcode3 = "";
                $barcode4 = "";
                if(isset($prod['gtins']['gtin'])) {
                    //set barcode
                    if (count($prod['gtins']['gtin']) == count($prod['gtins']['gtin'], COUNT_RECURSIVE)) {
                        $barcode1 = $prod['gtins']['gtin']['id'];

                    } else {
                        $x = 1;
                        foreach ($prod['gtins']['gtin'] as $gtin)
                        {
                            switch ($x)
                            {
                                case 1:
                                    $barcode1 = $gtin['id'];
                                    break;
                                case 2:
                                    $barcode4 = $gtin['id'];
                                    break;
                                case 3:
                                    $barcode3 = $gtin['id'];
                                    break;
                                case 4:
                                    $barcode2 = $gtin['id'];
                                    break;
                                default:

                            }
                            $x++;
                        }
                    }
                }
                //work around to set
                $product->setCustomAttribute('barcode1',$barcode1);
                $product->setCustomAttribute('barcode2',$barcode2);
                $product->setCustomAttribute('barcode3',$barcode3);
                $product->setCustomAttribute('barcode4',$barcode4);
                $forLogs .= "barcode1 ".$barcode1."\n";
                $forLogs .= "barcode2 ".$barcode2."\n";
                $forLogs .= "barcode3 ".$barcode3."\n";
                $forLogs .= "barcode4 ".$barcode4."\n";

                if(isset($prod['warehouse']['whse']))
                {

                    foreach ($prod['warehouse']['whse'] as $qt)
                    {
                        if(is_array($qt))
                        {
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($qt['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($qt['qty_available']);
                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }
                        else
                        {
                            // to handle single warehouse
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }

                    }
                }


                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
                $product->setCustomAttribute('qff_base', $prod['qff-base-points-per-dollar']);
                $product->setCustomAttribute('qff_bonus_points', $prod['qff-bonus-points-per-dollar']);
                if(isset($prod['qff-store-product-name']))
                {
                    $product->setCustomAttribute('qff_store_product_name', $prod['qff-store-product-name']);
                }
                if(isset($prod['qff-store-price']))
                {
                    $product->setCustomAttribute('qff_store_price', $prod['qff-store-price']);
                }

                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['stk-sort-analysis-code']);
                }
                else if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
                }
                else {
                    $product->setCustomAttribute('item_condition', " ");
                }
                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
                {
                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
                }
                else {
                    $product->setCustomAttribute('item_rating', " ");
                }

                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
                {
                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
                }
                else {
                    $product->setCustomAttribute('d2desc', " ");
                }

                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
                {
                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
                }
                else {
                    $product->setCustomAttribute('d2newsku', " ");
                }

                if(isset($prod['stk-storage-type-flag']))
                {
                    if($prod['stk-storage-type-flag'] == 'H')
                    {
                        $product->setCustomAttribute('dangerous_goods', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('dangerous_goods', '0');
                    }

                    if($prod['stk-storage-type-flag'] == 'B')
                    {
                        $product->setCustomAttribute('bulky_item', 1);
                    }
                    else
                    {
                        $product->setCustomAttribute('bulky_item', 0);
                    }

                }
                else
                {
                    $product->setCustomAttribute('dangerous_goods', '0');
                    $product->setCustomAttribute('bulky_item', 0);
                }

                $product->setCustomAttribute('marketplacer_seller', 20329);

                $today = date('Y-m-d');
                $product->setCustomAttribute('date_update', $today);
                $product->setCustomAttribute('is_nda', 1);

                if(isset($prod['stock-division']))
                {
                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
                }

                if(isset($prod['stock-department']))
                {
                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
                }

                if(isset($prod['stock-category']))
                {
                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
                }

                if(isset($prod['stock-class']))
                {
                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
                }

                $this->productRepository->save($product);

                $parent = "";
                $subcat1 = "";
                $subcat2 = "";
                $subcat3 = "";

                //set categories
                $categoryIds = array();
                $catList = "";
                $productCategoryIds = $product->getCategoryIds();
                $shouldupdate = false;


                if (count($getCategoryList))
                {
                    foreach ($getCategoryList as $id => $category)
                    {
                        //digiSeconds
                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }
                        else if($prod['stk-brand-desc'] == 'digiSeconds' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }

                        //digiSeconds
                        if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                        {
                            if($prod['stk-sort-analysis-code'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['stk-sort-analysis-code'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                        }
                        else if(isset($prod['d2lvl1']))
                        {
                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }



                        if($category['name'] == $prod['web-category1'])
                        {
                            if($parent == "")
                            {
                                if($category['parent_id'] == '2')
                                {
                                    $parent = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
                                    $categoryIds[] = $category['id'];
                                }

                            }

                        }
                        if(isset($prod['web-category2']))
                        {
                            if($category['name'] == $prod['web-category2'])
                            {
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category3']))
                        {
                            if($category['name'] == $prod['web-category3'])
                            {
                                if($category['parent_id'] == $subcat1)
                                {
                                    $subcat2 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category4']))
                        {

                            if($category['name'] == $prod['web-category4'])
                            {
                                if($category['parent_id'] == $subcat2)
                                {
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                    }
                }
                //echo $catList."<br>";
                $forLogs .= $catList."\n";
                //comment out for now until bugged category is fixed May 6, 2024
                if (count($categoryIds)) {

                    $forLogs .= "Categories: ".$catList."\n";
                    try
                    {
                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        try
                        {
                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $forLogs .=   $e->getMessage();
                        }
                    }
                }

            }

            $this->logger->info($forLogs);
        }

        return true;
    }


    public function productProntoSingle($startItem)
    {
        set_time_limit(300);

        //get brands to compare later
        $this->attributeOptions = $this->getOptionHash('brand');
        $lastCode = 0;
        $forLogs = "";
        echo 'Manual Pronto Product Sync - start item: '.$startItem."<br/>";

        //get all categories
        $parentID = 2;
        $getCategoryList = $this->getSubCategoryByParentID($parentID);

        //var_dump($getCategoryList);
        $this->logger->info('Pronto Product Sync - start item: '.$startItem);

        //connect to Pronto stock-master
        // call-type=full_enquiry
        //$url = 'https://digi-pronto.abtonline.com.au:8083/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem;//.$startitem; //test
        //live port :8084
        $url = 'https://digi-pronto.abtonline.com.au:8084/rest/abtws/stock-master?call-type=full_enquiry&start-item='.$startItem.'&end-item='.$startItem;
        $username = 'clint.mercado';
        $password = '849cd5080faff5ce';
        $jsonData = '{}';

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Accept", "application/json");
        $this->curl->addHeader("compcode", "DIG"); //live
        $this->curl->addHeader("user", "ewaveapi");
        $this->curl->addHeader("token", "904241bdbf10efa9");

//        $this->curl->addHeader("compcode", "UA1"); //test
//        $this->curl->addHeader("user", "clint.mercado");
//        $this->curl->addHeader("token", "849cd5080faff5ce");
        // get method
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST,false);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER,false);
        $this->curl->get($url);

        $result = $this->curl->getBody();
        // echo $result;
        $json = $this->jsonSerializer->unserialize($result);
        //var_dump($json['stockmaster']['stockcode']);
        //var_dump($json);
        var_dump($getCategoryList);

        foreach ($json['stockmaster'] as $prod)
        {
            //if blank exit
            if(!isset($prod['code']))
            {
                exit;
            }

            $lastCode = $prod['code'];

            try
            {
                //product update
                $forLogs .= "SKU ".$prod['code']."\n";
                echo "SKU ".$prod['code']."\n";
                $product = $this->productRepository->get($prod['code']); // code is SKU

                //set name, price, stock status
                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
                $product->setMetaTitle($prodname);
                $product->setStockStatus($prod['stk-stock-status']);
//                $product->setName($prodname);
                $tax = 10;
                //to set pricing
                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
                {
                    $product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
                }
                else
                {
                    $product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region']['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
                }

                //to populate custom attributes "cost" for wiserdata etc
                $pricetocost = floatval($price);
                $tax = floatval($tax);
                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                if(isset($prod['stk-replacement-cost']))
                {

                    $cost = $prod['stk-replacement-cost'];
                    if($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
                    {
                        if(isset($prod['stk-current-buy']))
                        {
                            $cost = $prod['stk-current-buy'];
                            if ($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
                            {
                                if(isset($prod['whse-avg-cost-swhs']))
                                {
                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price

                                    if ($cost == '0' || $cost == '0.00' || $cost == 0 || $cost == '')
                                    {
                                        $pricetocost = floatval($price);
                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                                    }
                                }

                            }
                        }
                    }
                }

                echo "cost price ".$cost."<br/>";
                if($cost > $price)
                {
                    $cost = $price;
                }
                $product->setCustomAttribute('cost', $cost);

                //to set marketplaces prices
                $marketplacesprice = 0;
                if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
                {
                    $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];

                    if(empty($marketplacesprice))
                    {
                        $marketplacesprice = 0;
                    }
                }
                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);

                //if true, do not enable
                $isNda = $product->getIsNda();
                if($isNda)
                {
                    echo "is nda";
                    echo "<br />";
                }
                else
                {
                    echo "not nda";
                    echo "<br />";
                }

                $endis = "nochange";
                echo $prod['stk-user-only-alpha4-1']." <br>";
                echo "Stock Condition " .$prod['stk-condition-code']." <br>";
                if($prod['stk-condition-code'] == 'O')
                {
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    $endis = 'disabled';
                }
                else
                {
                    //web flag
                    //if blank, set to disable
                    if($prod['stk-user-only-alpha4-1'] == '')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                        $endis = 'disabled';
                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'W') // W = web enabled
                    {
                        $isNda = $product->getIsNda();
                        if($isNda)
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                            $endis = 'disabled';
                        }
                        else
                        {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                            $endis = 'enabled';
                        }

                    }
                    else if($prod['stk-user-only-alpha4-1'] == 'N')
                    {
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                    }
                    else {
                        //$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                        $endis = 'as is';
                    }

                }

                echo $endis." <br/>";

//                if($prod['stk-user-only-alpha4-1'] == 'A')
//                {
//                    //$product->setData('awaiting_product', '1');
//                    $product->setCustomAttribute('awaiting_product', '1');
//                    echo "awaiting 1  <br/>";
//                }
//                else {
//                    //$product->setData('awaiting_product', '0');
//                    $product->setCustomAttribute('awaiting_product', '0');
//                    echo "awaiting 0  <br/>";
//                }

                //stk-user-only-alpha4-3 is_qantas_product
                if(isset($prod['stk-user-only-alpha4-3']))
                {
                    if($prod['stk-user-only-alpha4-3'] == "Q")
                    {
                        $product->setCustomAttribute('is_qantas_product', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('is_qantas_product', '0');
                    }
                }

                if(isset($prod['stock-division']))
                {
                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
                }

                if(isset($prod['stock-department']))
                {
                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
                }

                if(isset($prod['stock-category']))
                {
                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
                }

                if(isset($prod['stock-class']))
                {
                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
                }

                if($prod['stk-user-only-alpha4-1'] == 'P')
                {
                    //$product->setData('awaiting_product', '1');
                    //$product->setCustomAttribute('pre_order', '1');
                    //$product->setCustomAttribute('preorder', '1');
                    $product->setCustomAttribute('pre_order_status', '1');
                    echo "pre_order 1  <br/>";
                }
                //set brand
                //digiSeconds brand
                echo $prod['stk-brand-desc'] ."<br/>";
                if($prod['stk-brand-desc'] == 'digiSeconds')
                {
                    if(isset($prod['d2brand']))
                    {
                        $brandName = strtolower($prod['d2brand']);
                    }
                    else
                    {
                        $brandName = strtolower($prod['stk-brand']);
                    }
                }
                else
                {
                    $brandName = strtolower($prod['stk-brand']);
                }
                $forLogs .= $brandName."\n";
                if($brandName == "thinktank")
                {
                    $brandName = "think tank";
                }
                if($brandName == "peak")
                {
                    $brandName = "peak design";
                }
                if($brandName == "3lt")
                {
                    $brandName = "3 legged thing";
                }
                if(isset($this->attributeOptions[strtolower($brandName)]))
                {
                    $brandCode = $this->attributeOptions[strtolower($brandName)]; //get brand code by brandname
                    $product->setBrand($brandCode); //update via brand code
                }

                $sourceItems = [];
                if(isset($prod['warehouse']['whse']))
                {
                    foreach ($prod['warehouse']['whse'] as $qt)
                    {
                        if(is_array($qt))
                        {
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($qt['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($qt['qty_available']);
                            $sourceItems[] = $sourceItem;
                            //$forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
                            echo $qt['code']." - ".$qt['qty_available'];
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }
                        else
                        {
                            // to handle single warehouse
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
                            $sourceItems[] = $sourceItem;
//                            //$forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
                            echo $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available'] ." / ";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }
                    }
                }
                //default source, dapat lagi meron
                $sourceItem = $this->sourceItemFactory->create();
                $sourceItem->setSourceCode('default');
                $sourceItem->setSku($prod['code']);
                $sourceItem->setStatus(1);//in stock
                $sourceItem->setQuantity(0);
                $sourceItems[] = $sourceItem;
                echo "default - 0";
                try {
                    $this->sourceItemsSaveInterface->execute($sourceItems);
                    //return true;
                } catch (\Exception $e) {
                    echo " - error source " .$e->getMessage(); ;
                }

                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
                $product->setCustomAttribute('qff_base', $prod['qff-base-points-per-dollar']);
                $product->setCustomAttribute('qff_bonus_points', $prod['qff-bonus-points-per-dollar']);

                if(isset($prod['qff-store-product-name']))
                {
                    $product->setCustomAttribute('qff_store_product_name', $prod['qff-store-product-name']);
                    //echo $prod['qff-store-product-name'] ."<br/>";

                }
                if(isset($prod['qff-store-price']))
                {
                    $product->setCustomAttribute('qff_store_price', $prod['qff-store-price']);
                    echo "set qff-store-price <br/>";
                    //echo $prod['qff-store-price'] ."<br/>";
                }

                //check code meaing
                if($prod['stk-condition-code'] == 'T')
                {
                    $stock_condition = 181;
                }
                else if ($prod['stk-condition-code'] == 'O')
                {
                    $stock_condition = 179;
                }
                else
                {
                    $stock_condition = 183;
                }
                $product->setCustomAttribute('stock_condition', $stock_condition);
                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['stk-sort-analysis-code']);
                }
                else if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
                }
                else
                {
                    $product->setCustomAttribute('item_condition', " ");
                }
                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
                {
                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
                }
                else {
                    $product->setCustomAttribute('item_rating', " ");
                }

                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
                {
                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
                }
                else {
                    $product->setCustomAttribute('d2desc', " ");
                }

                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
                {
                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
                }
                else {
                    $product->setCustomAttribute('d2newsku', " ");
                }


                if(isset($prod['stk-storage-type-flag']))
                {
                    if($prod['stk-storage-type-flag'] == 'H')
                    {
                        $product->setCustomAttribute('dangerous_goods', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('dangerous_goods', '0');
                    }

                    if($prod['stk-storage-type-flag'] == 'B')
                    {
                        $product->setCustomAttribute('bulky_item', 1);
                    }
                    else
                    {
                        $product->setCustomAttribute('bulky_item', 0);
                    }

                }
                else
                {
                    $product->setCustomAttribute('dangerous_goods', '0');
                    $product->setCustomAttribute('bulky_item', 0);
                }

                $product->setCustomAttribute('marketplacer_seller', 20329); //digidirect seller code

                $productSales = $this->getProductSales($product->getId(), $price);
                $product->setCustomAttribute('nb_sales', $productSales); //bestseller attribute for sorting

                $today = date('Y-m-d');
                $product->setCustomAttribute('date_update', $today);
                echo $today . "<br>";
                $this->productRepository->save($product);
                //echo "update ".$lastCode ."<br/>";


                $parent = "";
                $subcat1 = "";
                $subcat2 = "";
                $subcat3 = "";

                //set categories
                $categoryIds = array();
                $catList = "";
                $productCategoryIds = $product->getCategoryIds();
                $shouldupdate = false;

                var_dump($getCategoryList);
                if (count($getCategoryList))
                {
                    foreach ($getCategoryList as $id => $category)
                    {
                        //digiSeconds
                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }
                        else if($prod['stk-brand-desc'] == 'digiSeconds' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }

                        //digiSeconds
                        if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                        {
                            if($prod['stk-sort-analysis-code'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['stk-sort-analysis-code'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }
                        else if(isset($prod['d2lvl1']))
                        {
                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['d2lvl1'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }

                        if($prod['web-category1'] == 'Cameras')
                        {
                            $prod['web-category1'] = 'Digital Cameras';
                        }

                        //actual category
<<<<<<< HEAD

=======
>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                        if($category['name'] == $prod['web-category1'])
                        {
                            echo "web-category1 : ".$prod['web-category1']." <br> ";
                            if($parent == "")
                            {
                                if($category['parent_id'] == '2')
                                {
                                    $parent = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." <br> ";
                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
                                    $categoryIds[] = $category['id'];
                                }

                            }

                        }
                        if(isset($prod['web-category2']))
                        {
<<<<<<< HEAD
                            echo "web-category2 : ".$prod['web-category2']." - ".$category['name']." <br> ";
                            if($category['name'] == $prod['web-category2'])
                            {
                                //echo "category name : ".$category['name']." parent_id ".$category['parent_id']."<br>";
=======
                            //echo "web-category2 : ".$prod['web-category2']." - ".$category['name']." <br> ";
                            if($prod['web-category2'] == 'Gaming')
                            {
                                $prod['web-category2'] = 'Gaming Products';
                            }

                            if($category['name'] == $prod['web-category2'])
                            {
                                echo "web-category2 category name : ".$category['name']." parent_id ".$category['parent_id']."<br>";
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
                            {
>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category3']))
                        {
<<<<<<< HEAD
                            echo "web-category3 : ".$prod['web-category3']." - ".$category['name']." <br> ";
                            if($category['name'] == $prod['web-category3'])
                            {
                                echo "category name : ".$category['name']." parent_id : ".$category['parent_id']." / ".$subcat1."<br>";
=======
                            //echo "web-category3 : ".$prod['web-category3']." - ".$category['name']." <br> ";
                            if($prod['web-category3'] == 'Fujifilm Instant Cameras')
                            {
                                $prod['web-category3'] = 'Fujifilm Instant Instax Cameras';
                            }

                            if($prod['web-category3'] == 'Cables & Adaptors')
                            {
                                $prod['web-category3'] = 'Computer Cables & Adaptors';
                            }

                            if($prod['web-category3'] == 'Cases Covers & Bags')
                            {
                                $prod['web-category3'] = 'Laptop Cases, Covers & Bags';
                            }

                            if($prod['web-category3'] == 'Chargers')
                            {
                                $prod['web-category3'] = 'Laptop Chargers';
                            }

                            if($prod['web-category3'] == 'Hubs & Docks')
                            {
                                $prod['web-category3'] = 'Computer Hubs & Docks';
                            }

                            if($prod['web-category3'] == 'Webcams')
                            {
                                $prod['web-category3'] = 'Computer Webcams';
                            }

                            if($prod['web-category3'] == 'Console Accessories')
                            {
                                $prod['web-category3'] = 'Console Gaming Accessories';
                            }

                            if($prod['web-category3'] == 'Consoles')
                            {
                                $prod['web-category3'] = 'Gaming Consoles';
                            }

                            if($prod['web-category3'] == 'Business')
                            {
                                $prod['web-category3'] = 'Business Laptops';
                            }

                            if($prod['web-category3'] == 'Home & Student')
                            {
                                $prod['web-category3'] = 'Home & Student Laptops';
                            }

                            if($prod['web-category3'] == 'Monitor Accessories')
                            {
                                $prod['web-category3'] = 'Computer Monitor Accessories';
                            }

                            if($prod['web-category3'] == 'Monitor Mounts & Stands')
                            {
                                $prod['web-category3'] = 'Monitor Arms, Mounts & Stands';
                            }

                            if($prod['web-category3'] == 'Monitors')
                            {
                                $prod['web-category3'] = 'Computer Monitors';
                            }

                            if($prod['web-category3'] == 'Ink')
                            {
                                $prod['web-category3'] = 'Printer Ink';
                            }

                            if($prod['web-category3'] == 'Paper')
                            {
                                $prod['web-category3'] = 'Photo Printing Papers';
                            }

                            if($prod['web-category3'] == 'Shredders')
                            {
                                $prod['web-category3'] = 'Paper Shredders';
                            }

                            if($prod['web-category3'] == 'Light Meters')
                            {
                                $prod['web-category3'] = 'Light Meters for Cameras';
                            }

                            if($category['name'] == $prod['web-category3'])
                            {
                                echo "web-category3 category name : ".$category['name']." parent_id : ".$category['parent_id']." / ".$subcat1."<br>";
>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                                if($category['parent_id'] == $subcat1)
                                {
                                    $subcat2 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category4']))
                        {
<<<<<<< HEAD
                            echo "web-category4 : ".$prod['web-category4']." <br> ";
                            if($category['name'] == $prod['web-category4'])
                            {
                                echo "category name : ".$category['name']." parent_id : ".$category['parent_id']." / ".$subcat2."<br>";
=======
                            //echo "web-category4 : ".$prod['web-category4']." <br> ";
                            if($category['name'] == $prod['web-category4'])
                            {
                                echo "web-category4 : ".$category['name']." parent_id : ".$category['parent_id']." / ".$subcat2."<br>";
>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                                if($category['parent_id'] == $subcat2)
                                {
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                    }
                }
                echo $catList."<br>";
                $forLogs .= $catList."\n";
                //comment out for now until bugged category is fixed May 6, 2024
                if (count($categoryIds)) {

                    $forLogs .= "Categories: ".$catList."\n";
                    //echo "update categories: ".$catList."<br />";
                    try
                    {
                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        try
                        {
                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $forLogs .=   $e->getMessage();
                        }
                    }
                }



            } catch (\Magento\Framework\Exception\NoSuchEntityException $e){

                //does not exist
                //insert new product
                $prodname = $prod['desc1']. " ".$prod['desc2']. " ".$prod['desc3'];
                $forLogs .= "Product Name: ".$prodname."\n";
                $forLogs .= "SKU: ".$prod['code']."\n";
                $product = $this->productFactory->create();
                $product->setSku($prod['code']);
                $product->setName($prodname);
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
                $product->setVisibility(4);
                $product->setAttributeSetId(4);
                $product->setMetaTitle($prodname);
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $price = 0;
                $tax = 10;
                if(isset($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']))
                {
                    $product->setPrice($prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region'][0]['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region'][0]['prc-recommend-retail-inc-tax']."\n";
                }
                else
                {
                    $product->setPrice($prod['pricing']['price-region']['prc-recommend-retail-inc-tax']);
                    $price = $prod['pricing']['price-region']['prc-recommend-retail-inc-tax'];
                    $tax = $prod['pricing']['price-region']['prc-tax-rate'];
                    $forLogs .= "Price ".$prod['pricing']['price-region']['prc-recommend-retail-inc-tax']."\n";
                }


                $pricetocost = floatval($price);
                $tax = floatval($tax);
                $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                if(isset($prod['stk-replacement-cost']))
                {

                    $cost = $prod['stk-replacement-cost'];
                    if($cost == '0' || $cost == '')
                    {
                        if(isset($prod['stk-current-buy']))
                        {
                            $cost = $prod['stk-current-buy'];
                            if ($cost == '0' || $cost == '')
                            {
                                if(isset($prod['whse-avg-cost-swhs']))
                                {
                                    $cost = $prod['whse-avg-cost-swhs']; //change to actual average price

                                    if ($cost == '0' || $cost == '')
                                    {
                                        $pricetocost = floatval($price);
                                        $cost = $pricetocost / ((1 + $tax) / 100); //prc-recommend-retail-inc-tax / ( 1 + prc-tax-rate / 100 )
                                    }
                                }


                            }
                        }
                    }
                }

                $product->setCustomAttribute('cost', $cost);

                $marketplacesprice = 0;

                if(isset($prod['pricing']['price-region'][0]['prc-break-price-4-inc']))
                {
                    $marketplacesprice = $prod['pricing']['price-region'][0]['prc-break-price-4-inc'];
                    if(empty($marketplacesprice))
                    {
                        $marketplacesprice = 0;
                    }
                }
                else
                {
                    if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
                    {
                        $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
                        if(empty($marketplacesprice))
                        {
                            $marketplacesprice = 0;
                        }
                    }
                }
                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);
                echo "marketplacesprice - ".$marketplacesprice;
                echo "<br />";
                //set brand
                //digiSeconds brand
                if($prod['stk-brand-desc'] == 'digiSeconds')
                {
                    if(isset($prod['d2brand']))
                    {
                        $brandName = strtolower($prod['d2brand']);
                    }
                    else
                    {
                        $brandName = strtolower($prod['stk-brand']);
                    }
                }
                else
                {
                    $brandName = strtolower($prod['stk-brand']);
                }
                $forLogs .= "Brand: ".$brandName."\n";
                if(isset($this->attributeOptions[strtolower($brandName)]))
                {
                    $brandCode = $this->attributeOptions[strtolower($brandName)];
                    $product->setBrand($brandCode);
                }


//                // If desired, you can set a tax class like so:
//                //$product->setCustomAttribute('tax_class_id', $taxClassId);
                $toUrl = $prodname;
                $toUrl = preg_replace('/[+]/', 'plus', $toUrl);
                $urltext = preg_replace('#[^0-9a-z]+#i', '-', $toUrl);
                $urltext = strtolower($urltext);
                $product->setUrlKey($urltext);

                // set gtin and apn
                $barcode1 = "";
                $barcode2 = "";
                $barcode3 = "";
                $barcode4 = "";
                if(isset($prod['gtins']['gtin'])) {
                    //set barcode
                    if (count($prod['gtins']['gtin']) == count($prod['gtins']['gtin'], COUNT_RECURSIVE)) {
                        $barcode1 = $prod['gtins']['gtin']['id'];

                    } else {
                        $x = 1;
                        foreach ($prod['gtins']['gtin'] as $gtin)
                        {
                            switch ($x)
                            {
                                case 1:
                                    $barcode1 = $gtin['id'];
                                    break;
                                case 2:
                                    $barcode2 = $gtin['id'];
                                    break;
                                case 3:
                                    $barcode3 = $gtin['id'];
                                    break;
                                case 4:
                                    $barcode4 = $gtin['id'];
                                    break;
                                default:

                            }
                            $x++;
                        }
                    }
                }
                //work around to set
                $product->setCustomAttribute('barcode1',$barcode1);
                $product->setCustomAttribute('barcode2',$barcode2);
                $product->setCustomAttribute('barcode3',$barcode3);
                $product->setCustomAttribute('barcode4',$barcode4);
                $forLogs .= "barcode1 ".$barcode1."\n";
                $forLogs .= "barcode2 ".$barcode2."\n";
                $forLogs .= "barcode3 ".$barcode3."\n";
                $forLogs .= "barcode4 ".$barcode4."\n";

                if(isset($prod['warehouse']['whse']))
                {

                    foreach ($prod['warehouse']['whse'] as $qt)
                    {
                        if(is_array($qt))
                        {
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($qt['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($qt['qty_available']);
                            $forLogs .= $qt['code']." - ".$qt['qty_available']."\n";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }
                        else
                        {
                            // to handle single warehouse
                            $sourceItem = $this->sourceItemFactory->create();
                            $sourceItem->setSourceCode($prod['warehouse']['whse']['code']);
                            $sourceItem->setSku($prod['code']);
                            $sourceItem->setStatus(1);
                            $sourceItem->setQuantity($prod['warehouse']['whse']['qty_available']);
                            $forLogs .= $prod['warehouse']['whse']['code']." - ".$prod['warehouse']['whse']['qty_available']."\n";
                            try {
                                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                                //return true;
                            } catch (\Exception $e) {
                                echo "error source ". $e->getMessage();
                            }
                        }

                    }
                }

//                $sourceItem = $this->sourceItemFactory->create();
//                $sourceItem->setSourceCode('default');
//                $sourceItem->setSku($prod['code']);
//                $sourceItem->setStatus(1);
//                $sourceItem->setQuantity(0);
//                $forLogs .="default - 0 \n";
//                $this->sourceItemsSaveInterface->execute([$sourceItem]);
                //redeploy

                $marketplacesprice = 0;
                if(isset($prod['pricing']['price-region'][0]['prc-break-price-4-inc']))
                {
                    $marketplacesprice = $prod['pricing']['price-region'][0]['prc-break-price-4-inc'];
                    if(empty($marketplacesprice))
                    {
                        $marketplacesprice = 0;
                    }
                }
                else
                {
                    if(isset($prod['pricing']['price-region']['prc-break-price-4-inc']))
                    {
                        $marketplacesprice = $prod['pricing']['price-region']['prc-break-price-4-inc'];
                        if(empty($marketplacesprice))
                        {
                            $marketplacesprice = 0;
                        }
                    }
                }
                $forLogs .= "Marketplaces Price ".$marketplacesprice."\n";
                $product->setCustomAttribute('marketplaces_price', $marketplacesprice);

                $product->setCustomAttribute('apn', $prod['stk-apn-number']);
                $product->setCustomAttribute('qff_base', $prod['qff-base-points-per-dollar']);
                $product->setCustomAttribute('qff_bonus_points', $prod['qff-bonus-points-per-dollar']);
                if(isset($prod['qff-store-product-name']))
                {
                    $product->setCustomAttribute('qff_store_product_name', $prod['qff-store-product-name']);
                }
                if(isset($prod['qff-store-price']))
                {
                    $product->setCustomAttribute('qff_store_price', $prod['qff-store-price']);
                }

                //digiSeconds Condition : OPENBOX, PRELOVED, REFURB
                if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['stk-sort-analysis-code']);
                }
                else if((isset($prod['d2lvl1'])) && (!empty($prod['d2lvl1'])))
                {
                    $product->setCustomAttribute('item_condition', $prod['d2lvl1']);
                }
                else {
                    $product->setCustomAttribute('item_condition', " ");
                }

                if((isset($prod['d2lvl2'])) && (!empty($prod['d2lvl2'])))
                {
                    $product->setCustomAttribute('item_rating', $prod['d2lvl2']);
                }
                else {
                    $product->setCustomAttribute('item_rating', " ");
                }

                if((isset($prod['d2desc'])) && (!empty($prod['d2desc'])))
                {
                    $product->setCustomAttribute('d2desc', $prod['d2desc']);
                }
                else {
                    $product->setCustomAttribute('d2desc', " ");
                }

                if((isset($prod['d2newsku'])) && (!empty($prod['d2newsku'])))
                {
                    $product->setCustomAttribute('d2newsku', $prod['d2newsku']);
                }
                else {
                    $product->setCustomAttribute('d2newsku', " ");
                }

                if(isset($prod['stk-storage-type-flag']))
                {
                    if($prod['stk-storage-type-flag'] == 'H')
                    {
                        $product->setCustomAttribute('dangerous_goods', '1');
                    }
                    else
                    {
                        $product->setCustomAttribute('dangerous_goods', '0');
                    }

                    if($prod['stk-storage-type-flag'] == 'B')
                    {
                        $product->setCustomAttribute('bulky_item', 1);
                    }
                    else
                    {
                        $product->setCustomAttribute('bulky_item', 0);
                    }

                }
                else
                {
                    $product->setCustomAttribute('dangerous_goods', '0');
                    $product->setCustomAttribute('bulky_item', 0);
                }

                if(isset($prod['stock-division']))
                {
                    $product->setCustomAttribute('stock_division', $prod['stock-division']);
                }

                if(isset($prod['stock-department']))
                {
                    $product->setCustomAttribute('stock_department', $prod['stock-department']);
                }

                if(isset($prod['stock-category']))
                {
                    $product->setCustomAttribute('stock_category', $prod['stock-category']);
                }

                if(isset($prod['stock-class']))
                {
                    $product->setCustomAttribute('stock_class', $prod['stock-class']);
                }

                $today = date('Y-m-d');
                $product->setCustomAttribute('date_update', $today);
                $product->setCustomAttribute('is_nda', 1);
                $this->productRepository->save($product);

                $parent = "";
                $subcat1 = "";
                $subcat2 = "";
                $subcat3 = "";

                //set categories
                $categoryIds = array();
                $catList = "";
                $productCategoryIds = $product->getCategoryIds();
                $shouldupdate = false;


                if (count($getCategoryList))
                {
                    foreach ($getCategoryList as $id => $category)
                    {
                        //digiSeconds
                        if($prod['stock-division'] == 'S' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }
                        else if($prod['stk-brand-desc'] == 'digiSeconds' && $category['name'] == 'digiSeconds')
                        {
                            $catList .= $category['name'] . " - " .$category['id']." : ";
                            $categoryIds[] = $category['id'];
                        }

                        //digiSeconds
                        if((isset($prod['stk-sort-analysis-code'])) && (!empty($prod['stk-sort-analysis-code'])))
                        {
                            if($prod['stk-sort-analysis-code'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['stk-sort-analysis-code'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            if($prod['stk-sort-analysis-code'] == 'USED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                        }
                        else if(isset($prod['d2lvl1']))
                        {
                            if($prod['d2lvl1'] == 'OPENBOX' && $category['name'] == 'OPENBOX')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'REFURB' && $category['name'] == 'REFURB')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }

                            //digiSeconds
                            if($prod['d2lvl1'] == 'PRELOVED' && $category['name'] == 'PRELOVED')
                            {
                                $catList .= $category['name'] . " - " .$category['id']." : ";
                                $categoryIds[] = $category['id'];
                            }
                        }

<<<<<<< HEAD
=======
                        if($prod['web-category1'] == 'Cameras')
                        {
                            $prod['web-category1'] = 'Digital Cameras';
                        }

>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                        if($category['name'] == $prod['web-category1'])
                        {
                            echo "web-category1 : ".$prod['web-category1']." <br> ";
                            if($parent == "")
                            {
                                if($category['parent_id'] == '2')
                                {
                                    $parent = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .= $category['name'] . " - " .$category['id'] ." : ";
                                    $categoryIds[] = $category['id'];
                                }

                            }

                        }
                        if(isset($prod['web-category2']))
                        {
                            echo "web-category2 : ".$prod['web-category2']." <br> ";
<<<<<<< HEAD
                            if($category['name'] == $prod['web-category2'])
                            {
                                echo "category name : ".$category['name']." parent_id ".$category['parent_id']."<br>";
=======
                            if($prod['web-category2'] == 'Gaming')
                            {
                                $prod['web-category2'] = 'Gaming Products';
                            }

                            if($category['name'] == $prod['web-category2'])
                            {
                                echo "category name : ".$category['name']." parent_id ".$category['parent_id']."<br>";
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                            else if($category['name'] == "Camera Cases and Bags" && $prod['web-category2'] == "Bags & Cases")
                            {
>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                                if($category['parent_id'] == $parent)
                                {
                                    $subcat1 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }

                        if(isset($prod['web-category3']))
                        {
                            echo "web-category3 : ".$prod['web-category3']." <br> ";
<<<<<<< HEAD
=======

                            if($prod['web-category3'] == 'Fujifilm Instant Cameras')
                            {
                                $prod['web-category3'] = 'Fujifilm Instant Instax Cameras';
                            }

                            if($prod['web-category3'] == 'Cables & Adaptors')
                            {
                                $prod['web-category3'] = 'Computer Cables & Adaptors';
                            }

                            if($prod['web-category3'] == 'Cases Covers & Bags')
                            {
                                $prod['web-category3'] = 'Laptop Cases, Covers & Bags';
                            }

                            if($prod['web-category3'] == 'Chargers')
                            {
                                $prod['web-category3'] = 'Laptop Chargers';
                            }

                            if($prod['web-category3'] == 'Hubs & Docks')
                            {
                                $prod['web-category3'] = 'Computer Hubs & Docks';
                            }

                            if($prod['web-category3'] == 'Webcams')
                            {
                                $prod['web-category3'] = 'Computer Webcams';
                            }

                            if($prod['web-category3'] == 'Console Accessories')
                            {
                                $prod['web-category3'] = 'Console Gaming Accessories';
                            }

                            if($prod['web-category3'] == 'Consoles')
                            {
                                $prod['web-category3'] = 'Gaming Consoles';
                            }

                            if($prod['web-category3'] == 'Business')
                            {
                                $prod['web-category3'] = 'Business Laptops';
                            }

                            if($prod['web-category3'] == 'Home & Student')
                            {
                                $prod['web-category3'] = 'Home & Student Laptops';
                            }

                            if($prod['web-category3'] == 'Monitor Accessories')
                            {
                                $prod['web-category3'] = 'Computer Monitor Accessories';
                            }

                            if($prod['web-category3'] == 'Monitor Mounts & Stands')
                            {
                                $prod['web-category3'] = 'Monitor Arms, Mounts & Stands';
                            }

                            if($prod['web-category3'] == 'Monitors')
                            {
                                $prod['web-category3'] = 'Computer Monitors';
                            }

                            if($prod['web-category3'] == 'Ink')
                            {
                                $prod['web-category3'] = 'Printer Ink';
                            }

                            if($prod['web-category3'] == 'Paper')
                            {
                                $prod['web-category3'] = 'Photo Printing Papers';
                            }

                            if($prod['web-category3'] == 'Shredders')
                            {
                                $prod['web-category3'] = 'Paper Shredders';
                            }

                            if($prod['web-category3'] == 'Light Meters')
                            {
                                $prod['web-category3'] = 'Light Meters for Cameras';
                            }

>>>>>>> 2e5e14811fe3e54b9234030adcd4d60047b59b8c
                            if($category['name'] == $prod['web-category3'])
                            {
                                echo "category name : ".$category['name']." parent_id : ".$category['parent_id']." / ".$subcat1."<br>";
                                if($category['parent_id'] == $subcat1)
                                {
                                    $subcat2 = $category['id'];
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                        if(isset($prod['web-category4']))
                        {
                            echo "web-category4 : ".$prod['web-category4']." <br> ";
                            if($category['name'] == $prod['web-category4'])
                            {
                                echo "category name : ".$category['name']." parent_id : ".$category['parent_id']." / ".$subcat2."<br>";
                                if($category['parent_id'] == $subcat2)
                                {
                                    echo $category['name'] . " - " .$category['id']." - ".$category['parent_id']." : ";
                                    $catList .=$category['name'] . " - " .$category['id']." : ";
                                    $categoryIds[] = $category['id'];
                                }
                            }
                        }
                    }
                }
                echo "<br>";
                echo $catList."<br>";
                $forLogs .= $catList."\n";
                //comment out for now until bugged category is fixed May 6, 2024
                if (count($categoryIds)) {

                    $forLogs .= "Categories: ".$catList."\n";
                    //echo "update categories: ".$catList."<br />";
                    try
                    {
                        $this->categoryLinkManagement->assignProductToCategories($prod['code'], $categoryIds);
                    }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                        try
                        {
                            $this->categoryLinkManagement->assignProductToCategories($prod['code'], array());
                        }  catch (\Magento\Framework\Exception\NoSuchEntityException $e){
                            $forLogs .=   $e->getMessage();
                        }
                    }
                }

            }
        }
        $this->logger->info($forLogs);
        return true;
    }

    public function getSourceItemBySku($sku)
    {
        return $this->sourceItemsBySku->execute($sku);
    }


    protected function getOptionHash(string $attributeCode): array
    {
        $result = [];
        $attribute = $this->productResource->getAttribute($attributeCode);
        $options = $attribute->getSource()->getAllOptions(false);
        foreach ($options as $option) {
            if (!isset($option['value']) || !strlen($option['value'])) {
                continue;
            }
            $result[strtolower($option['label'])] = $option['value'];
        }
        return $result;
    }


    public function getSubCategoryByParentID(int $categoryId): array
    {
        $categoryData = [];

        $getSubCategory = $this->getCategoryData($categoryId);
        foreach ($getSubCategory->getChildrenData() as $category) {
            $categoryData[$category->getId()] = [
                'name'=> $category->getName(),
                'url'=> $category->getUrl(),
                'id'=> $category->getId(),
                'parent_id'=> $category->getParentId()
            ];
            if (count($category->getChildrenData())) {
                $getSubCategoryLevelDown = $this->getCategoryData($category->getId());
                foreach ($getSubCategoryLevelDown->getChildrenData() as $subcategory) {
                    $categoryData[$subcategory->getId()]  = [
                        'name'=> $subcategory->getName(),
                        'url'=> $subcategory->getUrl(),
                        'id'=> $subcategory->getId(),
                        'parent_id'=>$subcategory->getParentId()
                    ];
                    if (count($subcategory->getChildrenData())) {
                        $getSubCategoryLevelDownAgain = $this->getCategoryData($subcategory->getId());
                        foreach ($getSubCategoryLevelDownAgain->getChildrenData() as $sub2category) {
                            $categoryData[$sub2category->getId()]  = [
                                'name'=> $sub2category->getName(),
                                'url'=> $sub2category->getUrl(),
                                'id'=> $sub2category->getId(),
                                'parent_id'=>$sub2category->getParentId()
                            ];
                            if (count($sub2category->getChildrenData())) {
                                $getSubCategoryLevelDownAgain4 = $this->getCategoryData($sub2category->getId());
                                foreach ($getSubCategoryLevelDownAgain4->getChildrenData() as $sub3category) {
                                    $categoryData[$sub3category->getId()]  = [
                                        'name'=> $sub3category->getName(),
                                        'url'=> $sub3category->getUrl(),
                                        'id'=> $sub3category->getId(),
                                        'parent_id'=>$sub3category->getParentId()
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $categoryData;
    }

    public function getCategoryData(int $categoryId): ?CategoryTreeInterface
    {
        try {
            $getSubCategory = $this->categoryManagement->getTree($categoryId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error("Category not found", [$e]);
            $getSubCategory = null;
        }

        return $getSubCategory;
    }

    public function getProductSales($entityId, $price) {
        $SoldProducts = $this->_reportCollectionFactory->create();
        $SoldProdudctCOl = $SoldProducts->addOrderedQty(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'))->addAttributeToFilter('product_id', $entityId);
        /* If does have any product id
         * then return false
         */
        if(!$SoldProdudctCOl->count()):
            return 0;
        endif;
        $SoldProdudctCOl->getSelect()->__toString();
        $product = $SoldProdudctCOl->getFirstItem();
        $productSales = (int)$product->getData('ordered_qty') * $price;
        $this->logger->info('getProductSales, ' . $entityId . ', ' . $product->getData('ordered_qty') . ', ' . $price . ', ' . $productSales);
        return $productSales;
    }

}
