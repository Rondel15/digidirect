<?php
namespace Digidirect\ProductOverlay\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;

/**
 * Class UpgradeData
 *
 * @package Digidirect\ProductOverlay\Setup
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var TypeFactory
     */
    protected $_eavTypeFactory;

    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param TypeFactory $_entityTypeFactory
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        TypeFactory $_entityTypeFactory,
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->_eavTypeFactory = $_entityTypeFactory;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
    }

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $moduleDataSetup, ModuleContextInterface $context)
    {
        $this->context = $context;
        $this->setup = $moduleDataSetup;

        $this->setup->startSetup();
        if ($this->compareVersion('1.0.5')) {
            $this->addNewFromToAttributes();
        }
        if ($this->compareVersion('1.0.7')) {
            $this->addNewOveralayIdAttribute();
        }
        if ($this->compareVersion('1.0.8')) {
            $this->convertSerializedDataToJson($this->setup);
        }
        $this->setup->endSetup();
    }

    /**
     * @param string $version
     * @return bool
     */
    protected function compareVersion(string $version)
    {
        return version_compare($this->context->getVersion(), $version, '<');
    }

    /**
     * @return void
     */
    protected function addNewFromToAttributes()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->setup]);

        $attributes = [
            'product_new_from_date' => [
                'type' => 'datetime',
                'label' => 'Set Product as New from Date',
                'input' => 'date',
                'backend' => 'Magento\Catalog\Model\Attribute\Backend\Startdate',
                'required' => false,
                'sort_order' => 7,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ],
            'product_new_to_date' => [
                'type' => 'datetime',
                'label' => 'Set Product as New to Date',
                'input' => 'date',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime',
                'required' => false,
                'sort_order' => 8,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ],
        ];

        foreach ($attributes as $attributeCode => $attributeConfig) {
            $categorySetup->addAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode,
                $attributeConfig
            );
        }

        foreach ($attributes as $attributeCode => $attributeConfig) {
            $categorySetup->addAttributeToGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                'Product Details',
                $attributeCode,
                100
            );
        }
    }

    /**
     * Create product attribute
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function addNewOveralayIdAttribute()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->setup]);
        $entityType = $this->_eavTypeFactory->create()->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $defaultSetId = $entityType->getDefaultAttributeSetId();
        //Add new group to set
        $groups = [
            'overlays' => ['name' => 'Overlays', 'code' => 'overlays', 'sort' => 30, 'id' => null],
        ];
        foreach ($groups as $code => $data) {
            $categorySetup->addAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $defaultSetId,
                $data['name'],
                60
            );
        }

        $categorySetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'overlay_id',
            [
                'type' => 'text',
                'label' => 'Show Overlay',
                'input' => 'multiselect',
                'user_defined' => true,
                'required' => false,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'source' => 'Digidirect\ProductOverlay\Model\Source\OverlayList',
                'sort_order' => 40,
                'visible' => true,
                'used_in_product_listing' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Overlays'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function convertSerializedDataToJson(ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );

        $queryModifier = $this->queryModifierFactory->create(
            'like',
            [
                'values' => [
                    'customer_group_ids' => "%a%"
                ]
            ]
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('digidirect_product_overlay'),
            'overlay_id',
            'customer_group_ids',
            $queryModifier
        );

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('digidirect_product_overlay'),
            'overlay_id',
            'cond_serialize'
        );
    }
}
