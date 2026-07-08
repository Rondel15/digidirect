<?php

namespace Digidirect\StoreLocator\Setup;

use Digidirect\Store\Setup\StoreEntitySetup;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Digidirect\StoreLocator\Helper\Config as CongigHelper;

/**
 * Class StoreLocatorEntitySetup
 *
 * @package Digidirect\StoreLocator\Setup
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreLocatorEntitySetup extends StoreEntitySetup
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
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                CongigHelper::ATTRIBUTE_FEATURED => [
                    'type' => 'int',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'label' => 'Featured',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 130,
                    'position' => 130,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ],
                CongigHelper::ATTRIBUTE_PRIORITY => [
                    'type' => 'int',
                    'label' => 'Priority',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 140,
                    'position' => 140,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            ], 'Address');
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addAttributeToStoreEntity($attributeSet, [
                CongigHelper::ATTRIBUTE_PHONE_NUMBER => [
                    'type' => 'varchar',
                    'label' => 'Phone Number',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 150,
                    'position' => 150,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            ], 'Address');

            $this->addAttributeToStoreEntity($attributeSet, [
                CongigHelper::ATTRIBUTE_DESCRIPTION => [
                    'type' => 'varchar',
                    'label' => 'Description',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 160,
                    'position' => 160,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true
                ],
                CongigHelper::ATTRIBUTE_OPENING_HOURS => [
                    'type' => 'varchar',
                    'label' => 'Opening Hours',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 170,
                    'position' => 170,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'wysiwyg_enabled' => true,
                    'is_html_allowed_on_front' => true
                ]
            ]);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_FEATURED,
                'is_global',
                ScopedAttributeInterface::SCOPE_WEBSITE
            );
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_PRIORITY,
                'is_global',
                ScopedAttributeInterface::SCOPE_WEBSITE
            );
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $mandatoryAttributes = ['street', 'city', 'country', 'state', 'postcode'];
            $this->updateRequiredAttributes($mandatoryAttributes);
        }
        // @codingStandardsIgnoreStart
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                'longitude',
                'note',
                'Longitude will be automatically populated on save. After that it could be changed manually, editing address fields would not change the data.'
            );
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                'latitude',
                'note',
                'Latitude will be automatically populated on save. After that it could be changed manually, editing address fields would not change the data.'
            );
        }
        // @codingStandardsIgnoreEnd

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_FEATURED,
                'source_model',
                'Digidirect\StoreLocator\Model\Config\Entity\Attribute\Source\Noyes'
            );
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                'state',
                'is_required',
                false
            );
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->addAttributeToStoreEntity($attributeSet, [
                CongigHelper::ATTRIBUTE_DISPLAY_ON_MAP => [
                    'type' => 'int',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'label' => 'Display On Map',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 180,
                    'position' => 180,
                    'user_defined' => true,
                    'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                ]
            ]);
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_DESCRIPTION,
                'backend_type',
                'text'
            );
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_OPENING_HOURS,
                'backend_type',
                'text'
            );
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_OPENING_HOURS,
                'is_html_allowed_on_front',
                true
            );
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                CongigHelper::ATTRIBUTE_OPENING_HOURS,
                'is_wysiwyg_enabled',
                true
            );
        }

        if (version_compare($context->getVersion(), '1.0.10', '<')) {
            $this->moveWrongValues();
        }

    }

    /**
     * @param array $attributes
     * @return void
     */
    protected function updateRequiredAttributes($attributes)
    {
        foreach ($attributes as $attribute) {
            $this->updateAttribute(
                AbstractEntity::ENTITY_TYPE,
                $attribute,
                'is_required',
                true
            );
        }
    }

    /**
     * @return void
     */
    protected function moveWrongValues()
    {
        $connection = $this->getSetup()->getConnection();
        $attributes = [
            CongigHelper::ATTRIBUTE_OPENING_HOURS,
            CongigHelper::ATTRIBUTE_DESCRIPTION
        ];
        $oldTableName = $this->getSetup()->getTable('digidirect_abstractentity_entity_varchar');
        $newTableName = $this->getSetup()->getTable('digidirect_abstractentity_entity_text');
        $fields = 'attribute_id, store_id, entity_id, value';
        foreach ($attributes as $attrCode) {
            $insertSql = "INSERT INTO $newTableName ($fields)
            SELECT $fields FROM $oldTableName
            WHERE attribute_id IN (
            SELECT
                eav_attribute.attribute_id
            FROM
                eav_attribute
            WHERE
            attribute_code = '$attrCode')";
            $connection->query($insertSql);

            $deleteSql = "DELETE FROM $oldTableName WHERE attribute_id IN (
            SELECT
                eav_attribute.attribute_id
            FROM
                eav_attribute
            WHERE
            attribute_code = '$attrCode')";
            $connection->query($deleteSql);
        }
    }
}