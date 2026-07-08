<?php

namespace Digidirect\StoreLocator\Component;

class Serialize
{
    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        return json_encode($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function unserialize($value)
    {
        return json_decode($value, true);
    }
}
