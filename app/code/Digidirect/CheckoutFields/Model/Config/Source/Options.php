<?php

namespace Digidirect\CheckoutFields\Model\Config\Source;

/**
 * Class Options
 * @package Digidirect\CheckoutFields\Model\Config\Source
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $result = [];
        if (isset($options['_attribute'])) {
            $result[0]['label'] = $options['_attribute']['label'];
            $result[0]['value'] = $options['_attribute']['value'];
        } else {
            foreach ($options as $key => $option) {
                $result[$key]['label'] = $option['_attribute']['label'];
                $result[$key]['value'] = $option['_attribute']['value'];
            }
        }
        $this->_options = $result;
        return $this;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_options;
    }
}
