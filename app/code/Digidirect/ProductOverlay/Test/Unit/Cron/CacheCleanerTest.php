<?php
namespace Digidirect\ProductOverlay\Test\Unit\Cron;

use Digidirect\ProductOverlay\Test\Unit;
use Digidirect\ProductOverlay\Cron\CacheCleaner;

/**
 * Class CacheCleanerTest
 * @package Digidirect\ProductOverlay\Test\Unit\Cron
 */
class CacheCleanerTest extends Unit\Library
{
    /**
     * @var CacheCleaner
     */
    protected $cacheCleanerOriginal;

    /**
     * Setup objects
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->cacheCleanerOriginal = $this->objectManager->getObject(CacheCleaner::class);
    }

    /**
     * Test method: getProductIdsToCleanCache
     * @param [] $data
     * @param bool $expected
     * @return void
     * @dataProvider provideRules
     */
    public function testGetProductIdsToCleanCache($data, $expected)
    {
        $reflection = $this->getReflectionClass(CacheCleaner::class);
        $method = $this->setAccessibleMethod($reflection, 'getProductIdsToCleanCache');
        $productIdsToClean = $method->invoke(
            $this->cacheCleanerOriginal,
            $data['old_overlays'],
            $data['current_overlays']
        );

        $this->assertEquals($expected, $productIdsToClean);
    }

    /**
     * @return array
     */
    public function provideRules()
    {
        return [
            [
                [
                    'old_overlays' => [
                        2 => [
                            1,
                            2,
                            3,
                        ]
                    ],
                    'current_overlays' => [
                        2 => [
                            1,
                            2,
                            3,
                        ]
                    ]
                ],
                []
            ],
            [
                [
                    'old_overlays' => [
                        2 => [
                            1,
                            2,
                        ]
                    ],
                    'current_overlays' => [
                        3 => [
                            1,
                            2,
                        ]
                    ]
                ],
                [1, 2]
            ],
            [
                [
                    'old_overlays' => [
                        3 => [
                            1,
                            2,
                        ]
                    ],
                    'current_overlays' => [
                        3 => [
                            1,
                            2,
                            3
                        ]
                    ]
                ],
                [3]
            ],
            [
                [
                    'old_overlays' => [
                        3 => [
                            1,
                            2,
                            4,
                        ]
                    ],
                    'current_overlays' => [
                        3 => [
                            1,
                            2,
                        ]
                    ]
                ],
                [4]
            ],
            [
                [
                    'old_overlays' => [
                        3 => [
                            1,
                            2,
                            4,
                        ]
                    ],
                    'current_overlays' => [
                        3 => [
                            1,
                            2,
                            4,
                            5,
                        ]
                    ]
                ],
                [5]
            ],
            [
                [
                    'old_overlays' => [],
                    'current_overlays' => [
                        3 => [
                            1,
                            2,
                        ]
                    ]
                ],
                [1, 2]
            ],
            [
                [
                    'old_overlays' => [
                        4 => [
                            7,
                            8,
                        ]
                    ],
                    'current_overlays' => []
                ],
                [7, 8]
            ],
        ];
    }
}
