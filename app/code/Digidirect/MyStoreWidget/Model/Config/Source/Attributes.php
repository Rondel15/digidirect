<?php

// @codingStandardsIgnoreFile

namespace Digidirect\MyStoreWidget\Model\Config\Source;

use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
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
    protected $backendTypes;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $backendTypes
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        array $backendTypes = ['varchar', 'text', 'int']
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->backendTypes = $backendTypes;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(AttributeInterface::BACKEND_TYPE, $this->backendTypes, 'in')
                ->create();

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
