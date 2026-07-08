<?php

namespace Digidirect\FreeGift\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Template implements ArrayInterface
{
    /**
     * @var array
     */
    protected $optionsConfig;

    /**
     * Template constructor.
     *
     * @param array $optionsConfig
     */
    public function __construct(array $optionsConfig = [])
    {
        $this->optionsConfig = $optionsConfig;
    }

    /**
     * Get mapper array
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->optionsConfig as $code => $data) {
            $result[$code] = !empty($data['label']) ? __($data['label']) : '';
        }
        return $result;
    }
}
