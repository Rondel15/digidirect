<?php
// @codingStandardsIgnoreFile
namespace Digidirect\AI\Test\Unit\Model\Lib\Import\Product\Entity;

use \Digidirect\AI\Model\Lib\Import\Product\Entity\TaxClassProcessor;

class TaxClassProcessorTest extends \PHPUnit_Framework_TestCase
{
    const TAX_CLASS_ID = 1;
    protected $_taxClassProcessor;

    public function setUp()
    {
        $select = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $select->expects($this->any())->method('from')->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());

        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchOne')->will($this->returnValue(self::TAX_CLASS_ID));
        $connection->method('select')->willReturn($select);
        $connection->method('getTableName')->will($this->returnArgument(0));
        $connection->method('fetchAll')->will($this->returnValue([]));
        $connection->method('insert')->will($this->returnValue(null));

        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource->expects($this->once())->method('getConnection')->willReturn($connection);

        $this->_taxClassProcessor = new TaxClassProcessor($resource);
    }

    /**
     * Test creating tax class
     */
    public function testCreateTaxClass()
    {
        $productTypeModel = $this->getMockForAbstractClass(
            \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class,
            [],
            '',
            false
        );

        $taxClassReflection = new \ReflectionClass(\Digidirect\AI\Model\Lib\Import\Product\Entity\TaxClassProcessor::class);
        $createTaxClassMethodReflection = $taxClassReflection->getMethod('createTaxClass');
        $createTaxClassMethodReflection->setAccessible(true);

        $resultId = $createTaxClassMethodReflection->invokeArgs(
            $this->_taxClassProcessor,
            ['Test tax class', $productTypeModel]
        );
        $this->assertEquals(self::TAX_CLASS_ID, $resultId);
    }
}
