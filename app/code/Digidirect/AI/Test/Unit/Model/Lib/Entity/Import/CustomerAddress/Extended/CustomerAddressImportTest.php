<?php
// @codingStandardsIgnoreFile
namespace Digidirect\AI\Test\Unit\Model\Lib\Entity\Import\CustomerAddress\Extended;

use Digidirect\Utilities\Test\Unit\Library;
use Digidirect\AI\Model\Lib\Entity\Import\CustomerAddress\Extended\CustomerAddressImport;

/**
 * Class CustomerAddressImportTest
 *
 * @package Digidirect\AI\Test\Unit\Model\Lib\Entity\Import\CustomerAddress\Extended
 */
class CustomerAddressImportTest extends Library
{
    /**
     * @var CustomerAddressImport
     */
    protected $customerImportExtended;

    /**
     * Setup objects
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $extendedReflection = $this->getReflectionClass(CustomerAddressImport::class);

        $this->customerImportExtended = $extendedReflection->newInstanceWithoutConstructor();
    }

    /**
     * @dataProvider provideData
     * @param array $data
     * @param array $expected
     * @return void
     */
    public function testPrepareAttributes(array $data, array $expected)
    {
        $classReflection = $this->getReflectionClass(CustomerAddressImport::class);
        $method = $this->setAccessibleProtectedMethod($classReflection, 'prepareAttributes');

        $excludedCustomerIdsProperty = $this->setAccessibleProtectedProperty(
            $classReflection,
            'excludedAddressIds'
        );

        $excludedCustomerIdsProperty->setValue($this->customerImportExtended, $data['excluded_address_ids']);

        $result = $method->invokeArgs(
            $this->customerImportExtended,
            [$data['attributes_to_save']]
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function provideData()
    {
        return [
            [
                [
                    'attributes_to_save' =>
                        [
                            'customer_entity_int' => [
                                25 => [
                                    227 => 'some_value_int',
                                ],
                                1 => [
                                    227 => 'some_value_int',
                                ],
                            ],
                            'customer_entity_varchar' => [
                                25 => [
                                    227 => 'some_value_varchar',
                                ],
                                1 => [
                                    227 => 'some_value_varchar',
                                ],
                            ],
                        ],
                    'excluded_address_ids' => [25],
                ],
                [
                    'customer_entity_int' => [
                        1 => [
                            227 => 'some_value_int',
                        ],
                    ],
                    'customer_entity_varchar' => [
                        1 => [
                            227 => 'some_value_varchar',
                        ],
                    ],
                ],

            ],
            [
                [
                    'attributes_to_save' => [],
                    'excluded_address_ids' => [],
                ],
                [],
            ],
            [
                [
                    'attributes_to_save' =>
                        [
                            'customer_entity_int' => [
                                25 => [
                                    227 => 'some_value_int',
                                ],
                                1 => [
                                    227 => 'some_value_int',
                                ],
                            ],
                            'customer_entity_varchar' => [
                                25 => [
                                    227 => 'some_value_varchar',
                                ],
                                1 => [
                                    227 => 'some_value_varchar',
                                ],
                            ],
                        ],
                    'excluded_address_ids' => [],
                ],

                [
                    'customer_entity_int' => [
                        25 => [
                            227 => 'some_value_int',
                        ],
                        1 => [
                            227 => 'some_value_int',
                        ],
                    ],
                    'customer_entity_varchar' => [
                        25 => [
                            227 => 'some_value_varchar',
                        ],
                        1 => [
                            227 => 'some_value_varchar',
                        ],
                    ],
                ],

            ],
        ];
    }

    /**
     * @dataProvider provideDataForGetDataForAction
     * @param array $data
     * @param array $expected
     * @return  void
     */
    public function testGetDataForAction(array $data, array $expected)
    {
        $reflectionClass = $this->getReflectionClass(CustomerAddressImport::class);
        $parametersProperty = $this->setAccessibleProtectedProperty($reflectionClass, '_parameters');
        $availableBehavioursProperty = $this->setAccessibleProtectedProperty($reflectionClass, '_availableBehaviors');
        $availableBehavioursProperty->setValue(
            $this->customerImportExtended,
            [
                [
                    \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                    \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                ],
            ]
        );

        $configurationProperty = $this->setAccessibleProtectedProperty($reflectionClass, 'configuration');
        $configurationProperty->setValue(
            $this->customerImportExtended,
            [
                'actions' => [
                    'delete' => [
                        'empty' => [
                            \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                        ],
                    ],
                    'create' => [
                        'empty' => [
                            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                        ],
                    ],
                    'update' => [
                        'empty' => [
                            \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                        ],
                    ],
                ],
            ]
        );

        $parametersProperty->setValue($this->customerImportExtended, [
            $data
            ['parameters'],
        ]);

        $method = $this->setAccessibleProtectedMethod($reflectionClass, 'getDataForAction');
        $result = $method->invokeArgs(
            $this->customerImportExtended,
            [
                $data['action'],
                $data['customer_data'],
            ]
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function provideDataForGetDataForAction()
    {
        return [
            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                    ],
                    'customer_data' => [
                        [
                            'email' => 'customer_integration+0@digidirect.com',
                        ],
                    ],
                    'action' => 'delete',

                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                    ],
                    'customer_data' => [
                        [

                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'update',

                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
                    ],
                    'customer_data' => [
                        [
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'create',

                ],
                [
                    [
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',
                    ],
                ],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                    ],
                    'customer_data' => [
                        [

                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'create',
                ],
                [
                    [
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',

                    ],
                ],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                    ],
                    'customer_data' => [
                        [
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'delete',
                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                    ],
                    'customer_data' => [
                        [
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                        ],
                    ],
                    'action' => 'update',
                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                    ],
                    'customer_data' => [
                        [
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'update',
                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                    ],
                    'customer_data' => [
                        [
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'delete',
                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                    ],
                    'customer_data' => [
                        [
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'create',
                ],
                [
                    [
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',

                    ],
                ],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                    ],
                    'customer_data' => [
                        [

                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'delete',
                ],
                [],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                    ],
                    'customer_data' => [
                        [

                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'create',
                ],
                [
                    [

                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',

                    ],
                ],
            ],

            [
                [
                    'parameters' => [
                        'behaviour' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
                    ],
                    'customer_data' => [
                        [

                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',

                        ],
                    ],
                    'action' => 'update',
                ],
                [
                ],
            ],
        ];
    }
}
