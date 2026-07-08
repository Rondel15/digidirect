<?php

namespace Digidirect\Collect\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DataObjectFactory;
use Digidirect\Collect\Api\CollectPlaceStockInterface;
use Magento\Framework\Webapi\Exception;

/**
 * Class SourceProcessor
 *
 * @package Digidirect\Collect\Model
 */
class SourceProcessor
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

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
     * @param DataObjectFactory $dataObject
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DataObjectFactory $dataObject
    ) {
        $this->_objectManager = $objectManager;
        $this->_initParamsObject = $dataObject->create();
    }

    /**
     * Get source processor instance (CollectPlaceStockInterface)
     *
     * @param string $processorClass
     * @param array $params
     * @return CollectPlaceStockInterface
     * @throws Exception
     */
    public function getProcessor($processorClass, $params = [])
    {
        $baseParams = ['object_manager' => $this->_objectManager];
        $initParams = array_merge($params, $baseParams);

        $this->_initParamsObject->setData($initParams);

        /* Processor Factory */
        $processor = $this->_objectManager->create($processorClass, ['initParams' => $this->_initParamsObject]);

        if (!$processor instanceof CollectPlaceStockInterface) {
            $message = __('Invalid collect place stock interface');
            throw new Exception($message);
        }

        return $processor;
    }
}
