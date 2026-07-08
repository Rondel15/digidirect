<?php
namespace Digidirect\Locator\Model\Config;

/**
 * Class Converter
 * @package Digidirect\Locator\Model\Config
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $scrolls */
        $entities = $source->getElementsByTagName('entity');
        /** @var \DOMNode $scroll */
        foreach ($entities as $entity) {
            /** @var \DOMNode $config */
            foreach ($entity->childNodes as $config) {
                if ($config->nodeName != 'config' || $config->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $data = $this->_convertConfig($config);
                $output[mb_strtolower($entity->attributes->getNamedItem('name')->nodeValue)] = $data;
            }
        }
        return ['locator' => $output];
    }

    /**
     * Convert observer configuration
     *
     * @param \DOMNode $config
     * @return array
     */
    public function _convertConfig($config)
    {
        /** Parse instance configuration */
        $instance = $config->attributes->getNamedItem('instance')->nodeValue;
        if (!$instance) {
            throw new \InvalidArgumentException('Attribute instance is missed');
        }

        return ['instance' => $instance];
    }
}
