<?php
namespace Digidirect\AI\Model\Integrations\Config;

class Converter extends \Magento\Framework\Config\Converter\Dom implements \Magento\Framework\Config\ConverterInterface
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
        $nodeListData = [];

        /** @var $node \DOMNode */
        foreach ($source->childNodes as $node) {
            if ($node->nodeType == XML_ELEMENT_NODE) {
                $nodeData = [];

                /** @var $attribute \DOMNode */
                foreach ($node->attributes as $attribute) {
                    if ($attribute->nodeType == XML_ATTRIBUTE_NODE) {
                        $nodeData[$attribute->nodeName] = $attribute->nodeValue;
                    }
                }

                $childrenData = $this->convert($node);
                if (is_array($childrenData)) {
                    $nodeData = array_merge($nodeData, $childrenData);
                } else {
                    $nodeData = $childrenData;
                }

                if (is_array($nodeData) && isset($nodeData['name'])) {
                    $nodeListData[$nodeData['name']] = $nodeData;
                } else if (!empty($nodeData)) {
                    $nodeListData[$node->nodeName] = $nodeData;
                }
            } elseif ($node->nodeType == XML_CDATA_SECTION_NODE || $node->nodeType == XML_TEXT_NODE
                && trim($node->nodeValue) != ''
            ) {
                return $node->nodeValue;
            }
        }

        return $nodeListData;
    }
}
