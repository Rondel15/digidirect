<?php
// @codingStandardsIgnoreFile
namespace Digidirect\AI\Test\Unit\Model\Lib\Mapping;

use Digidirect\AI\Model\Lib\Mapping\Mapper;
use Digidirect\Utilities\Test\Unit\Library;

class MapperTest extends Library
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var array
     */
    protected $keysMap = [
        'status' => 'state',
        'items' => 'products',
        'items.*.sku' => 'code',
        'billing_address' => 'invoice_address',
        'billing_address.postcode' => 'zipcode'
    ];

    /**
     * @var array
     */
    protected $valuesMap = [
        'status.completed' => 'done',
        'items.*.total' => [
            'class' => '\Digidirect\AI\Test\Unit\Model\Lib\Mapping\MapperTest',
            'method' => 'calculateItemTotal'
        ]
    ];

    /**
     * @var array
     */
    protected $callbacks = [
        'addToken' => [
            'class' => '\Digidirect\AI\Test\Unit\Model\Lib\Mapping\MapperTest',
            'method' => 'addOrderToken'
        ]
    ];

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->mapper = new Mapper(
            $this->keysMap,
            $this->valuesMap,
            $this->callbacks
        );
    }

    /**
     * Test data mapping
     *
     * @dataProvider provideData
     * @param string $data
     * @param string $mappedData
     */
    public function testMapData($data, $mappedData)
    {
        $this->assertEquals($mappedData, $this->mapper->map($data));
    }

    /**
     * Provide data
     *
     * @return []
     */
    public function provideData()
    {
        return [
            [
                [
                    'status' => 'completed',
                    'billing_address' => [
                        'postcode' => '222310'
                    ],
                    'items' => [
                        [
                            'sku' => 'sku1',
                            'price' => 10,
                            'qty' => 5,
                            'total' => 0,
                        ],
                        [
                            'sku' => 'sku2',
                            'price' => 8,
                            'qty' => 3,
                            'total' => 0,
                        ],
                    ]
                ],
                [
                    'state' => 'done',
                    'invoice_address' => [
                        'zipcode' => '222310'
                    ],
                    'products' => [
                        [
                            'code' => 'sku1',
                            'price' => 10,
                            'qty' => 5,
                            'total' => 50,
                        ],
                        [
                            'code' => 'sku2',
                            'price' => 8,
                            'qty' => 3,
                            'total' => 24,
                        ],
                    ],
                    'token' => 'someString',
                ],
            ],
        ];
    }

    /**
     * @param mixed $currentValue
     * @param array $condition
     * @param array $map
     * @param array $item
     * @return float
     */
    public static function calculateItemTotal($currentValue, $condition, $map, $item)
    {
        return $item['price'] * $item['qty'];
    }

    /**
     * @param array $data
     * @return array
     */
    public static function addOrderToken($data)
    {
        $data['token'] = 'someString';
        return $data;
    }
}
