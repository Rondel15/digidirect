<?php

namespace Digidirect\AbstractGiftCard\Model;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface;

/**
 * Price model for external catalogs
 */
class AbstractGiftCardEntityFactory
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
     * @return \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface
     * @throws \UnexpectedValueException
     */
    public function create()
    {
        return $this->_objectManager->create(AbstractGiftCardEntityInterface::class);
    }
}
