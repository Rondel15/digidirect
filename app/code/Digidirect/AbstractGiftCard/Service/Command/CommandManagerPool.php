<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class CommandManagerPool
 * @package Digidirect\AbstractGiftCard\Service\Command
 * @api
 */
class CommandManagerPool implements CommandManagerPoolInterface
{
    /**
     * @var CommandManagerInterface[] | TMap
     */
    private $_executors;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $executors
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $executors = []
    ) {
        $this->_executors = $tmapFactory->createSharedObjectsMap(
            [
                'array' => $executors,
                'type' => CommandManagerInterface::class
            ]
        );
    }

    /**
     * Returns Command executor for defined service provider
     *
     * @param string $serviceProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     */
    public function get($serviceProviderCode)
    {
        if (!isset($this->_executors[$serviceProviderCode])) {
            throw new NotFoundException(
                __('Command Executor for %1 is not defined.', $serviceProviderCode)
            );
        }

        return $this->_executors[$serviceProviderCode];
    }
}
