<?php
namespace Digidirect\AddressVerification\Model\ResourceModel\Location;

use Digidirect\AddressVerification\Api\LocationRepositoryInterface;
use Digidirect\AddressVerification\Model\ResourceModel\Location;
use Digidirect\ProductAttachment\Model\ResourceModel\Attachment;
use Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Digidirect\AddressVerification\Model\ResourceModel\Location
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $idFieldName = 'entity_id';

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connection = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            'Digidirect\AddressVerification\Model\Location',
            'Digidirect\AddressVerification\Model\ResourceModel\Location'
        );
    }
}
