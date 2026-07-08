<?php

namespace Digidirect\Catalog\Plugin\Block\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class ListProduct
{
    const SORT_ORDER_DESC = 'DESC';
    
    protected $_conn;
    
    protected $_subQueryApplied = false;
    
    protected $logger;
    
    public function __construct(
        ResourceConnection $resource,
        \Psr\Log\LoggerInterface $loggerInterface
    ){
        $this->_conn = $resource->getConnection('catalog');
        $this->logger = $loggerInterface;
    }
    
    public function afterGetLoadedProductCollection($subject, $result) {
        
        if (!$this->_subQueryApplied) {
            //$this->logger->info('afterGetLoadedProductCollection');
            /*$result->getSelect()->joinLeft( 
                'sales_order_item', 
                'e.entity_id = sales_order_item.product_id', 
                array('qty_ordered'=>'SUM(sales_order_item.qty_ordered)')) 
                ->group('e.entity_id') 
                ->order('qty_ordered DESC');*/
            $reportEventTable = $result->getResource()->getTable('report_event');
            $subSelect = $this->_conn->select()->from(
                ['report_event_table' => $reportEventTable],
                'COUNT(report_event_table.event_id)'
            )->where(
                'report_event_table.object_id = e.entity_id'
            );

            $result->getSelect()->reset(Select::ORDER)->columns(
                ['views' => $subSelect]
            )->order(
                'views '  . self::SORT_ORDER_DESC
            );
            $this->_subQueryApplied = true;
        }
        
        return $result;
    }
}