<?php

namespace Digidirect\StoreLocator\Component;

/**
 * As it is used in different places class was created
 */
class AttributeSetNameNormalizer
{
    /**
     * @var array
     */
    protected $normalizedByName = [];

    /**
     * @param string $name
     * @return string
     */
    public function normalize($name)
    {
        if (!isset($this->normalizedByName[$name])) {
            $this->normalizedByName[$name] = trim(strtolower($name));
        }

        return $this->normalizedByName[$name];
    }
}
