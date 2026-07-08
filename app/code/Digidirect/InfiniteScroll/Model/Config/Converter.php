<?php
namespace Digidirect\InfiniteScroll\Model\Config;

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
        $scrolls = $source->getElementsByTagName('scroll');
        /** @var \DOMNode $scroll */
        foreach ($scrolls as $scroll) {
            //$scrollName = $scroll->attributes->getNamedItem('name')->nodeValue;
            /** @var \DOMNode $config */
            foreach ($scroll->childNodes as $config) {
                if ($config->nodeName != 'config' || $config->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $data = $this->_convertConfig($config);
                $output[mb_strtolower($data['handle'])] = $data;
            }
        }
        return ['infinitescrolls' => $output];
    }

    /**
     * Convert observer configuration
     *
     * @param \DOMNode $config
     * @return array
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

        /** Parse selector configuration */
        $selector = $config->attributes->getNamedItem('selector')->nodeValue;
        if (!$selector) {
            throw new \InvalidArgumentException('Attribute selector is missed');
        }

        return ['handle' => $handle, 'instance' => $instance, 'selector' => $selector];
    }
}
