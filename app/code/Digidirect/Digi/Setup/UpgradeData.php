<?php

namespace Digidirect\Digi\Setup;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Setup\Exception;
use Magento\Swatches\Helper\Media;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Digidirect\Store\Setup\StoreEntitySetupFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Model\Customer as CustomerEntity;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class UpgradeSchema
 * @package Digidirect\Digi\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Config
     */
    protected $productMediaConfig;

    /**
     * @var File
     */
    protected $driverFile;

    /**
     * @var Media
     */
    protected $swatchHelper;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var KeyFeatureEntitySetup
     */
    protected $keyFeatureEntitySetup;

    /**
     * @var StoreEntitySetup
     */
    private $storeEntitySetupFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $_productAttributeRepository;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var SalesSetupFactory
     */
    private $setupFactory;
    /**
     * @var SeoBrandDescriptionEntitySetupFactory
     */
    private $seoBrandDescriptionEntitySetupFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpgradeData constructor.
     * @param AttributeSetFactory $attributeSetFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepository $attributeRepository
     * @param State $state
     * @param Filesystem $filesystem
     * @param Config $productMediaConfig
     * @param File $driverFile
     * @param Media $swatchHelper
     * @param Json $serializer
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param KeyFeatureEntitySetupFactory $keyFeatureEntitySetup
     * @param StoreEntitySetupFactory $storeEntitySetupFactory
     * @param TypeFactory $typeFactory
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SalesSetupFactory $setupFactory
     * @param SeoBrandDescriptionEntitySetupFactory $seoBrandDescriptionEntitySetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        AttributeSetFactory $attributeSetFactory,
        EavSetupFactory $eavSetupFactory,
        AttributeRepository $attributeRepository,
        State $state,
        Filesystem $filesystem,
        Config $productMediaConfig,
        File $driverFile,
        Media $swatchHelper,
        Json $serializer,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        KeyFeatureEntitySetupFactory $keyFeatureEntitySetup,
        StoreEntitySetupFactory $storeEntitySetupFactory,
        TypeFactory $typeFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        CustomerSetupFactory $customerSetupFactory,
        SalesSetupFactory $setupFactory,
        SeoBrandDescriptionEntitySetupFactory $seoBrandDescriptionEntitySetupFactory,
        LoggerInterface $logger
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->state = $state;
        $this->filesystem = $filesystem;
        $this->productMediaConfig = $productMediaConfig;
        $this->driverFile = $driverFile;
        $this->swatchHelper = $swatchHelper;
        $this->serializer = $serializer;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->keyFeatureEntitySetup = $keyFeatureEntitySetup;
        $this->storeEntitySetupFactory = $storeEntitySetupFactory;
        $this->typeFactory = $typeFactory;
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->customerSetupFactory = $customerSetupFactory;

        try {
            $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->setupFactory = $setupFactory;
        $this->seoBrandDescriptionEntitySetupFactory = $seoBrandDescriptionEntitySetupFactory;
        $this->logger = $logger;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->upgradeTo101($setup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->upgradeTo102($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->upgradeTo103($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->upgradeTo104($setup);
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->upgradeTo105($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->upgradeTo106($setup);
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $this->upgradeTo107($setup);
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->upgradeTo108($setup);
        }
        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->upgradeTo109($setup);
        }
        if (version_compare($context->getVersion(), '1.0.10', '<')) {
            $this->upgradeTo110($setup);
        }
        if (version_compare($context->getVersion(), '1.0.11', '<')) {
            $this->upgradeTo111($setup);
        }
        if (version_compare($context->getVersion(), '1.0.12', '<')) {
            $this->upgradeTo112($setup);
        }

        if (version_compare($context->getVersion(), '1.0.13', '<')) {
            $this->upgradeTo113($setup);
        }
        if (version_compare($context->getVersion(), '1.0.14', '<')) {
            $this->upgradeTo114($setup);
        }
        if (version_compare($context->getVersion(), '1.0.15', '<')) {
            $this->upgradeTo115($setup);
        }
        if (version_compare($context->getVersion(), '1.0.16', '<')) {
            $this->upgradeTo116($setup);
        }

        if (version_compare($context->getVersion(), '1.0.17', '<')) {
            $this->upgradeTo117($setup);
        }

        if (version_compare($context->getVersion(), '1.0.18', '<')) {
            $this->upgradeTo118($setup);
        }

        if (version_compare($context->getVersion(), '1.0.19', '<')) {
            $this->upgradeTo119($setup);
        }

        if (version_compare($context->getVersion(), '1.0.20', '<')) {
            $this->upgradeTo120($setup);
        }

        if (version_compare($context->getVersion(), '1.0.21', '<')) {
            $this->upgradeTo121($setup);
        }
        if (version_compare($context->getVersion(), '1.0.22', '<')) {
            $this->upgradeTo122($setup);
        }

        if (version_compare($context->getVersion(), '1.0.22', '<')) {
            $this->upgradeTo122($setup);
        }
        if (version_compare($context->getVersion(), '1.0.23', '<')) {
            $this->upgradeTo123($setup);
        }
        if (version_compare($context->getVersion(), '1.0.24', '<')) {
            $this->upgradeTo124($setup);
        }
        if (version_compare($context->getVersion(), '1.0.25', '<')) {
            $this->upgradeTo125($setup);
        }
        if (version_compare($context->getVersion(), '1.0.26', '<')) {
            $this->upgradeTo126($setup);
        }
        if (version_compare($context->getVersion(), '1.0.27', '<')) {
            $this->upgradeTo127($setup);
        }
//        if (version_compare($context->getVersion(), '1.0.28', '<')) {
//            $this->upgradeTo128($setup);
//        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo101(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attributesData = [
            'category_image_background_desktop' => [
                'type' => 'varchar',
                'label' => 'Category Background Image Desktop',
                'input' => 'image',
                'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 10,
            ],
            'category_image_background_mobile' => [
                'type' => 'varchar',
                'label' => 'Category Background Image Mobile',
                'input' => 'image',
                'backend' => \Magento\Catalog\Model\Category\Attribute\Backend\Image::class,
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 20,
            ],
        ];

        foreach ($attributesData as $code => $attrData) {
            $eavSetup->addAttribute($entityTypeId, $code, $attrData);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idGroup,
                $code,
                $attrData['sort_order']
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo102(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Images');

        $attributesData = [
            'product_image_background_desktop' => [
                'type' => 'varchar',
                'label' => 'Product Background Image Desktop',
                'input' => 'media_image',
                'backend' => '',
                'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 10,
            ],
            'product_image_background_mobile' => [
                'type' => 'varchar',
                'label' => 'Product Background Image Mobile',
                'input' => 'media_image',
                'backend' => '',
                'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
                'required' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 20,
            ],
        ];

        foreach ($attributesData as $code => $attrData) {
            $eavSetup->addAttribute($entityTypeId, $code, $attrData);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idGroup,
                $code,
                $attrData['sort_order']
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo103(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attributesData = [
            'pronto_stock_status' => [
                'type' => 'int',
                'label' => 'Pronto Stock Status',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default' => 0,
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 10,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
            ],
            'whats_in_the_box' => [
                'type' => 'varchar',
                'label' => 'What\'s in the box',
                'input' => 'textarea',
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 20,
            ],
            'specification' => [
                'type' => 'text',
                'label' => 'Specification',
                'input' => 'textarea',
                'required' => false,
                'user_defined' => true,
                'wysiwyg_enabled' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 30,
            ],
            'mount' => [
                'type' => 'int',
                'label' => 'Mount type',
                'input' => 'select',
                'required' => false,
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 40,
                'visible' => true,
                'searchable' => false,
                'comparable' => true,
                'filterable' => true,
                'filterable_in_search' => true,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'option' => [
                    'values' => [
                        'AF Mount',
                        'F Mount',
                        'Z Mount'
                    ]
                ]
            ]
        ];

        foreach ($attributesData as $code => $attrData) {
            $eavSetup->addAttribute($entityTypeId, $code, $attrData);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idGroup,
                $code,
                $attrData['sort_order']
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Exception
     */
    public function upgradeTo104(ModuleDataSetupInterface $setup)
    {
        try {
            $entitySetup = $this->keyFeatureEntitySetup->create(['setup' => $setup]);
            $entitySetup->installEntities();
        } catch (\Exception $e) {

        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Exception
     */
    public function upgradeTo105(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'varchar',
            'label' => 'Key Features',
            'input' => 'multiselect',
            'required' => false,
            'source' => \Digidirect\Digi\Model\Source\Categories::class,
            'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order' => 50,
            'visible' => true,
            'searchable' => false,
            'comparable' => false,
            'filterable' => false,
            'filterable_in_search' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
        ];

        $eavSetup->addAttribute($entityTypeId, 'key_features', $attrData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'key_features',
            $attrData['sort_order']
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Exception
     */
    public function upgradeTo106(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attributesData = [
            'subject' => [
                'type' => 'int',
                'label' => 'Subject',
                'input' => 'select',
                'required' => false,
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 100,
                'visible' => true,
                'searchable' => false,
                'comparable' => false,
                'filterable' => false,
                'filterable_in_search' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'option' => [
                    'values' => [
                        'Travel',
                        'Landscape',
                        'Wedding & Events',
                        'Portrait',
                        'Family',
                        'Nature & Wildlife',
                        'Sports & Action',
                        'Video Architecture Interior',
                    ]
                ]
            ],
            'experience' => [
                'type' => 'int',
                'label' => 'Experience',
                'input' => 'select',
                'required' => false,
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'sort_order' => 100,
                'visible' => true,
                'searchable' => false,
                'comparable' => false,
                'filterable' => false,
                'filterable_in_search' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'option' => [
                    'values' => [
                        'Beginner',
                        'Enthusiast',
                        'Professional'
                    ]
                ]
            ]
        ];

        foreach ($attributesData as $code => $attrData) {
            $eavSetup->addAttribute($entityTypeId, $code, $attrData);
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idGroup,
                $code,
                $attrData['sort_order']
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo107(ModuleDataSetupInterface $setup)
    {
        /* @var \Magento\Framework\DB\Adapter\AdapterInterface $connection*/
        $connection = $setup->getConnection();
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeId = $eavSetup->getAttributeId($entityTypeId, 'whats_in_the_box');

        $eavSetup->updateAttribute(
            $entityTypeId,
            $attributeId,
            [
                'is_wysiwyg_enabled' => true,
                'backend_type' => 'text'
            ]
        );

        $oldValuesSelect = $connection->select();
        $oldValuesSelect->from(
            ['cpev' => $setup->getTable('catalog_product_entity_varchar')],
            ['attribute_id', 'store_id', 'row_id', 'value']
        )
            ->where('cpev.attribute_id = ?', $attributeId);

        $connection->query($connection->insertFromSelect(
            $oldValuesSelect,
            $setup->getTable('catalog_product_entity_text'),
            ['attribute_id', 'store_id', 'row_id', 'value']
        ));

        $connection->delete(
            $setup->getTable('catalog_product_entity_varchar'),
            [
                'attribute_id = ?' => $attributeId
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo108(ModuleDataSetupInterface $setup)
    {
        /* @var \Magento\Framework\DB\Adapter\AdapterInterface $connection*/
        $connection = $setup->getConnection();
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeId = $eavSetup->getAttributeId($entityTypeId, 'pronto_stock_status');

        $connection->delete(
            $setup->getTable('catalog_product_entity_int'),
            [
                'attribute_id = ?' => $attributeId
            ]
        );

        $eavSetup->updateAttribute(
            $entityTypeId,
            $attributeId,
            [
                'backend_type' => 'varchar',
                'frontend_input' => 'select',
                'source_model' => \Digidirect\Digi\Model\Source\ProntoStatus::class
            ]
        );

        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attributeData = [
            'type' => 'int',
            'label' => 'Qualifies for free shipping',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'default' => 0,
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => false,
            'is_filterable_in_grid' => false,
        ];

        $eavSetup->addAttribute($entityTypeId, 'qualifies_for_free_shipping', $attributeData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'qualifies_for_free_shipping'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Exception
     */
    public function upgradeTo109(ModuleDataSetupInterface $setup)
    {

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'int',
            'label' => 'Type',
            'input' => 'select',
            'required' => false,
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order' => 100,
            'visible' => true,
            'searchable' => false,
            'comparable' => false,
            'filterable' => false,
            'filterable_in_search' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'option' => [
                'values' => [
                    'Full Frame DSLR',
                    'APS-C DSLR',
                    'Micro Four-Thirds',
                    'Full Frame Mirrorless',
                    'APS-C Mirrorless'
                ]
            ]
        ];
        $eavSetup->addAttribute($entityTypeId, 'type', $attrData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'type'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo110(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'int',
            'label' => 'Usage',
            'input' => 'select',
            'required' => false,
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order' => 100,
            'visible' => true,
            'searchable' => false,
            'comparable' => false,
            'filterable' => false,
            'filterable_in_search' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'option' => [
                'values' => [
                    'Hobby',
                    'Commercial shooting',
                    'Racing',
                ]
            ]
        ];
        $eavSetup->addAttribute($entityTypeId, 'usage', $attrData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'usage'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo111(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'int',
            'label' => 'Photography type',
            'input' => 'select',
            'required' => false,
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order' => 100,
            'visible' => true,
            'searchable' => false,
            'comparable' => false,
            'filterable' => false,
            'filterable_in_search' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'option' => [
                'values' => [
                    'Travel',
                    'Street',
                    'Landscape & Ultra-wide',
                    'Sports & Action',
                    'Macro',
                    'Portrait',
                    'General Use',

                ]
            ]
        ];
        $eavSetup->addAttribute($entityTypeId, 'photography_type', $attrData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'photography_type'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo112(ModuleDataSetupInterface $setup)
    {
        $entityType = $this->typeFactory->create()->loadByCode(AbstractEntity::ENTITY_TYPE);

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityType->getId())
            ->addFieldToFilter('attribute_set_name', \Digidirect\Store\Setup\StoreEntitySetup::ABSTRACT_ENTITY_NAME)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        /**
         * \Digidirect\Store\Setup\StoreEntitySetup $storeEntitySetup
         */
        $storeEntitySetup = $this->storeEntitySetupFactory->create(['setup' => $setup]);

        $storeEntitySetup->addAttributeToStoreEntity($attributeSet, [
            'additional_image' => [
                'type' => 'varchar',
                'label' => 'Additional Image',
                'input' => 'image',
                'backend' => 'Digidirect\Store\Model\Store\Attribute\Backend\Image',
                'required' => false,
                'sort_order' => 55,
                'user_defined' => true,
                'position' => 55,
                'is_global' => ScopedAttributeInterface::SCOPE_STORE,
            ],
        ]);

        $storeEntitySetup->addAttributeToStoreEntity($attributeSet, [
            'email' => [
                'type' => 'varchar',
                'label' => 'Email',
                'input' => 'text',
                'required' => false,
                'sort_order' => 130,
                'position' => 130,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ],
            'fax' => [
                'type' => 'varchar',
                'label' => 'Fax',
                'input' => 'text',
                'required' => false,
                'sort_order' => 140,
                'position' => 140,
                'user_defined' => true,
                'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        ], 'Address');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo113(ModuleDataSetupInterface $setup)
    {
        $entityType = $this->typeFactory->create()->loadByCode(AbstractEntity::ENTITY_TYPE);

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityType->getId())
            ->addFieldToFilter('attribute_set_name', \Digidirect\Store\Setup\StoreEntitySetup::ABSTRACT_ENTITY_NAME)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        /**
         * \Digidirect\Store\Setup\StoreEntitySetup $storeEntitySetup
         */
        $storeEntitySetup = $this->storeEntitySetupFactory->create(['setup' => $setup]);

        $storeEntitySetup->addAttributeToStoreEntity($attributeSet, [
            'code' => [
                'type' => 'varchar',
                'label' => 'Code',
                'input' => 'select',
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'required' => false,
                'sort_order' => 54,
                'visible' => true,
                'user_defined' => true,
                'position' => 54,
                'is_global' => ScopedAttributeInterface::SCOPE_STORE,
                'source' => \Digidirect\MSI\Model\Source\Source::class,
            ],
        ]);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo114(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'varchar',
            'label' => 'Model',
            'input' => 'text',
            'required' => false,
            'sort_order' => 90,
            'position' => 90,
            'user_defined' => true,
            'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'searchable' => true,
            'comparable' => false,
            'filterable' => false,
            'filterable_in_search' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => false,
            'used_for_promo_rules' => true,
            'is_html_allowed_on_front' => true,

        ];
        $eavSetup->addAttribute($entityTypeId, 'model', $attrData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'model'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo115(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'int',
            'label' => 'Available for Studio 19',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'default' => 0,
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order' => 15,
            'is_used_in_grid' => true
        ];
        $eavSetup->addAttribute($entityTypeId, 'available_for_studio19', $attrData);
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $idGroup,
            'available_for_studio19'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo116(ModuleDataSetupInterface $setup)
    {
        try {
            $attribute = $this->_productAttributeRepository->get('brand');
            $attribute->setData('aa_status', \Digidirect\AbstractAttributes\Helper\Attribute::STATUS_ENABLED);
            $this->_productAttributeRepository->save($attribute);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {}
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     *
     * @throws \Exception
     */
    public function upgradeTo117(ModuleDataSetupInterface $setup)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup'=> $setup]);
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attrsData = [
            'aipp_number' => [
                'type'      => 'varchar',
                'label'     => 'AIPP number',
                'input'     => 'text',
                'required'  => false,
                'visible'   => true,
                'system'    => false,
                'position'  => 0,
                'user_defined'          => true,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                'used_in_forms'         => ['adminhtml_customer', 'customer_account_edit'],
            ],
            'is_aipp_verified' => [
                'type'      => 'int',
                'label'     => 'AIPP verified',
                'input'     => 'boolean',
                'source'    => Boolean::class,
                'default'   => 0,
                'required'  => false,
                'visible'   => false,
                'system'    => false,
                'position'  => 0,
                'user_defined'          => true,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                'used_in_forms'         => ['adminhtml_customer', 'customer_account_edit'],
            ],
        ];

        foreach ($attrsData as $attrCode => $attrData) {
            $eavSetup->addAttribute(CustomerEntity::ENTITY, $attrCode, $attrData);
            $eavSetup->updateAttribute(CustomerEntity::ENTITY, $attrCode, 'is_used_for_customer_segment', true);
            $attribute = $customerSetup->getEavConfig()->getAttribute(CustomerEntity::ENTITY, $attrCode);
            $attribute->setData('used_in_forms', $attrData['used_in_forms']);
            $attribute->save();
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Exception
     */
    public function upgradeTo118(ModuleDataSetupInterface $setup): void
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup'=> $setup]);
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attrCode = 'contact_number';
        $attrData = [
            'type' => 'varchar',
            'label' => 'Contact number',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'system' => false,
            'position' => 0,
            'user_defined' => true,
        ];

        $eavSetup->addAttribute(CustomerEntity::ENTITY, $attrCode, $attrData);
        $attribute = $customerSetup->getEavConfig()->getAttribute(CustomerEntity::ENTITY, $attrCode);
        $attribute->setData('used_in_forms', ['customer_account_edit']);
        $attribute->save();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo119(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeIds = [
            'aipp_number',
            'is_aipp_verified',
            'contact_number'
        ];

        foreach ($attributeIds as $attributeId) {
            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attributeId
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo120(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(CustomerEntity::ENTITY, 'contact_number', 'is_visible', false);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo121(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'pronto_stock_status');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo122(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $regionTable = $setup->getTable('directory_country_region');
        $regionNameTable = $setup->getTable('directory_country_region_name');

        $deleteFromSelectSql = $connection->select()
            ->from(['region' => $regionTable])
            ->joinLeft(['region_name' => $regionNameTable], 'region.region_id = region_name.region_id')
            ->joinInner(
                ['region_duplicated' => $regionTable],
                'region.country_id = region_duplicated.country_id
                        AND region.code = region_duplicated.code
                        AND region.default_name = region_duplicated.default_name
                        AND region.region_id <> region_duplicated.region_id')
            ->where('region.country_id = ?', 'AU')
            ->where('region_name.locale IS NULL')
            ->where('region_name.name IS NULL')
            ->deleteFromSelect('region');

        $connection->query($deleteFromSelectSql);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo123(ModuleDataSetupInterface $setup)
    {
        /**
         * @var $salesInstaller \Magento\Sales\Setup\SalesSetup
         */
        $salesInstaller = $this->setupFactory->create(
            ['resourceName' => 'sales_setup', 'setup' => $setup]
        );

        $salesInstaller->addAttribute(
            Order::ENTITY,
            'kw_delivery_notice',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'label' => 'Delivery notice',
                'visible' => false,
                'nullable' => true
            ]
        );
        $salesInstaller->addAttribute(
            Order::ENTITY,
            'kw_billing_notice',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'label' => 'Billing notice',
                'visible' => false,
                'nullable' => true
            ]
        );
        $salesInstaller->addAttribute(
            Order::ENTITY,
            'kw_order_additional_info',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'visible' => false,
                'nullable' => true
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo124(ModuleDataSetupInterface $setup)
    {
        try {
            /**
             * @var $entitySetup \Digidirect\Digi\Setup\SeoBrandDescriptionEntitySetup
             */
            $entitySetup = $this->seoBrandDescriptionEntitySetupFactory->create(['setup' => $setup]);
            $entitySetup->installEntities();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo125(ModuleDataSetupInterface $setup)
    {
        try {
            $entityType = $this->typeFactory->create()->loadByCode(AbstractEntity::ENTITY_TYPE);

            $attributeSet = $this->attributeSetFactory->create();
            $setCollection = $attributeSet->getResourceCollection()
                ->addFieldToFilter('entity_type_id', $entityType->getId())
                ->addFieldToFilter(
                    'attribute_set_name',
                    \Digidirect\Digi\Setup\SeoBrandDescriptionEntitySetup::ABSTRACT_ENTITY_NAME
                )
                ->load();
            $attributeSet = $setCollection->fetchItem();

            /**
             * \Digidirect\Store\Setup\StoreEntitySetup $storeEntitySetup
             */
            $storeEntitySetup = $this->storeEntitySetupFactory->create(['setup' => $setup]);

            $storeEntitySetup->addAttributeToStoreEntity($attributeSet, [
                'meta_title' => [
                    'type' => 'varchar',
                    'label' => 'Meta Title',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 55,
                    'user_defined' => true,
                    'position' => 55,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ],
                'meta_description' => [
                    'type' => 'text',
                    'label' => 'Meta Description',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 130,
                    'position' => 130,
                    'user_defined' => true,
                    'wysiwyg_enabled' => false,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ],
                'meta_keywords ' => [
                    'type' => 'text',
                    'label' => 'Meta Keywords ',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 140,
                    'position' => 140,
                    'user_defined' => true,
                    'wysiwyg_enabled' => false,
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function upgradeTo126(ModuleDataSetupInterface $setup)
    {
        try {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $attributesCode = [
                'aipp_number',
                'contact_number'
            ];

            foreach ($attributesCode as $attributeCode) {
                $attribute = $customerSetup->getEavConfig()->getAttribute(
                    CustomerEntity::ENTITY,
                    $attributeCode
                );
                $attribute->setData('used_in_forms', ['adminhtml_customer', 'customer_account_edit']);
                $attribute->save();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }


    /**
     * @param ModuleDataSetupInterface $setup
     * @throws LocalizedException
     */
    public function upgradeTo127(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $idGroup = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

        $attrData = [
            'type' => 'int',
            'label' => 'Web-only sale',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'default' => 0,
            'required' => false,
            'user_defined' => true,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order' => 60,
            'group' => 'General',
            'is_used_in_grid' => true,
            'used_in_product_listing' => true
        ];
        $eavSetup->addAttribute($entityTypeId, 'web_only_sale', $attrData);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
//    public function upgradeTo128(ModuleDataSetupInterface $setup)
//    {
//        $connection = $setup->getConnection();
//        $table = $setup->getTable('Digidirect_layerednavigation_filter_setting');
//        $connection->update($table, ['index_mode' => 2, 'follow_mode' => 2]);
//    }
}
