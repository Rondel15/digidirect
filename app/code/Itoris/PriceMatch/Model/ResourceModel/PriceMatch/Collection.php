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

namespace Itoris\PriceMatch\Model\ResourceModel\PriceMatch;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    const MAIN_TABLE_ALIAS = 'pm';

    protected $registry;
    protected $configurableProduct;
    protected $productFactory;

    public function __construct
    (
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->productFactory = $productFactory;
        $this->configurableProduct = $configurableProduct;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->registry = $registry;
    }

    protected function _construct()
    {
        $this->_init('Itoris\PriceMatch\Model\PriceMatch', 'Itoris\PriceMatch\Model\ResourceModel\PriceMatch');
    }

    protected function _initSelect()
    {
        $this->getSelect()->from(
            [self::MAIN_TABLE_ALIAS => $this->getMainTable()]);

        $this->addItorisAttr();
        return $this;
    }

    public function addItorisAttr()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $indexColumn = $productMetadata->getEdition() != 'Community' ? 'row_id' : 'entity_id';

        $attributeRepository = $objectManager->create('Magento\Eav\Api\AttributeRepositoryInterface');
        $attrNameId = $attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, 'name')->getAttributeId();

        $phpTableArray = [];
        $query = $this->getConnection()->select()->from(['cf' => $this->getTable('itoris_pricematch_data')])
            ->where("cf.by_request IS NOT NULL ");
        $data = $this->getConnection()->fetchAll($query);

        foreach ($data as $item){
            $product = $this->productFactory->create()->load($item['product_id']);

            $byRequest = \Zend_Json_Decoder::decode( $item['by_request'] );
            $simpleProduct = $this->configurableProduct->getProductByAttributes($byRequest, $product);
            $phpTableArray[] = ['product_id'=>$item['product_id'], 'child_product_id'=>$simpleProduct->getId(), 'by_request'=>$item['by_request'], 'product_name'=>$simpleProduct->getName()];
        }

        $this->getConnection()->query("CREATE TEMPORARY TABLE IF NOT EXISTS {$this->getTable('itoris_pm_temporary')} (
                                                        product_id int unsigned not null, 
                                                        child_product_id int unsigned not null,
                                                        by_request text
                                                      )");

        $query = $this->getConnection()->select()->from(['check' => $this->getTable('itoris_pm_temporary')]);
        $data = $this->getConnection()->fetchAll($query);

        if(!isset($data[0])){
            foreach ($phpTableArray as $item){
                $this->getConnection()->query("INSERT INTO {$this->getTable('itoris_pm_temporary')} (`product_id`, `child_product_id`, `by_request`)  VALUES ({$item['product_id']},{$item['child_product_id']},{$this->getConnection()->quote($item['by_request'])});");
            }
        }

        $this->getSelect()->distinct()
            ->join(
                ['st'=>$this->getTable('store')],
                "pm.store_id = st.store_id",
                []
            )->joinLeft(
                ['temporary_it'=>$this->getTable('itoris_pm_temporary')],
                "pm.product_id = temporary_it.product_id AND pm.by_request = temporary_it.by_request",
                ['modify_product_id'=>'IF(isnull(temporary_it.child_product_id),pm.product_id,temporary_it.child_product_id)']
            )->joinLeft(
                ['cust'=>$this->getTable('customer_entity')],
                "pm.customer_id = cust.entity_id",
                [
                    'customer_email'=>'IF(isnull(pm.customer_id),pm.email, cust.email)' ,
                    'customer_name'=>"IF(isnull(pm.customer_id),pm.name, IF(isnull(cust.middlename),concat(cust.firstname,' ',cust.lastname),concat(cust.firstname,' ',cust.middlename,' ',cust.lastname)))"
                ]
            )->joinLeft(
                ['ent'=>$this->getTable('catalog_product_entity')],
                "pm.product_id = ent.entity_id",
                ['product_sku'=>'ent.sku']
            )->join(
                ['price_index'=>$this->getTable('catalog_product_index_price')],
                "(
                    (temporary_it.child_product_id IS NOT NULL AND price_index.entity_id = temporary_it.child_product_id) OR
                    (temporary_it.child_product_id IS NULL AND price_index.entity_id = pm.product_id)
                ) AND price_index.website_id = st.website_id AND price_index.customer_group_id = 0",
                ['final_price'=>'IF(isnull(pm.old_price),price_index.final_price,pm.old_price)']
            )->join(
                ['a_varchar'=>$this->getTable('catalog_product_entity_varchar')],
                "a_varchar.{$indexColumn} = ent.{$indexColumn} AND a_varchar.attribute_id = ".$attrNameId.' AND  a_varchar.store_id = (
                SELECT `ee2`.store_id FROM '.$this->getTable('catalog_product_entity_varchar').' AS `ee2` 
                WHERE a_varchar.attribute_id  = ee2.attribute_id AND ent.'.$indexColumn.'  = ee2.'.$indexColumn.' AND  ee2.store_id IN(0, pm.store_id) ORDER BY ee2.store_id DESC LIMIT 1
                )',
                ['product_name'=>'a_varchar.value']
            );

        return $this;
    }

    public function sendItemById($itemId)
    {
        $query = $this->getConnection()->select()->from(['composite' => $this->getSelect()])->where("composite.item_id = ".$itemId);
        $data = $this->getConnection()->fetchAll($query);

        if( isset($data[0]['item_id']) ){
            return $data[0];
        }

        return null;
    }

    public function filterPending()
    {
        $this->getSelect()->where(self::MAIN_TABLE_ALIAS.".status = 'pending'");
        return $this;
    }

    public function getItorisCount()
    {
        $buff = $this->getConnection()->fetchAll($this->getSelect());
        return count($buff);
    }
}