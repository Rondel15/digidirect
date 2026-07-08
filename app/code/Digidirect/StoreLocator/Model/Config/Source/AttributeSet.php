<?php
namespace Digidirect\StoreLocator\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Helper\Config as ConfigHelper;

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
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param AttributeSetRepository $attributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        AttributeSetRepository $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigHelper $configHelper
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->configHelper = $configHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            $availableSets = $this->configHelper->getEntities();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $availableSets, 'in')
                ->create();
            foreach ($this->attributeSetRepository->getList($searchCriteria)->getItems() as $attributeSet) {
                $this->_options[] = [
                    'value' => $attributeSet->getAttributeSetId(),
                    'label' => $attributeSet->getAttributeSetName(),
                ];
            }
        }
        return $this->_options;
    }
}
