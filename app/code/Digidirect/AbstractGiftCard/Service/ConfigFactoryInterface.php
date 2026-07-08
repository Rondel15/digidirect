<?php

namespace Digidirect\AbstractGiftCard\Service;

/**
 * Class ConfigInterfaceFactory
 * @package Digidirect\AbstractGiftCard\Service
 * @api
 */
interface ConfigFactoryInterface
{
    /**
     * @param string|null $serviceCode
     * @param string|null $pathPattern
     * @return mixed
     */
    public function create($serviceCode = null, $pathPattern = null);
}
