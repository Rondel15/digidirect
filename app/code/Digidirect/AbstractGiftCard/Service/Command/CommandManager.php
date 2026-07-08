<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\Exception\NotFoundException;
use Digidirect\AbstractGiftCard\Service\Command;
use Digidirect\AbstractGiftCard\Service\CommandInterface;
use Digidirect\AbstractGiftCard\Service\Data\ServiceDataObjectFactoryInterface;
use Digidirect\AbstractGiftCard\Model\ServiceInterface;

/**
 * Class CommandManager
 * @package Digidirect\AbstractGiftCard\Service\Command
 * @api
 */
class CommandManager implements CommandManagerInterface
{
    /**
     * @var CommandPoolInterface
     */
    private $_commandPool;

    /**
     * @var ServiceDataObjectFactoryInterface
     */
    private $_serviceDataObjectFactory;

    /**
     * CommandExecutor constructor.
     * @param CommandPoolInterface $commandPool
     * @param ServiceDataObjectFactoryInterface $serviceDataObjectFactory
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        ServiceDataObjectFactoryInterface $serviceDataObjectFactory
    ) {
        $this->_commandPool = $commandPool;
        $this->_serviceDataObjectFactory = $serviceDataObjectFactory;
    }

    /**
     * Executes command by code
     *
     * @param string $commandCode
     * @param ServiceInterface|null $service
     * @param array $arguments
     * @return ResultInterface|null
     * @throws NotFoundException
     * @throws CommandException
     */
    public function executeByCode($commandCode, ServiceInterface $service = null, array $arguments = [])
    {
        $commandSubject = $arguments;
        if ($service !== null) {
            $commandSubject['service'] = $this->_serviceDataObjectFactory->create($service);
        }

        return $this->_commandPool
            ->get($commandCode)
            ->execute($commandSubject);
    }

    /**
     * Executes command
     *
     * @param CommandInterface $command
     * @param ServiceInterface|null $service
     * @param array $arguments
     * @return ResultInterface|null
     * @throws CommandException
     */
    public function execute(CommandInterface $command, ServiceInterface $service = null, array $arguments = [])
    {
        $commandSubject = $arguments;
        if ($service !== null) {
            $commandSubject['service'] = $this->_serviceDataObjectFactory->create($service);
        }

        return $command->execute($commandSubject);
    }

    /**
     * Retrieves operation
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     */
    public function get($commandCode)
    {
        return $this->_commandPool->get($commandCode);
    }
}
