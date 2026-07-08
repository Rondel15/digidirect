<?php

namespace Digidirect\AbstractGiftCard\Service\Response;

/**
 * Interface HandlerInterface
 * @package Digidirect\AbstractGiftCard\Service\Response
 * @api
 */
interface HandlerInterface
{
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response);
}
