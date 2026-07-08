<?php
namespace Digidirect\Collect\Model\ResourceModel\PostCode;

use Digidirect\Collect\Model\ResourceModel\PostCode as PostCodeResource;
use Digidirect\Collect\Model\PostCode;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = PostCode::TABLE_COLUMN_ID;

    /**
     * @var string
     */
    protected $_eventPrefix = 'digidirect_postcode_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(PostCode::class, PostCodeResource::class);
    }
}
