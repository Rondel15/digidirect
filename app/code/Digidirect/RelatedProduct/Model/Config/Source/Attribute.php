<?php

/**
 * Attribute source
 */
namespace Digidirect\RelatedProduct\Model\Config\Source;

class Attribute implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_excludedFrontendInputs;

    /**
     * @var array
     */
    protected $_excludedAttributes;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \Magento\Framework\Api\MetadataServiceInterface
     */
    protected $attrRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param array $excludedFrontendInputs
     * @param array $excludedAttributes
     * @param \Magento\Framework\Api\MetadataServiceInterface $attrRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Framework\Api\MetadataServiceInterface $attrRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        $excludedFrontendInputs = [],
        $excludedAttributes = []
    ) {
        $this->_excludedFrontendInputs = $excludedFrontendInputs;
        $this->_excludedAttributes = $excludedAttributes;
        $this->attrRepository = $attrRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->getOptionAttributes();
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    public function getOptionAttributes()
    {
        $selectFilter[] = $this->filterBuilder
            ->setField('frontend_input')
            ->setConditionType('nin')
            ->setValue($this->_excludedFrontendInputs)
            ->create();

        $selectFilter[] = $this->filterBuilder
            ->setField('attribute_code')
            ->setConditionType('nin')
            ->setValue($this->_excludedAttributes)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($selectFilter)
            ->create();

        $searchResult = $this->attrRepository->getList($searchCriteria);

        $options = [];

        foreach ($searchResult->getItems() as $option) {
            $options[] = ['label' => $option->getFrontendLabel(), 'value' => $option->getAttributeCode()];
        }
        return $options;
    }
}
