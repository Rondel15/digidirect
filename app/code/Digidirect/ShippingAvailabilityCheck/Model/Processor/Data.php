<?php

namespace Digidirect\ShippingAvailabilityCheck\Model\Processor;

/**
 * Class Data
 * @package Digidirect\ShippingAvailabilityCheck\Model\Processor
 */
class Data
{
    /**
     * @param array ...$args
     * @return string
     */
    public function getUniqueHash(...$args)
    {
        $hash = '';
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $arg = serialize($arg);
            }
            $hash .= md5($arg);
        }
        return $hash;
    }
}
