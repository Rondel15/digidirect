<?php

namespace Digidirect\CheckoutFields\Test\Unit\Helper\Xml\Fields;

use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Digidirect\CheckoutFields\Helper\Config as ConfigHelper;
use Digidirect\CheckoutFields\Model\Config\Data as FieldsConfig;

/**
 * Class ParserTest
 *
 * @package Digidirect\CheckoutFields\Test\Unit\Helper\Xml\Fields
 */
class ParserTest extends \Digidirect\CheckoutFields\Test\Unit\TestAbstract
{
    /**
     * @var Parser
     */
    protected $_parserHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldsConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configHelperMock;

    /**
     * Setup objects
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->_configHelperMock = $this->getMockObjectWithoutConstructor(
            ConfigHelper::class,
            ['getActiveCheckoutFields', 'isEnabled']
        );

        $this->_fieldsConfigMock = $this->getMockObjectWithoutConstructor(
            FieldsConfig::class,
            ['getFields']
        );

        $this->_parserHelper = $this->_objectManager->getObject(
            Parser::class,
            [
                'fieldsConfig' => $this->_fieldsConfigMock,
                'config' => $this->_configHelperMock
            ]
        );
    }

    /**
     * Test get fields
     *
     * @return void
     * @dataProvider getFields
     */
    public function testGetFields($readFields, $expected)
    {
        $this->_configHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $this->_configHelperMock->expects($this->any())
            ->method('getActiveCheckoutFields')
            ->willReturn(
                [
                    'delivery_text' => [
                        'active' => true
                    ]
                ]
            );

        $this->_fieldsConfigMock->expects($this->any())
            ->method('getFields')
            ->willReturn($readFields);

        $this->assertEquals($expected, $this->_parserHelper->getFields());
    }

    /**
     * Provide data
     *
     * @return array
     */
    public function getFields()
    {
        return [
            [
                [
                    'fields' => [
                        'delivery_text' => [],
                        'second_field' => []
                    ]
                ],
                ['delivery_text' => []]
            ],
            [
                [
                    'fields15' => [
                        'delivery_text' => [],
                        'second_field' => []
                    ]
                ],
                []
            ]
        ];
    }
}
