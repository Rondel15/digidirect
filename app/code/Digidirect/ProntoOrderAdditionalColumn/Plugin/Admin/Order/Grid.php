<?php
namespace Digidirect\ProntoOrderAdditionalColumn\Plugin\Admin\Order;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;
// use Magento\Ui\Model\Export\ConvertToCsv;

class Grid extends \Magento\Framework\Data\Collection
{

    // Code that can made export data from query function...
        protected $coreResource;

        protected $adminUsers;

        public function __construct(
            EntityFactoryInterface $entityFactory,
            ResourceConnection $coreResource,
            UserCollection $adminUsers
        ) {
            parent::__construct($entityFactory);
            $this->coreResource = $coreResource;
            $this->adminUsers = $adminUsers;
        }

        public function beforeLoad($printQuery = false, $logQuery = false)
        {
            if ($printQuery instanceof Collection) {
                $collection = $printQuery;
                $select = $collection->getSelect();
                $select->joinLeft(
                    ["sales_order" => $collection->getTable("sales_order")],
                    'main_table.entity_id = sales_order.entity_id'
                );
                // $collection is an order collection


                // $collection = $printQuery;
                    // $joined_tables = array_keys(
                    //     $collection->getSelect()->getPart('from')
                    // );

                    // $collection->getSelect()->columns(
                    //     array(
                    //         // 'pronto_order_number'  => new \Zend_Db_Expr('(SELECT GROUP_CONCAT(`pronto_order_number` SEPARATOR " & ") FROM `sales_order`)')
                    //     )
                    // );




            }

            // option code details of join query...
                // if ($requestName == 'sales_order_grid_data_source') {
                //     if ($result instanceof $this->collection
                //     ) {
                //         if (is_null($this->registry->registry('order_joined'))) {
                //             $select = $this->collection->getSelect();
                //             $select->join(
                //                 ["salesorder" => "sales_order"],
                //                 'main_table.entity_id = salesorder.entity_id',
                //                 'salesorder.pronto_order_number'
                //             )
                //                 ->distinct();
                //             $this->registry->register('order_joined', true);
                //         }
                //     }

                // }

                // return $this->collection;
        }
}
