<?php
namespace Digidirect\CollectAbstractEntity\Model\System\Config\Source;

use Digidirect\CollectAbstractEntity\Helper\Config;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\AttributeLoader;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class AbstractEntityAttr
 * @package Digidirect\CollectAbstractEntity\Model\System\Config\Source
 */
class AbstractEntityAttr implements ArrayInterface
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
     * @var Config
     */
    protected $configHelper;

    /**
     * @var array
     */
    protected $options;

    /**
     * AbstractEntityAttr constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $configHelper
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $configHelper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->configHelper = $configHelper;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $this->options = [];
        $entityId = $this->configHelper->getCollectAbstractEntityId();
        if (!$entityId) {
            return $this->options;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeLoader::ATTRIBUTE_SET_ID, $entityId)
            ->create();

        $attributes = $this->attributeRepository->getList(AbstractEntity::ENTITY_TYPE, $searchCriteria);
        foreach ($attributes->getItems() as $item) {
            $this->options[] = [
                'value' => $item->getAttributeCode(),
                'label' => $item->getFrontendLabel(),
            ];
        }
        return $this->options;
    }
}
