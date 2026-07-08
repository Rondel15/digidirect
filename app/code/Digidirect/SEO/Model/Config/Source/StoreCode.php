<?php

namespace Digidirect\SEO\Model\Config\Source;

/**
 * Class StoreCode
 */
class StoreCode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $codeName;

    /**
     * StoreCode constructor.
     * @param array $fields
     */
    public function __construct(
        array $fields = []
    ) {
        $this->codeName = $fields;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->codeName;
    }
}
