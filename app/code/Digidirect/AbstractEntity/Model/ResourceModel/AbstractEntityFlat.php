<?php
namespace Digidirect\AbstractEntity\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\Context;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;

/**
 * Class AbstractEntityFlat
 * @package Digidirect\AbstractEntity\Model\ResourceModel
 */
class AbstractEntityFlat extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * AbstractEntityFlat constructor.
     * @param Context $context
     * @param string $entityName
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $entityName,
        $connectionName = null
    ) {
        $this->entityName = $entityName;
        parent::__construct($context, $connectionName);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(AbstractEntityIndex::TABLE_NAME . $this->entityName, AbstractEntityInterface::ENTITY_ID);
    }
}
