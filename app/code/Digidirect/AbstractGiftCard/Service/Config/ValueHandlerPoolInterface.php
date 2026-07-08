<?php

namespace Digidirect\AbstractGiftCard\Service\Config;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface ValueHandlerPoolInterface
 * @package Digidirect\AbstractGiftCard\Service\Config
 * @api
 */
interface ValueHandlerPoolInterface
{
    /**
     * Retrieves an appropriate configuration value handler
     *
     * @param string $field
     * @return ValueHandlerInterface
     * @throws NotFoundException
     */
    public function get($field);
}
