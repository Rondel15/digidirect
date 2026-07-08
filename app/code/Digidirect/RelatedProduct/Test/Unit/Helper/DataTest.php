<?php
namespace Digidirect\RelatedProduct\Test\Unit\Helper;

use Digidirect\RelatedProduct\Test\Unit\RelatedProductTestUnitTrait;
use Digidirect\RelatedProduct\Helper\Data as Helper;

/**
 * Class DataTest
 * @package Digidirect\RelatedProduct\Test\Unit\Helper
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    use RelatedProductTestUnitTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * Setup objects
     * @return void
     */
    public function setUp()
    {
        $this->_helperMock = $this->getMockObjectWithoutConstructor(
            Helper::class,
            null
        );
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider provideOptionsArray
     */
    public function testGetValuesFromOptions($data, $expected)
    {
        $result = $this->_helperMock->getValuesFromOptions([[$data => $expected]]);

        $this->assertEquals([$expected], $result);
    }

    /**
     * @return array
     */
    public function provideOptionsArray()
    {
        return [
            ['label', ''],
            ['label', 'Some Label'],
        ];
    }
}
