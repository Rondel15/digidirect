<?php
namespace Digidirect\InfiniteScroll\Model;

class ProcessorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
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
     * Create processor model instance
     *
     * @param string $className
     * @param array $arguments
     * @throws \Exception
     * @return ProcessorInterface
     */
    public function create($className, array $arguments = [])
    {
        $object = $this->_objectManager->create($className, $arguments);
        if ($object instanceof ProcessorInterface) {
            return $object;
        }
        throw new \Exception('Processor must implement Digidirect\InfiniteScroll\Model\ProcessorInterface');
    }
}
