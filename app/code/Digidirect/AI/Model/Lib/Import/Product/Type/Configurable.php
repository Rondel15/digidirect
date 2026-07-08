<?php
namespace Digidirect\AI\Model\Lib\Import\Product\Type;

class Configurable extends \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable
{
    /**
     * In case we've dynamically added new attribute option during import we need to add it to our cache
     * in order to keep it up to date.
     *
     * @param string $code
     * @param string $optionKey
     * @param string $optionValue
     *
     * @return $this
     */
    public function addAttributeOption($code, $optionKey, $optionValue)
    {
        foreach ($this->_attributes as $attrSetName => $attrSetValue) {
            if (isset($attrSetValue[$code])) {
                $this->_attributes[$attrSetName][$code]['options'][$optionKey] = $optionValue;
            }
            if (!isset($this->_superAttributes[$code]['options'][$optionKey])) {
                $this->_superAttributes[$code]['options'][$optionKey] = $optionValue;
            }
        }
        return $this;
    }
}
