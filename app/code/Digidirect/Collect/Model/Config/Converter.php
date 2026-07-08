<?php

namespace Digidirect\Collect\Model\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return []
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $placestorages */
        $placestorages = $source->getElementsByTagName('placestorage');
        /** @var \DOMNode $placestorage */
        foreach ($placestorages as $placestorage) {
            /** @var \DOMNode $config */
            foreach ($placestorage->childNodes as $config) {
                if ($config->nodeName != 'config' || $config->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $data = $this->_convertConfig($config);
                $output[mb_strtolower($data['handle'])] = $data;
            }
        }

        return ['placestorages' => $output];
    }

    /**
     * Convert observer configuration
     *
     * @param \DOMNode $config
     * @return []
     */
    public function _convertConfig($config)
    {
        /** Parse handle configuration */
        $handle = $config->attributes->getNamedItem('handle')->nodeValue;
        if (!$handle) {
            throw new \InvalidArgumentException('Attribute handle is missed');
        }

        /** Parse instance configuration */
        $instance = $config->attributes->getNamedItem('instance')->nodeValue;
        if (!$instance) {
            throw new \InvalidArgumentException('Attribute instance is missed');
        }

        /** Parse source configuration */
        $source = $config->attributes->getNamedItem('source')->nodeValue;

        return ['handle' => $handle, 'instance' => $instance, 'source' => $source];
    }
}
