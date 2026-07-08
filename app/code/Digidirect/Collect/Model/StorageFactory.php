<?php

namespace Digidirect\Collect\Model;

use Digidirect\Collect\Api\CollectPlaceRepositoryInterface;

class StorageFactory
{
    /**
     * ObjectManager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Storage Factory construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get observer model instance
     *
     * @param string $className
     * @return mixed
     */
    public function get($className)
    {
        return $this->_objectManager->get($className);
    }

    /**
     * Create collect place repository model instance (CollectPlaceRepositoryInterface)
     *
     * @param string $className
     * @param array $arguments
     * @throws \Exception
     * @return CollectPlaceRepositoryInterface
     */
    public function create($className, array $arguments = [])
    {
        $object = $this->_objectManager->create($className, $arguments);
        if ($object instanceof CollectPlaceRepositoryInterface) {
            return $object;
        }
        throw new \Exception('Collect place storage must implement Digidirect\Collect\Api\CollectPlaceRepositoryInterface');
    }
}
