<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\Exception\NotFoundException;
use Digidirect\AbstractGiftCard\Service\CommandInterface;
use Digidirect\AbstractGiftCard\Model\ServiceInterface;

/**
 * Interface CommandManagerInterface
 * @api
 */
interface CommandManagerInterface extends CommandPoolInterface
{
    /**
     * Executes command by code
     *
     * @param string $commandCode
     * @param ServiceInterface|null $service
     * @param array $arguments
     * @return ResultInterface|null
     * @throws NotFoundException
     * @throws CommandException
     *
     */
    public function executeByCode($commandCode, ServiceInterface $service = null, array $arguments = []);

    /**
     * Executes command
     *
     * @param CommandInterface $command
     * @param ServiceInterface|null $service
     * @param array $arguments
     * @return ResultInterface|null
     * @throws CommandException
     */
    public function execute(CommandInterface $command, ServiceInterface $service = null, array $arguments = []);
}
