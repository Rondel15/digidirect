<?php
namespace Digidirect\CollectAbstractEntity\Model\System\Config\Source;

use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory as AddressAttributeCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class AddressAttributes
 *
 * @package Digidirect\CollectAbstractEntity\Model\System\Config\Source
 */
class AddressAttributes implements ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var AddressAttributeCollectionFactory
     */
    protected $addressAttributeCollectionFactory;

    /**
     * @var array
     */
    protected $labelMapping;

    /**
     * AddressAttributes constructor.
     * @param AddressAttributeCollectionFactory $addressAttributeCollectionFactory
     * @param array $labelMapping
     */
    public function __construct(
        AddressAttributeCollectionFactory $addressAttributeCollectionFactory,
        $labelMapping = []
    ) {
        $this->addressAttributeCollectionFactory = $addressAttributeCollectionFactory;
        $this->labelMapping = $labelMapping;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            /** @var \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection $collection */
            $collection = $this->addressAttributeCollectionFactory->create();
            foreach ($collection->addSystemHiddenFilter()->getItems() as $item) {
                $this->options[] = [
                    'value' => $item->getAttributeCode(),
                    'label' => isset($this->labelMapping[$item->getAttributeCode()]) ?
                        $this->labelMapping[$item->getAttributeCode()] :
                        $item->getFrontendLabel()
                ];
            }
        }

        return $this->options;
    }
}
