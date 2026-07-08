<?php
// @codingStandardsIgnoreFile
namespace Digidirect\AI\Test\Unit\Model\Lib\Import\Product\Entity;

use Digidirect\AI\Model\Lib\Import\Product\Service\DataSource;

class DataSourceTest extends \PHPUnit_Framework_TestCase
{
    protected static $_dataSourceModel;

    public static function setUpBeforeClass()
    {
        self::$_dataSourceModel = new DataSource;
        self::$_dataSourceModel->setBunch(self::skuDataProvider());
    }

    public static function tearDownAfterClass()
    {
        self::$_dataSourceModel = null;
    }

    public static function skuDataProvider()
    {
        return [
            'sku1' => ['test_sku_1'],
            'sku2' => ['test_sku_2'],
            'sku3' => ['test_sku_3'],
        ];
    }

    /**
     * Testing iterator, iterate through all banches
     * @dataProvider skuDataProvider
     */
    public function testGetNextBunch($sku)
    {
        $actual = self::$_dataSourceModel->getNextBunch();
        $this->assertEquals($sku, $actual[0]);
    }
}
