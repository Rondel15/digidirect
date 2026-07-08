<?php

namespace Digidirect\Locator\Model;

/**
 * @since 1.2.0
 */
interface CoordinatesGetterInterface
{
    /**
     * @param string|array $address
     * @param string $serverApiKey
     * @return array|bool
     */
    public function getCoordinatesByAddress($address, $serverApiKey = null);
}
