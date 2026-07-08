<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\Exception\NotFoundException;
use Digidirect\AbstractGiftCard\Service\CommandInterface;

/**
 * Interface CommandPoolInterface
 * @package Digidirect\AbstractGiftCard\Service\Command
 * @api
 */
interface CommandPoolInterface
{
    /**
     * Retrieves operation
     *
     * @param string $commandCode
     * @return CommandInterface
     * @throws NotFoundException
     */
    public function get($commandCode);
}
