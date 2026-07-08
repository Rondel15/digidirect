<?php

namespace Digidirect\StoreLocator\Model\Config\Source;

use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\StoreLocator\Component\StoreEntityExtractor;
use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;

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
    protected $options;

    /**
     * @var AbstractEntityResource
     */
    protected $abstractEntityResource;

    /**
     * @var StoreEntityExtractor
     */
    protected $extractor;

    // @codingStandardsIgnoreStart

    /**
     * Attributes constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AbstractEntityResource $abstractEntityResource
     * @param StoreEntityExtractor $extractor
     * @param array $backendTypes
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        AbstractEntityResource $abstractEntityResource,
        StoreEntityExtractor $extractor,
        array $backendTypes = ['varchar', 'text']
    ) {
        $this->extractor = $extractor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->backendTypes = $backendTypes;
        $this->abstractEntityResource = $abstractEntityResource;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            $id = $this->abstractEntityResource->getAttributeSetIdByName($this->extractor->extractSetName());
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(AttributeInterface::BACKEND_TYPE, $this->backendTypes, 'in')
                ->addFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $id, 'eq')
                ->create();

            $attributes = $this->attributeRepository->getList(AbstractEntity::ENTITY_TYPE, $searchCriteria);
            foreach ($attributes->getItems() as $item) {
                $this->options[] = [
                    'value' => $item->getAttributeCode(),
                    'label' => $item->getFrontendLabel(),
                ];
            }
        }

        return $this->options;
    }
}
