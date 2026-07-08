<?php
// @codingStandardsIgnoreFile
namespace Digidirect\AI\Test\Unit\Model\Lib\Import\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase;
use \Digidirect\AI\Model\Lib\Import\Product\Entity;
use \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use \Magento\Catalog\Model\Product;

class EntityTest extends AbstractImportTestCase
{
    const EXISTING_SIZE = 10;
    const OPTION_ID = 123;
    const ENTITY_TYPE_ID = 1;
    const MEDIA_DIRECTORY = 'media/import';

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_connection;

    /** @var \Magento\Framework\Json\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonHelper;

    /** @var \Magento\ImportExport\Model\ResourceModel\Import\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dataSourceModel;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\ImportExport\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceHelper;

    /** @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject */
    protected $string;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_eventManager;

    /** @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockRegistry;

    /** @var \Magento\CatalogImportExport\Model\Import\Product\OptionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $optionFactory;

    /** @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockConfiguration;

    /** @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockStateProvider;

    /** @var \Magento\CatalogImportExport\Model\Import\Product\Option|\PHPUnit_Framework_MockObject_MockObject */
    protected $optionEntity;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTime;

    /** @var array */
    protected $data;

    /** @var \Magento\ImportExport\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $importExportData;

    /** @var \Magento\ImportExport\Model\ResourceModel\Import\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $importData;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\ImportExport\Model\ResourceModel\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceHelper;

    /** @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_catalogData;

    /** @var \Magento\ImportExport\Model\Import\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $_importConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_resourceFactory;

    // @codingStandardsIgnoreStart
    /** @var  \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_setColFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\Type\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_productTypeFactory;

    /** @var  \Magento\Catalog\Model\ResourceModel\Product\LinkFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_linkFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_proxyProdFactory;

    /** @var  \Magento\CatalogImportExport\Model\Import\UploaderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_uploaderFactory;

    /** @var  \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystem;

    /** @var  \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_mediaDirectory;

    /** @var  \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_stockResItemFac;

    /** @var  \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_localeDate;

    /** @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerRegistry;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_logger;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\StoreResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeResolver;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $skuProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\Validator|\PHPUnit_Framework_MockObject_MockObject */
    protected $validator;

    /** @var  \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectRelationProcessor;

    /** @var  \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionManager;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor|\PHPUnit_Framework_MockObject_MockObject */
    // @codingStandardsIgnoreEnd
    protected $taxClassProcessor;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product */
    protected $importProduct;

    /**
     * @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface
     */
    protected $errorAggregator;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Catalog\Model\Product\Url|\PHPUnit_Framework_MockObject_MockObject */
    protected $productUrl;

    /** @var  \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory|
     * \PHPUnit_Framework_MockObject_MockObject */
    protected $prodAttrColFac;

