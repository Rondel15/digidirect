<?php
namespace Digidirect\MyStoreWidget\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class MyStore
 * @package Digidirect\MyStoreWidget\Model\ResourceModel
 */
class MyStore extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_mystorewidget_customer_store', 'id');
    }
}
