<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Helper;

class UrlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Url helper mock
     * @var \Digidirect\LayeredNavigation\Helper\UrlParser
     */
    protected $_urlParser;

    /**
     * Seo helper (data) mock
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_seoHelper;

    /**
     * Set up required common objects
     * @return void
     */
    public function setUp()
    {
        $reflector = new \ReflectionClass('\Digidirect\LayeredNavigation\Helper\UrlParser');
        $this->_urlParser = $reflector->newInstanceWithoutConstructor();

        $this->_seoHelper = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['getOptionsSeoData'])
            ->getMock();

        $property = $reflector->getProperty('seoHelper');
        $property->setAccessible(true);
        $property->setValue($this->_urlParser, $this->_seoHelper);
    }

    /**
     * Test case for parseSeoPart
     * @return void
     */
    public function testParseSeoPart()
    {
        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn([
                10 => [
                    'alias' => 'red',
                    'attribute_code' => 'color',
                ],
                '20-30' => [
                    'alias' => '20-30',
                    'attribute_code' => 'price',
                ],
                12 => [
                    'alias' => 'blue',
                    'attribute_code' => 'color',
                ],
                13 => [
                    'alias' => 'xs',
                    'attribute_code' => 'size',
                ],
                14 => [
                    'alias' => 'm',
                    'attribute_code' => 'size',
                ],
            ]);

        $expectedResult = [
            'size' => '13',
            'price' => '20-30',
            'color' => '10,12'
        ];

        $this->assertEquals($expectedResult, $this->_urlParser->parseSeoPart('size/xs/color/red,blue/price/20-30'));
    }

    /**
     * Test case for parseSeoPart
     * @return void
     */
    public function testParseSeoPartWithoutSeoAttributes()
    {
        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn([]);

        $this->assertEquals(false, $this->_urlParser->parseSeoPart('blue-red-20-30-xs'));
    }

    /**
     * Test case for parseSeoPart
     * @return void
     */
    public function testParseSeoPartWrongSeoUrl()
    {
        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn([
                10 => [
                    'alias' => 'red',
                    'attribute_code' => 'color',
                ],
                '20-30' => [
                    'alias' => '20-30',
                    'attribute_code' => 'price',
                ],
            ]);

        $this->assertEquals(false, $this->_urlParser->parseSeoPart('blue-xs'));
    }
}
