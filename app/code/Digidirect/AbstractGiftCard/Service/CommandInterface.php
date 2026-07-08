<?php

namespace Digidirect\AbstractGiftCard\Service;

use Digidirect\AbstractGiftCard\Service\Command\CommandException;

/**
 * Interface CommandInterface
 * @package Digidirect\AbstractGiftCard\Service
 * @api
 */
interface CommandInterface
{
    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     * @return null|Command\ResultInterface
     * @throws CommandException
     */
    public function execute(array $commandSubject);
}
