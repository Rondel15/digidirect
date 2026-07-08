<?php

namespace Digidirect\AbstractGiftCard\Service;

/**
 * Interface ConfigInterface
 * @package Digidirect\AbstractGiftCard\Service
 * @api
 */
interface ConfigInterface
{
    /**
     * Retrieve information from service configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null);

    /**
     * Sets method code
     *
     * @param string $serviceCode
     * @return void
     */
    public function setServiceCode($serviceCode);

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern);
}
