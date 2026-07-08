<?php

namespace Digidirect\AbstractGiftCard\Service\Http\Converter;

use Digidirect\AbstractGiftCard\Service\Http\ConverterException;
use Digidirect\AbstractGiftCard\Service\Http\ConverterInterface;

/**
 * Class StringToMap
 * @package Digidirect\Wex\Service\Http\Converter
 */
class XmlStringToArray implements ConverterInterface
{
    /**
     * Converts gateway response to ENV structure
     *
     * @param mixed $response
     * @return array
     * @throws ConverterException
     */
    public function convert($response)
    {
        if (!is_string($response)) {
            throw new ConverterException(__('Wrong response type'));
        }

        $data = $this->_prepareXml($response);
        $dataArray = $this->objectToArray(simplexml_load_string($data));

        return $dataArray;
    }

    /**
     * @param string $response
     * @return array
     */
    public function objectToArray($response)
    {
        $response = (array) $response;
        foreach ($response as $key => $value) {
            if (is_object($value)) {
                $response[$key] = $this->objectToArray($value);
            }
        }

        return $response;
    }

    /**
     * Prepare Xml by changing special chars
     *
     * @param string $xml
     * @return string
     */
    protected function _prepareXml($xml)
    {
        return preg_replace('/&(?!(?:apos|quot|[gl]t|amp);|#)/', '&amp;', $xml);
    }
}
