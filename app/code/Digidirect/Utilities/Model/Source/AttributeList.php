<?php
namespace Digidirect\Utilities\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class AttributeList is Utilitarian class
 * @package Digidirect\Utilities\Model\Source
 */
class AttributeList extends AbstractSource
{
    /**
     * Entity type code "product_entity"
     * @var string
     */
    protected $_entity;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterface|null
     */
    protected $_attributesCollection = null;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $_attributeRepository;

    /**
     * AttributeList constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param string $entity
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        $entity = ""
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_attributeRepository = $attributeRepository;
        $this->_entity = $entity;
    }

    /**
     * Get options in format $key => $value
     * @return array
     */
    public function getOptionArray()
    {
        $collection = $this->_getAttributesList();
        $output = [];
        foreach ($collection->getItems() as $item) {

            $output[$item->getAttributeCode()] = $item->getFrontendLabel();
        }
        return $output;
    }

    /**
     * Get Options in format for selects
     * @return array
     */
    public function getAllOptions()
    {
        $options = $this->getOptionArray();
        $output = [];
        foreach ($options as $key => $value) {

            $output[] = [
                'label' => $value,
                'value' => $key
            ];
        }
        return $output;
    }

    /**
     * Get Attributes Collection
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface|null
     */
    protected function _getAttributesList()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();

        $this->_attributesCollection = $this->_attributeRepository->getList(
            $this->_entity,
            $searchCriteria
        );

        return $this->_attributesCollection;
    }
}
