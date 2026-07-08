<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface CommandManagerPoolInterface
 * @package Digidirect\AbstractGiftCard\Service\Command
 * @api
 */
interface CommandManagerPoolInterface
{
    /**
     * Returns Command executor for defined service provider
     *
     * @param string $serviceProviderCode
     * @return CommandManagerInterface
     * @throws NotFoundException
     */
    public function get($serviceProviderCode);
}
