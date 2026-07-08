<?php

namespace Digidirect\AbstractGiftCard\Model\Checks;

/**
 * Class \Digidirect\AbstractGiftCard\Model\Checks\SpecificationFactory
 */
class SpecificationFactory
{
    /**
     * Composite Factory
     *
     * @var \Digidirect\AbstractGiftCard\Model\Checks\CompositeFactory
     */
    protected $_compositeFactory;

    /** @var  array mapping */
    protected $_mapping;

    /**
     * Construct
     *
     * @param \Digidirect\AbstractGiftCard\Model\Checks\CompositeFactory $compositeFactory
     * @param array $mapping
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Model\Checks\CompositeFactory $compositeFactory,
        array $mapping = []
    ) {
        $this->_compositeFactory = $compositeFactory;
        $this->_mapping = $mapping;
    }

    /**
     * Creates new instances of service models
     *
     * @param array $data
     * @return Composite
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($data)
    {
        $specifications = array_intersect_key($this->_mapping, array_flip((array)$data));
        return $this->_compositeFactory->create(['list' => $specifications]);
    }
}
