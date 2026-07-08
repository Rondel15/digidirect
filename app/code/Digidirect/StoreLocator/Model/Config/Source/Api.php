<?php

namespace Digidirect\StoreLocator\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

// @codingStandardsIgnoreFile

/**
 * All possible API's
 * @since 1.4.0
 */
class Api implements ArrayInterface
{
    /**
     * @var array
     */
    protected $apiPool = [];

    /**
     * Api constructor.
     * @param array $apiPool
     */
    public function __construct(
        array $apiPool = ['google' => 'Google API']
    ) {
        $this->apiPool = $apiPool;
    }

    /**
     * Get all possible API
     *
     * @return array
     */
    public function toOptionArray()
    {
        $api = [];
        foreach ($this->apiPool as $code => $label) {
            $api[] = [
                'value' => $code,
                'label' => __($label)
            ];
        }
        return $api;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $api = [];
        foreach ($this->apiPool as $code => $label) {
            $api[$code] = __($label);
        }
        return $api;
    }
}
