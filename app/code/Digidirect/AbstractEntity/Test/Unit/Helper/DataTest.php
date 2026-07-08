<?php

namespace Digidirect\AbstractEntity\Test\Unit\Helper;

use Digidirect\AbstractEntity\Helper\Data;

class DataTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @return void
     */
    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManager->getObject(Data::class);
    }

    /**
     * @dataProvider dataTestGetIndexTablePostfix
     * @param string $attributeSetName
     * @param string $expectedResult
     */
    public function testGetIndexTablePostfix($attributeSetName, $expectedResult)
    {
        echo $this->helper->getIndexTablePostfix($attributeSetName);
        $this->assertEquals($expectedResult, $this->helper->getIndexTablePostfix($attributeSetName));
    }

    /**
     * @return array
     */
    public function dataTestGetIndexTablePostfix()
    {
        return [
            ['Test', 'test'],
            ['Test 1', 'test_1'],
            ['This string is longer than thirty characters.', 'this_string_is_longer_than_thi'],
            ['Test $# 1', 'test__1'],
            ['12Test', '12test'],
            ['!Test', 'test'],
        ];
    }
}
