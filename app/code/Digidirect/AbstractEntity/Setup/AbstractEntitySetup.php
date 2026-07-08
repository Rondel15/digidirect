<?php

// @codingStandardsIgnoreFile

namespace Digidirect\AbstractEntity\Setup;

use Digidirect\AbstractEntity\Api\AttributeSetRepositoryInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AbstractEntitySetup extends EavSetup
{
    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * AbstractEntitySetup constructor.
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param TypeFactory $typeFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        TypeFactory $typeFactory,
        AttributeSetFactory $attributeSetFactory,
        AttributeSetRepositoryInterface $attributeSetRepository = null
    ) {
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
        $this->typeFactory = $typeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetRepository = $attributeSetRepository;

        if ($this->attributeSetRepository === null) {
            $this->attributeSetRepository = ObjectManager::getInstance()->get(
                AttributeSetRepositoryInterface::class
            );
        }
    }

    const ADDITIONAL_ATTRIBUTES = [
        'is_global',
        'position',
        'default_value',
        'note',
        'source_entity_type'
    ];

    /**
     * Default entities and attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultEntities()
    {
        return [
            AbstractEntity::ENTITY_TYPE => [
                'entity_model' => 'Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity',
                'attribute_model' => 'Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute',
                'table' => 'digidirect_abstractentity_entity',
                'entity_attribute_collection' => 'Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection',
                'additional_attribute_table' => 'digidirect_abstractentity_eav_attribute',
                'attributes' => [
                    'name' => [
                        'type' => 'varchar',
                        'label' => 'Name',
                        'input' => 'text',
                        'required' => true,
                        'sort_order' => 10,
                        'position' => 10,
                        'is_global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General',
                    ],
                    'status' => [
                        'type' => 'int',
                        'label' => 'Status',
                        'input' => 'select',
                        'source' => 'Digidirect\AbstractEntity\Model\AbstractEntity\Attribute\Source\Status',
                        'sort_order' => 20,
                        'position' => 20,
                        'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'group' => 'General',
                    ],
                ],
            ]
        ];
    }

    /**
     * @param null $entities
     * @return $this
     */
    public function installEntities($entities = null)
    {
        parent::installEntities($entities);
        foreach ($this->getDefaultEntities() as $entityType => $defaultEntity) {
            foreach ($defaultEntity['attributes'] as $attributeName => $attribute) {
                foreach (self::ADDITIONAL_ATTRIBUTES as $additionalAttribute) {
                    if (isset($attribute[$additionalAttribute])) {
                        $this->updateAttribute(
                            $entityType,
                            $attributeName,
                            $additionalAttribute,
                            $attribute[$additionalAttribute],
                            $attribute['sort_order'] ?? null
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param object $attributeSet
     * @param array $attributes
     * @param string|null $group
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addAttributeToEntity($attributeSet, $attributes, $group = 'General')
    {
        $entityTypeCode = AbstractEntity::ENTITY_TYPE;
        $groupId = $this->getAttributeGroupId(
            $entityTypeCode,
            $attributeSet->getAttributeSetId(),
            $group
        );
        foreach ($attributes as $attributeName => $attribute) {
            foreach (AbstractEntitySetup::ADDITIONAL_ATTRIBUTES as $additionalAttribute) {
                $this->addAttribute($entityTypeCode, $attributeName, $attribute);
                if (isset($attribute[$additionalAttribute])) {
                    $this->updateAttribute(
                        $entityTypeCode,
                        $attributeName,
                        $additionalAttribute,
                        $attribute[$additionalAttribute],
                        $attribute['sort_order'] ?? null
                    );
                }
            }

            $this->addAttributeToGroup(
                $entityTypeCode,
                $attributeSet->getAttributeSetId(),
                $groupId,
                $attributeName,
                null
            );
        }
    }

    /**
     * @param string $name
     * @param array $relationsData
     * @param $name
     * @return \Magento\Eav\Model\Entity\Attribute\Set|AbstractModel
     */
    public function getOrCreateAttributeSet($name, array $relationsData = null)
    {
        $entityTypeCode = AbstractEntity::ENTITY_TYPE;
        $entityType = $this->typeFactory->create()->loadByCode($entityTypeCode);

        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityType->getId())
            ->addFieldToFilter('attribute_set_name', $name)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        if (!$attributeSet) {
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet->setEntityTypeId($entityType->getId());
            $attributeSet->setAttributeSetName($name);
            $attributeSet->save();
        }

        if ($relationsData !== null) {
            $this->attributeSetRepository->processRelations($attributeSet->getId(), $relationsData);
        }

        return $attributeSet;
    }
}
