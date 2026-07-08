<?php
namespace Digidirect\AbstractEntity\Ui\Component\Listing;

use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var AttributeGroupRepositoryInterface
     */
    protected $attributeGroupRepository;

    /**
     * @var \Magento\Catalog\Ui\Component\ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $attributes = $this->getAttributes();

        foreach ($attributes as $attribute) {
            $config = [];
            if (!isset($this->components[$attribute->getAttributeCode()])) {
                $config['sortOrder'] = ++$columnSortOrder;
                if ($attribute->getIsFilterableInGrid()) {
                    $config['filter'] = $this->getFilterType($attribute->getFrontendInput());
                }
                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
                $column->prepare();
                $this->addComponent($attribute->getAttributeCode(), $column);
            }
        }
        parent::prepare();
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return $this->filterMap[$frontendInput] ?? $this->filterMap['default'];
    }

    /**
     * @return int
     */
    protected function getAttributeSetId()
    {
        return (int)$this->getContext()->getRequestParam(AttributeGroupInterface::ATTRIBUTE_SET_ID);
    }

    /**
     * @return array
     */
    protected function getGroups()
    {
        $attributeGroups = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                AttributeGroupInterface::ATTRIBUTE_SET_ID,
                $this->getAttributeSetId()
            )->create();

        $attributeGroupSearchResult = $this->attributeGroupRepository->getList($searchCriteria);
        foreach ($attributeGroupSearchResult->getItems() as $group) {
            $attributeGroups[$group->getAttributeGroupCode()] = $group;
        }

        return $attributeGroups;
    }

    /**
     * @return array
     */
    protected function getAttributes()
    {
        $attributes = [];
        foreach ($this->getGroups() as $group) {
            $groupAttributes = $this->loadAttributes($group);
            foreach ($groupAttributes as $attribute) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $attributes;
    }

    /**
     * @param AttributeGroupInterface $group
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    protected function loadAttributes(AttributeGroupInterface $group)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, $group->getAttributeGroupId())
            ->addFilter(Attribute::KEY_IS_USED_IN_GRID, 1)
            ->create();

        return $this->attributeRepository->getList(AbstractEntity::ENTITY_TYPE, $searchCriteria)->getItems();
    }
}
