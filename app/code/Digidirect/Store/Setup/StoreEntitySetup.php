<?php
namespace Digidirect\Store\Setup;

use Digidirect\AbstractEntity\Setup\AbstractEntitySetup;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class StoreEntitySetup
 *
 * @package Digidirect\Store\Setup
 */
class StoreEntitySetup extends AbstractEntitySetup
{
    const ABSTRACT_ENTITY_NAME = 'Store';

    /**
     * @var AttributeSetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var TypeFactory
     */
    protected $_eavTypeFactory;

    /**
     * StoreEntitySetup constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param TypeFactory $typeFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        TypeFactory $typeFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->_eavTypeFactory = $typeFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory, $typeFactory, $attributeSetFactory);
    }

    /**
     * @param null $entities
     * @return void
     */
    public function installEntities($entities = null)
    {
        parent::installEntities($entities);
        $entityTypeCode = AbstractEntity::ENTITY_TYPE;
        $entityType = $this->_eavTypeFactory->create()->loadByCode($entityTypeCode);
        $defaultSetId = $entityType->getDefaultAttributeSetId();
        $attributeSet = $this->_attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityType->getId())
            ->addFieldToFilter('attribute_set_name', self::ABSTRACT_ENTITY_NAME)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        if (!$attributeSet) {
            $attributeSet = $this->_attributeSetFactory->create();
            $attributeSet->setEntityTypeId($entityType->getId());
            $attributeSet->setAttributeSetName(self::ABSTRACT_ENTITY_NAME);
            $attributeSet->save();
            $attributeSet->initFromSkeleton($defaultSetId);
            $attributeSet->save();
        }

        //Add new group to set
        $groups = [
            'address' => ['name' => 'Address', 'code' => 'address', 'sort' => 30, 'id' => null],
        ];
        foreach ($groups as $code => $data) {
            $this->addAttributeGroup($entityTypeCode, $attributeSet->getAttributeSetId(), $data['name'], 60);
        }

        $this->addAttributeToStoreEntity($attributeSet, $this->getGeneralGroupAttribute());
        $this->addAttributeToStoreEntity($attributeSet, $this->getAddressGroupAttribute(), 'Address');
    }

    /**
     * @return array
     */
    public function getGeneralGroupAttribute()
    {
        return [
            'image' => [
                'type' => 'varchar',
                'label' => 'Image',
                'input' => 'image',
                'backend' => 'Digidirect\Store\Model\Store\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 50,
                'user_defined' => true,
                'position' => 50,
                'is_global' => ScopedAttributeInterface::SCOPE_STORE,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAddressGroupAttribute()
    {
        return [
            'street' => [
                'type' => 'varchar',
                'label' => 'Street Address',
                'input' => 'text',
                'required' => false,
                'sort_order' => 60,
                'position' => 60,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'city' => [
                'type' => 'varchar',
                'label' => 'City',
                'input' => 'text',
                'required' => false,
                'sort_order' => 70,
                'position' => 70,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'country' => [
                'type' => 'varchar',
                'label' => 'Country',
                'input' => 'select',
                'source' => 'Digidirect\Store\Model\Store\Attribute\Source\Country',
                'required' => false,
                'sort_order' => 80,
                'position' => 80,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'state' => [
                'type' => 'varchar',
                'label' => 'State/Province',
                'input' => 'text',
                'required' => false,
                'sort_order' => 90,
                'position' => 90,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'postcode' => [
                'type' => 'varchar',
                'label' => 'Zip/Postal Code',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'position' => 100,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        ];
    }

    /**
     * @param object $attributeSet
     * @param array $attributes
     * @param string|null $group
     * @return void
     */
    public function addAttributeToStoreEntity($attributeSet, $attributes, $group = 'General')
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
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgradeData(ModuleContextInterface $context)
    {
        $entityType = $this->_eavTypeFactory->create()->loadByCode(AbstractEntity::ENTITY_TYPE);

        $attributeSet = $this->_attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityType->getId())
            ->addFieldToFilter('attribute_set_name', self::ABSTRACT_ENTITY_NAME)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addAttributeToStoreEntity($attributeSet, [
                'longitude' => [
                    'type' => 'varchar',
                    'label' => 'Longitude',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 110,
                    'position' => 110,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ],
                'latitude' => [
                    'type' => 'varchar',
                    'label' => 'Latitude',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 120,
                    'position' => 120,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            ], 'Address');
        }

    }
}