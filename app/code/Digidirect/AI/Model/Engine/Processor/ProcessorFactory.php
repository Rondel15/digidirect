<?php

namespace Digidirect\AI\Model\Engine\Processor;

use Digidirect\AI\Model\Engine\Exception\EngineException;
use Digidirect\AI\Model\Engine\Processor\Processor;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObjectFactory;

/**
 * Class ProcessorFactory
 *
 * @package Digidirect\AI\Model\Engine\Processor
 */
class ProcessorFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * Queue Repository
     *
     * @var QueueRepository
     */
    protected $_queueRepository;
    
    /**
     * DataObject
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_initParamsObject;

    /**
     * ProcessorFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param QueueRepository $queueRepository
     * @param DataObjectFactory $dataObject
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        QueueRepository $queueRepository,
        DataObjectFactory $dataObject
    ) {
        $this->_objectManager = $objectManager;
        $this->_queueRepository = $queueRepository;
        $this->_initParamsObject = $dataObject->create();
    }

    /**
     * Get processor instance
     *
     * @param string $processorClass
     * @param array $params
     * @return \Digidirect\AI\Model\Engine\Processor\ProcessorInterface
     * @throws EngineException
     */
    public function getProcessor($processorClass, $params = [])
    {
        $baseParams = ['object_manager' => $this->_objectManager, 'queue_repository' => $this->_queueRepository];
        $initParams = array_merge($params, $baseParams);

        $this->_initParamsObject->setData($initParams);

        /* Processor Factory */
        $processor = $this->_objectManager->create($processorClass, ['initParams' => $this->_initParamsObject]);

        if (!$processor instanceof ProcessorInterface) {
            $message = __('Invalid processor interface');
            throw new EngineException($message);
        }

        return $processor;
    }
}
