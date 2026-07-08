<?php

namespace Digidirect\CheckoutFields\Test\Unit\Plugin\Checkout\Model;

use Digidirect\CheckoutFields\Plugin\Checkout\Model\LayoutProcessor;
use Digidirect\CheckoutFields\Helper\Xml\Fields\Parser;
use Magento\Checkout\Block\Checkout\LayoutProcessor as MagentoLayoutProcessor;
use Digidirect\CheckoutFields\Model\Component\Type\Factory;

/**
 * Class LayoutProcessorTest
 * @package Digidirect
 */
class LayoutProcessorTest extends \Digidirect\CheckoutFields\Test\Unit\TestAbstract
{
    /**
     * @var LayoutProcessor
     */
    protected $layoutProcessorPlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $parserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $magentoLayoutProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactoryMock;

    /**
     * Setup objects
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->parserMock = $this->getMockObjectWithoutConstructor(
            Parser::class,
            ['getFields']
        );

        $this->typeFactoryMock = $this->getMockObjectWithoutConstructor(Factory::class, ['create']);
        $this->magentoLayoutProcessorMock = $this->getMockObjectWithoutConstructor(MagentoLayoutProcessor::class);

        $this->layoutProcessorPlugin = $this->_objectManager->getObject(
            LayoutProcessor::class,
            ['parser' => $this->parserMock, 'factory' => $this->typeFactoryMock]
        );
    }

    /**
     * Test after process method - adding fields
     * @return void
     */
    public function testAfterProcess()
    {
        $selectMock = $this->getMockObjectWithoutConstructor(
            '\Digidirect\CheckoutFields\Model\Component\Type\Select',
            [
                'getComponent',
                'getElementTemplate'
            ]
        );

        $selectMock->expects($this->any())
            ->method('getComponent')
            ->willReturn('path/to/Component');

        $selectMock->expects($this->any())
            ->method('getElementTemplate')
            ->willReturn('path/to/element/Template');

        $jsLayout = $this->_getJsLayout('shipping-step', 'shippingAddress', 'address-list-additional-addresses');
        $this->typeFactoryMock->expects($this->any())
            ->method('create')
            ->with('\Digidirect\CheckoutFields\Model\Component\Type\Select')
            ->willReturn($selectMock);

        $this->parserMock->expects($this->any())
            ->method('getFields')
            ->willReturn($this->_getReturnValue());

        $this->assertEquals(
            $this->_getExpectedResult(),
            $this->layoutProcessorPlugin->afterProcess($this->magentoLayoutProcessorMock, $jsLayout)
        );
    }

    /**
     * @return array
     */
    protected function _getExpectedResult()
    {
        $array = [
            'components' => [
                'checkout' => [
                    'children' => [
                        'steps' => [
                            'children' => [
                                'shipping-step' => [
                                    'children' => [
                                        'shippingAddress' => [
                                            'children' => [
                                                'address-list-additional-addresses' => [
                                                    'children' => [
                                                        'delivery_text' => [
                                                            'component' => 'path/to/Component',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'path/to/element/Template',
                                                                'id' => 'delivery_text',
                                                            ],
                                                            'dataScope' => 'shippingAddress.delivery_text',
                                                            'label' => 'Frontend Name',
                                                            'provider' => 'checkoutProvider',
                                                            'visible' => true,
                                                            'validation' => [
                                                                'required-entry' => 1
                                                            ],
                                                            'sortOrder' => 100000,
                                                            'id' => 'delivery_text',
                                                            'options' => [
                                                                0 => [
                                                                    'label' => 'Option1',
                                                                    'value' => 1
                                                                ],
                                                                1 => [
                                                                    'label' => 'Option48',
                                                                    'value' => 16
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return array_merge_recursive(
            $array,
            $this->_getJsLayout('shipping-step', 'shippingAddress', 'address-list-additional-addresses')
        );
    }

    /**
     * Get jsLayout array
     *
     * @param string $step
     * @param string $scope
     * @param string $fieldset
     * @return []
     */
    protected function _getJsLayout($step, $scope, $fieldset)
    {
        return [
            'components' => [
                'checkout' => [
                    'children' => [
                        'steps' => [
                            'children' => [
                                $step => [
                                    'children' => [
                                        $scope => [
                                            'children' => [
                                                $fieldset => [
                                                    'children' => [

                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function _getReturnValue()
    {
        return [
            'delivery_text' => [
                'frontend_name' => 'Frontend Name',
                'frontend_input' => 'select',
                'sort_order' => 100000,
                'validation' => [
                    'rule' => [
                        0 => [
                            '_value' => 1,
                            '_attribute' => [
                                'name' => 'required-entry'
                            ]
                        ]
                    ]
                ],
                'area' => [
                    'checkout_step' => 'shipping-step',
                    'custom_scope' => 'shippingAddress',
                    'fieldset' => 'address-list-additional-addresses',
                ],
                'options' => [
                    'option' => [
                        0 => [
                            '_attribute' => [
                                'value' => 1,
                                'label' => 'Option1'
                            ]
                        ],
                        1 => [
                            '_attribute' => [
                                'value' => 16,
                                'label' => 'Option48'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
