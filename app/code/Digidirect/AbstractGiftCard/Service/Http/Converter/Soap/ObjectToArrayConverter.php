<?php

namespace Digidirect\AbstractGiftCard\Service\Http\Converter\Soap;

use Digidirect\AbstractGiftCard\Service\Http\ConverterInterface;

/**
 * Class ObjectToArrayConverter
 * @package Digidirect\AbstractGiftCard\Service\Http\Converter\Soap
 * @api
 */
class ObjectToArrayConverter implements ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws \Digidirect\AbstractGiftCard\Service\Http\ConverterException
     */
    public function convert($response)
    {
        $response = (array) $response;
        foreach ($response as $key => $value) {
            if (is_object($value)) {
                $response[$key] = $this->convert($value);
            }
        }

        return $response;
    }
}
