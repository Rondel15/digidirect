<?php

namespace Digidirect\AbstractGiftCard\Service\Config;

/**
 * Interface ValueHandlerInterface
 * @package Digidirect\AbstractGiftCard\Service\Config
 * @api
 */
interface ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null);
}
