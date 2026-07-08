<?php

namespace Digidirect\CheckoutFields\Model\Config;

use Magento\Framework\Config\ConverterInterface;
use \Magento\Framework\Xml\Parser as MagentoParser;

/**
 * Class Converter
 * @package Digidirect\CheckoutFields\Model\Config
 */
class Converter implements ConverterInterface
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
        /** @var \DOMNodeList $fields */
        $fields = $source->getElementsByTagName('field');

        /** @var \DOMNode $field */
        foreach ($fields as $field) {
            $fieldId = $field->attributes->getNamedItem('id')->nodeValue;
            if (!$fieldId) {
                continue;
            }

            if (!$field->childNodes) {
                continue;
            }

            $output['fields'][$fieldId] = $this->_xmlToArray($field);
        }

        return ['checkout_fields' => $output];
    }

    /**
     * @param \DOMNode $currentNode
     * @return array|string
     */
    protected function _xmlToArray($currentNode)
    {
        $content = '';
        /** @var $node \DOMElement */
        foreach ($currentNode->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $content = $content ?: [];

                    $value = null;
                    if ($node->hasChildNodes()) {
                        $value = $this->_xmlToArray($node);
                    }
                    $attributes = [];
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attribute) {
                            $attributes += [$attribute->name => $attribute->value];
                        }
                        $value = ['_value' => $value, '_attribute' => $attributes];
                    }

                    $this->_getElementNodeContent($node, $value, $content);

                    break;
                case XML_CDATA_SECTION_NODE:
                    $content = $node->nodeValue;
                    break;
                case XML_TEXT_NODE:
                    $this->_getTextNodeContent($node, $content);
                    break;
            }
        }
        return $content;
    }

    /**
     * @param \DOMElement $node
     * @param [] $value
     * @param [] $content
     * @return void
     */
    protected function _getElementNodeContent($node, $value, &$content)
    {
        if (isset($content[$node->nodeName])) {
            if (!isset($content[$node->nodeName][0]) || !is_array($content[$node->nodeName][0])) {
                $oldValue = $content[$node->nodeName];
                $content[$node->nodeName] = [];
                $content[$node->nodeName][] = $oldValue;
            }
            $content[$node->nodeName][] = $value;
        } else {
            $content[$node->nodeName] = $value;
        }
    }

    /**
     * Get node content
     *
     * @param \DOMElement $node
     * @param []| string $content
     * @return void
     */
    protected function _getTextNodeContent($node, &$content)
    {
        if (trim($node->nodeValue) !== '') {
            $content = $node->nodeValue;
        }
    }
}
