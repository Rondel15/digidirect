<?php
namespace Digidirect\AI\Model\ResourceModel\Queue\Queue;

use Digidirect\AI\Api\Data\QueueInterface;

/**
 * Class Collection
 *
 * @package Digidirect\AI\Model\ResourceModel\Queue\Queue
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AI\Model\Engine\Queue\Queue', 'Digidirect\AI\Model\ResourceModel\Queue\Queue');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Before Load
     *
     * @return void
     */
    protected function _beforeLoad()
    {
        $this->addFieldToFilter(QueueInterface::IS_RETIRED, ['eq' => 0]);
        parent::_beforeLoad();
    }
}
