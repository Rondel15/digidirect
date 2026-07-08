<?php

namespace Digidirect\AbstractGiftCard\Service\Http;

use Digidirect\AbstractGiftCard\Service\Response;

/**
 * Interface ClientInterface
 * @package Digidirect\AbstractGiftCard\Service\Http
 * @api
 */
interface ClientInterface
{
    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param \Digidirect\AbstractGiftCard\Service\Http\TransferInterface $transferObject
     * @return array
     * @throws \Digidirect\AbstractGiftCard\Service\Http\ClientException
     * @throws \Digidirect\AbstractGiftCard\Service\Http\ConverterException
     */
    public function placeRequest(\Digidirect\AbstractGiftCard\Service\Http\TransferInterface $transferObject);
}
