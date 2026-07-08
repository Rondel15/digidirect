<?php
namespace Digidirect\Catalog\Plugin\Block;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Product list toolbar plugin
 */
class Toolbar
{
    const SORT_ORDER_DESC = 'DESC';

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_collection = null;

    /**
     * DB connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_conn;

    /**
     * @var boolean
     */
    protected $_subQueryApplied = false;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->_conn = $resource->getConnection('catalog');
    }

    /**
     * Plugin - Used to modify default Sort By filters
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param null $result
     * @param \Magento\Framework\Data\Collection $collection
     * @return Toolbar
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        $result,
        $collection
    ) {
        $this->_collection = $collection;
        $collection->getSelect()->joinLeft( 
            'sales_order_item', 
            'e.entity_id = sales_order_item.product_id', 
            array('qty_ordered'=>'SUM(sales_order_item.qty_ordered)')) 
            ->group('e.entity_id') 
            ->order('qty_ordered DESC');
        /*if ($subject->getCurrentOrder() == 'most_viewed') {
            if (!$this->_subQueryApplied) {
                $reportEventTable = $this->_collection->getResource()->getTable('report_event');
                $subSelect = $this->_conn->select()->from(
                    ['report_event_table' => $reportEventTable],
                    'COUNT(report_event_table.event_id)'
                )->where(
                    'report_event_table.object_id = e.entity_id'
                );

                $this->_collection->getSelect()->reset(Select::ORDER)->columns(
                    ['views' => $subSelect]
                )->order(
                    'views '  . self::SORT_ORDER_DESC
                );

                $this->_subQueryApplied = true;
            }
            
        } elseif ($subject->getCurrentOrder() == 'latest') {
            $this->_collection->getSelect()->order('created_at DESC');
            
        } elseif ($subject->getCurrentOrder() == "best_sellers") {
              $collection->getSelect()->joinLeft( 
                'sales_order_item', 
                'e.entity_id = sales_order_item.product_id', 
                array('qty_ordered'=>'SUM(sales_order_item.qty_ordered)')) 
                ->group('e.entity_id') 
                ->order('qty_ordered DESC');
              
        } elseif ($subject->getCurrentOrder() == 'product_name_asc') {
            $this->_collection->setOrder('name', 'ASC');
            
        } elseif ($subject->getCurrentOrder() == 'product_name_desc') {
            $this->_collection->setOrder('name', 'DESC');
            
        } elseif ($subject->getCurrentOrder() == 'price_lowest_first') {
            $this->_collection->getSelect()->order('price ASC');
            
        } elseif ($subject->getCurrentOrder() == 'price_highest_first') {
            $this->_collection->getSelect()->order('price DESC');
            
        } 
        
        //elseif ($subject->getCurrentOrder() == 'highest_percent_discount') {
        //   $this->_collection->getSelect()->order('((price - special_price) / price) DESC');        
        //}*/    

        return $this;
    }
}