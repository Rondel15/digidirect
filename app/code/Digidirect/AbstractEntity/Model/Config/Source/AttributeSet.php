<?php
namespace Digidirect\AbstractEntity\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;

class AttributeSet implements ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

    /**
     * @param AttributeSetRepository $attributeSetRepository
     */
    public function __construct(AttributeSetRepository $attributeSetRepository)
    {
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            foreach ($this->attributeSetRepository->getList()->getItems() as $attributeSet) {
                $this->_options[] = [
                    'value' => $attributeSet->getAttributeSetId(),
                    'label' => $attributeSet->getAttributeSetName(),
                ];
            }
        }
        return $this->_options;
    }
}
