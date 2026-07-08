<?php

namespace Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity;

use \Magento\Framework\Data\Collection\EntityFactoryInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use \Magento\Framework\Event\ManagerInterface;
use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntityFlat as AbstractEntityResource;
use \Magento\Framework\App\ObjectManager;

class FlatCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * FlatCollection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $entityName
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $entityName,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->entityName = $entityName;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            AbstractEntity::class,
            AbstractEntityResource::class
        );
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource()
    {
        if (empty($this->_resource)) {
            $this->_resource = ObjectManager::getInstance()->create(
                $this->getResourceModelName(),
                ['entityName' => $this->entityName]
            );
        }
        return $this->_resource;
    }

    /**
     * @param mixed $field
     * @param bool $joinType
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addAttributeToSelect($field, $joinType = false)
    {
        return parent::addFieldToSelect($field);
    }
}
