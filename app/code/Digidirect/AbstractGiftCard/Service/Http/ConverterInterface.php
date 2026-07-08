<?php

namespace Digidirect\AbstractGiftCard\Service\Http;

/**
 * Interface ConverterInterface
 * @package Digidirect\AbstractGiftCard\Service\Http
 * @api
 */
interface ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws \Digidirect\AbstractGiftCard\Service\Http\ConverterException
     */
    public function convert($response);
}
