<?php
namespace Digidirect\Utilities\Test\Unit\Plugin\Magento\Widget\Model\ResourceModel\Widget;

use Digidirect\Utilities\Test\Unit\Library;
use Digidirect\Utilities\Plugin\Magento\Widget\Model\ResourceModel\Widget\Instance;

/**
 * Class InstanceTest
 */
class InstanceTest extends Library
{
    /**
     * @var Instance
     */
    protected $pluginOriginal;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $widgetInstanceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    /**
     * Setup objects
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->widgetInstanceMock = $this->getMockObjectWithoutConstructor(
            \Magento\Widget\Model\Widget\Instance::class,
            ['save']
        );

        $this->resourceModelMock = $this->getMockObjectWithoutConstructor(
            \Magento\Widget\Model\ResourceModel\Widget\Instance::class,
            ['save']
        );

        $this->pluginOriginal = $this->objectManager->getObject(
            Instance::class,
            [
                'handlesToCorrect' => [
                    'cms_index_noroute' => 'cms_noroute_index',
                ],
            ]
        );
    }

    /**
     * @dataProvider provideData
     * @param [] $data
     * @param [] $expected
     */
    public function testAroundSave($data, $expected)
    {
        $reflectionInstance = $this->getReflectionClass(\Magento\Widget\Model\Widget\Instance::class);
        $dataProperty = $this->setAccessibleProtectedProperty($reflectionInstance, '_data');
        $dataProperty->setValue($this->widgetInstanceMock, $data);

        $resource = $this->resourceModelMock;
        $instance = $this->widgetInstanceMock;

        $closure = function () use ($resource, $instance) {
            return $resource->save($instance);
        };
        $this->pluginOriginal->aroundSave($this->resourceModelMock, $closure, $this->widgetInstanceMock);

        $this->assertEquals(
            $expected,
            $this->widgetInstanceMock->getData()
        );
    }

    /**
     * @return array
     */
    public function provideData()
    {
        $data1 = [
            'page_groups' => [
                0 => [
                    'page_group' => 'pages',
                    'pages' => [
                        'page_id' => 28,
                        'layout_handle' => 'cms_index_noroute',
                    ],
                ],
            ],
        ];

        $expected1 = [
            'page_groups' => [
                0 => [
                    'page_group' => 'pages',
                    'pages' => [
                        'page_id' => 28,
                        'layout_handle' => 'cms_noroute_index',
                    ],
                ],
            ],
        ];

        $data2 = [
            'page_groups' => [
                0 => [
                    'page_group' => 'vepe',
                    'pages' => [
                        'page_id' => 28,
                        'layout_handle' => 'cms_index_noroute',
                    ],
                ],
            ],
        ];

        $expected2 = [
            'page_groups' => [
                0 => [
                    'page_group' => 'vepe',
                    'pages' => [
                        'page_id' => 28,
                        'layout_handle' => 'cms_index_noroute',
                    ],
                ],
            ],
        ];

        $data3 = [
            'page_groups' => [
                0 => [
                    'page_group' => 'pages',
                    'pages' => [
                        'page_id' => 28,
                        'layout_handle' => 'cms_index_noroute_vepe',
                    ],
                ],
            ],
        ];

        $expected3 = [
            'page_groups' => [
                0 => [
                    'page_group' => 'pages',
                    'pages' => [
                        'page_id' => 28,
                        'layout_handle' => 'cms_index_noroute_vepe',
                    ],
                ],
            ],
        ];

        return [
            [$data1, $expected1],
            [$data2, $expected2],
            [$data3, $expected3],
        ];
    }
}
