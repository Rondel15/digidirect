<?php
namespace Digidirect\MyStoreWidget\Model\ResourceModel\MyStore;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Digidirect\MyStoreWidget\Model\ResourceModel\MyStore
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\MyStoreWidget\Model\MyStore', 'Digidirect\MyStoreWidget\Model\ResourceModel\MyStore');
    }
}
