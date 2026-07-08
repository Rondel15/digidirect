<?php
namespace Digidirect\AbstractEntity\Model\Config\Source;

use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Attributes implements ArrayInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList(AbstractEntity::ENTITY_TYPE, $searchCriteria);
            foreach ($attributes->getItems() as $item) {
                $this->_options[] = [
                    'value' => $item->getAttributeCode(),
                    'label' => $item->getFrontendLabel(),
                ];
            }
        }
        return $this->_options;
    }
}