    /**
     * @var \Digidirect\AI\Model\Lib\Import\Product\Service\DataSource
     */
    protected $_dataSource;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        parent::setUp();
        /* For parent object construct */
        $this->jsonHelper =
            $this->getMockBuilder('\Magento\Framework\Json\Helper\Data')
                ->disableOriginalConstructor()
                ->getMock();
        $this->importExportData =
            $this->getMockBuilder('\Magento\ImportExport\Helper\Data')
                ->disableOriginalConstructor()
                ->getMock();
        $this->_dataSourceModel =
            $this->getMockBuilder('\Magento\ImportExport\Model\ResourceModel\Import\Data')
                ->disableOriginalConstructor()
                ->getMock();
        $this->config =
            $this->getMockBuilder('\Magento\Eav\Model\Config')
                ->disableOriginalConstructor()
                ->getMock();

        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());

        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->method('select')->willReturn($select);
        $connection->method('getTableName')->will($this->returnArgument(0));
        $connection->method('fetchAll')->will($this->returnValue([]));
        $connection->method('insert')->will($this->returnValue(null));
        $connection->method('lastInsertId')->will($this->returnValue(true));

        $this->resource =
            $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
                ->disableOriginalConstructor()
                ->getMock();
        $this->resource->method('getConnection')->willReturn($connection);

        $this->resourceHelper =
            $this->getMockBuilder('\Magento\ImportExport\Model\ResourceModel\Helper')
                ->disableOriginalConstructor()
                ->getMock();
        $this->string =
            $this->getMockBuilder('\Magento\Framework\Stdlib\StringUtils')
                ->disableOriginalConstructor()
                ->getMock();

        /* For object construct */
        $this->_eventManager =
            $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
                ->getMock();
        $this->stockRegistry =
            $this->getMockBuilder('\Magento\CatalogInventory\Api\StockRegistryInterface')
                ->getMock();
        $this->stockConfiguration =
            $this->getMockBuilder('\Magento\CatalogInventory\Api\StockConfigurationInterface')
                ->getMock();
        $this->stockStateProvider =
            $this->getMockBuilder('\Magento\CatalogInventory\Model\Spi\StockStateProviderInterface')
                ->getMock();
        $this->_catalogData =
            $this->getMockBuilder('\Magento\Catalog\Helper\Data')
                ->disableOriginalConstructor()
                ->getMock();
        $this->_importConfig =
            $this->getMockBuilder('\Magento\ImportExport\Model\Import\Config')
                ->disableOriginalConstructor()
                ->getMock();
        $this->_resourceFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_setColFactory = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_productTypeFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\Type\Factory',
            ['create'],
            [],
            '',
            false
        );
        $this->_linkFactory = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\LinkFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_proxyProdFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_uploaderFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\UploaderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_filesystem =
            $this->getMockBuilder('\Magento\Framework\Filesystem')
                ->disableOriginalConstructor()
                ->getMock();
        $this->_mediaDirectory =
            $this->getMockBuilder('\Magento\Framework\Filesystem\Directory\WriteInterface')
                ->getMock();
        $this->_stockResItemFac = $this->getMock(
            '\Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_localeDate =
            $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime\TimezoneInterface')
                ->getMock();
        $this->dateTime =
            $this->getMockBuilder('\Magento\Framework\Stdlib\DateTime')
                ->disableOriginalConstructor()
                ->getMock();
        $this->indexerRegistry =
            $this->getMockBuilder('\Magento\Framework\Indexer\IndexerRegistry')
                ->disableOriginalConstructor()
                ->getMock();
        $this->_logger =
            $this->getMockBuilder('\Psr\Log\LoggerInterface')
                ->getMock();
        $this->storeResolver =
            $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\StoreResolver')
                ->setMethods([
                    'getStoreCodeToId',
                ])
                ->disableOriginalConstructor()
                ->getMock();
        $this->skuProcessor =
            $this->getMockBuilder('\Digidirect\AI\Model\Lib\Import\Product\Entity\SkuProcessor')
                ->disableOriginalConstructor()
                ->getMock();
        $this->categoryProcessor =
            $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor')
                ->disableOriginalConstructor()
                ->getMock();
        $this->validator =
            $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Validator')
                ->setMethods(['isAttributeValid', 'getMessages', 'isValid'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->objectRelationProcessor =
            $this->getMockBuilder('\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor')
                ->disableOriginalConstructor()
                ->getMock();
        $this->transactionManager =
            $this->getMockBuilder('\Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface')
                ->getMock();

        $this->taxClassProcessor =
            $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor')
                ->disableOriginalConstructor()
                ->getMock();

        $this->scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productUrl = $this->getMockBuilder('\Magento\Catalog\Model\Product\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_dataSource = $this->getMockBuilder('\Digidirect\AI\Model\Lib\Import\Product\Service\DataSource')
            ->setMethods(null)
            ->getMock();

        $this->prodAttrColFac = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $attrCollection = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $attrCollection->method('getSelect')->willReturn($select);
        $attrCollection->method('getItems')->willReturn([]);
        $this->prodAttrColFac->expects($this->any())->method('create')->willReturn($attrCollection);

        $this->errorAggregator = $this->getErrorAggregatorObject();

        $this->data = [];

        $this->_objectConstructor()
            ->_parentObjectConstructor()
            ->_initAttributeSets()
            ->_initTypeModels()
            ->_initSkus();
        
        $this->importProduct = new Entity(
            $this->jsonHelper,
            $this->importExportData,
            $this->_dataSourceModel,
            $this->config,
            $this->resource,
            $this->resourceHelper,
            $this->string,
            $this->errorAggregator,
            $this->_eventManager,
            $this->stockRegistry,
            $this->stockConfiguration,
            $this->stockStateProvider,
            $this->_catalogData,
            $this->_importConfig,
            $this->_resourceFactory,
            $this->optionFactory,
            $this->_setColFactory,
            $this->_productTypeFactory,
            $this->_linkFactory,
            $this->_proxyProdFactory,
            $this->_uploaderFactory,
            $this->_filesystem,
            $this->_stockResItemFac,
            $this->_localeDate,
            $this->dateTime,
            $this->_logger,
            $this->indexerRegistry,
            $this->storeResolver,
            $this->skuProcessor,
            $this->categoryProcessor,
            $this->validator,
            $this->objectRelationProcessor,
            $this->transactionManager,
            $this->taxClassProcessor,
            $this->scopeConfig,
            $this->productUrl,
            $this->_dataSource,
            $this->prodAttrColFac,
            $this->getMockBuilder('Digidirect\AI\Model\Logger\Logger')->disableOriginalConstructor()->getMock(),
            $this->data
        );
    }

    /**
     * @return $this
     */
    protected function _initSkus()
    {
        $this->skuProcessor->expects($this->once())->method('setTypeModels');
        $this->skuProcessor->expects($this->once())->method('getOldSkus')->willReturn([]);
        $this->skuProcessor->method('reloadOldSkus')->willReturnSelf();
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initTypeModels()
    {
        $entityTypes = [
            'simple' => [
                'model' => 'simple_product',
                'params' => [],
            ]
        ];

        $attribute = [
            'type' => 'select',
            'code' => 'size',
            'id' => self::OPTION_ID,
            'options' => [
                self::EXISTING_SIZE => true
            ]
        ];

        $valueMap = [
            ['size', 'Default', $attribute]
        ];
        $productTypeInstance =
            $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType')
                ->disableOriginalConstructor()->getMock();
        $productTypeInstance->expects($this->once())
            ->method('isSuitable')
            ->willReturn(true);
        $productTypeInstance->expects($this->once())
            ->method('getParticularAttributes')
            ->willReturn([]);
        $productTypeInstance->expects($this->once())
            ->method('getCustomFieldsMapping')
            ->willReturn([]);
        $productTypeInstance->method('retrieveAttribute')
            ->will($this->returnValueMap($valueMap));
        $this->_importConfig->expects($this->once())
            ->method('getEntityTypes')
            ->with(Product::ENTITY)
            ->willReturn($entityTypes);
        $this->_productTypeFactory->expects($this->once())->method('create')->willReturn($productTypeInstance);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _objectConstructor()
    {
        $this->optionFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\OptionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->optionEntity = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product\Option')
            ->disableOriginalConstructor()->getMock();
        $this->optionFactory->expects($this->once())->method('create')->willReturn($this->optionEntity);

        $this->_filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue(self::MEDIA_DIRECTORY));

        $this->validator->expects($this->any())->method('init');
        return $this;
    }

    /**
     * @return $this
     */
    protected function _parentObjectConstructor()
    {
        $type = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')->disableOriginalConstructor()->getMock();
        $type->expects($this->any())->method('getEntityTypeId')->will($this->returnValue(self::ENTITY_TYPE_ID));
        $this->config->expects($this->any())->method('getEntityType')->with(Product::ENTITY)->willReturn($type);

        $this->_connection = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->_connection);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initAttributeSets()
    {
        $attributeSetOne = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\Set')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetOne->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn('attributeSet1');
        $attributeSetOne->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $attributeSetTwo = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute\Set')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetTwo->expects($this->any())
            ->method('getAttributeSetName')
            ->willReturn('attributeSet2');
        $attributeSetTwo->expects($this->any())
            ->method('getId')
            ->willReturn('2');
        $attributeSetCol = [$attributeSetOne, $attributeSetTwo];
        $collection = $this->getMockBuilder('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('setEntityTypeFilter')
            ->with(self::ENTITY_TYPE_ID)
            ->willReturn($attributeSetCol);
        $this->_setColFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        return $this;
    }

    public function attributesToCreateDataProvider()
    {
        return [
            1 => [
                [
                    'size' => 7,
                    Entity::COL_TYPE => 'simple',
                    Entity::COL_ATTR_SET => 'Default'
                ]
            ],
            2 => [
                [
                    'size' => self::EXISTING_SIZE,
                    Entity::COL_TYPE => 'simple',
                    Entity::COL_ATTR_SET => 'Default'
                ]
            ]
        ];
    }

    /**
     * Test creating new attribute options, that doesn't exist in Magento
     * @dataProvider attributesToCreateDataProvider
     */
    public function testCreatingNewAttributeOption($attribute)
    {
        $this->_dataSource->setBunch([[$attribute]]);
        $this->invokeMethod($this->importProduct, '_processSelectAttributes');

        $isset = isset(AbstractType::$commonAttributesCache[self::OPTION_ID]['options'][(int)$attribute['size']]);
        if ($attribute['size'] === self::EXISTING_SIZE) {
            $this->assertFalse($isset);
        } else {
            $this->assertTrue($isset);
        }
    }

    /**
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
