<?php
namespace Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping;

use Magento\Framework\DataObject;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Field id name
     *
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
        $this->_init(
            'Digidirect\AI\Model\Integrations\Rule\Mapping\Data',
            'Digidirect\AI\Model\ResourceModel\Integrations\Rule\Mapping'
        );
    }
}
