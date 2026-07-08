<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_ITORIS_PRICE_MATCH
 * @copyright  Copyright (c) 2018 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PriceMatch\Ui\DataProvider\Admin;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $addFieldStrategies;
    protected $addFilterStrategies;
    protected $customerRepository;
    protected $productRepository;
    protected $priceCurrency;
    protected $storeRepository;
    protected $configurableProduct;
    protected $productFactory;

    private $filters = [];


    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Itoris\PriceMatch\Model\ResourceModel\PriceMatch\CollectionFactory $collectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->configurableProduct = $configurableProduct;
        $this->productFactory = $productFactory;
        $this->customerRepository = $customerRepository;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    public function getData()
    {


        if (!$this->getCollection()->isLoaded()) {

            $this->getCollection()->load();
        }

        $items = $this->getCollection()->getData();

        $iItemsNew = [];
        foreach ($items as $item){
            if($item['by_request']){
                $byRequest = \Zend_Json_Decoder::decode( $item['by_request'] );
                $product = $this->productFactory->create()->load( $item['product_id'] );
                $simpleProduct = $this->configurableProduct->getProductByAttributes($byRequest, $product);
                $item['product_name'] = $simpleProduct->getName();
            }

            $iItemsNew[] = $item;
        }

        return [
            'totalRecords' => count($items),
            'items' => $iItemsNew,
        ];
    }

    public function addField($field, $alias = null)
    {

        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->itorisFilter( $filter );
    }

    private function itorisFilter( \Magento\Framework\Api\Filter $filter )
    {
 //       return;
        $collectionClone = clone $this->collection;

        if( $filter->getField() == 'date_created' || $filter->getField() == 'date_response' ){
            $timezone = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $date = $timezone->date( $filter->getValue() );
            $filter->setValue( $date->format('Y-m-d') ) ;
        }

        switch ( $filter->getConditionType() ){
            case 'gteq':
                $query = $this->collection->getConnection()->select()
                    ->from(['composite_table' => $collectionClone->getSelect()], 'item_id')
                    ->where(  $filter->getField()." >= ".$this->collection->getConnection()->quote(   $filter->getValue()  )     );

                break;

            case 'lteq':
                $query = $this->collection->getConnection()->select()
                    ->from(['composite_table' => $collectionClone->getSelect()], 'item_id')
                    ->where(  $filter->getField()." <= ".$this->collection->getConnection()->quote(   $filter->getValue()  )     );

                break;

            case 'like':
                $query = $this->collection->getConnection()->select()
                    ->from(['composite_table' => $collectionClone->getSelect()], 'item_id')
                    ->where($filter->getField()." LIKE ". $this->collection->getConnection()->quote($filter->getValue()) );

                break;

            case 'in':

                if($filter->getValue()){
                    $buff = [];

                    foreach ($filter->getValue() as $item){
                        $buff[] = $this->collection->getConnection()->quote(   $item  );
                    }

                    $buff = implode(",", $buff);
                    $query = $this->collection->getConnection()->select()
                        ->from(['composite_table' => $collectionClone->getSelect()], 'item_id')
                        ->where(  $filter->getField()." IN(". $buff .")"    );
                }

                break;
        }

        $itemIds = [];

        foreach ($this->collection->getConnection()->fetchAll( $query ) as $item ){
            $itemIds[] = $item['item_id'];
        }

        if(!$itemIds){
            $itemIds = [0];
        }
        $this->collection->getSelect()->where('item_id IN('.implode(',', $itemIds).')');
    }

}