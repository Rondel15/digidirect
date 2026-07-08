<?php
// @codingStandardsIgnoreFile
namespace Digidirect\AI\Test\Unit\Model\Lib\Entity\Import\Customer\Extended;

use Digidirect\Utilities\Test\Unit\Library;
use Digidirect\AI\Model\Lib\Entity\Import\Customer\Extended\CustomerImport;

/**
 * Class CustomerImportTest
 *
 * Unit tests for custom protected methods
 */
class CustomerImportTest extends Library
{
    /**
     * @var CustomerImport
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

        $extendedReflection = $this->getReflectionClass(CustomerImport::class);

        $this->customerImportExtended = $extendedReflection->newInstanceWithoutConstructor();
    }

    /**
     * @dataProvider provideData
     * @param array $data
     * @param array $expected
     * @return void
     */
    public function testPrepareAttributesToSave(array $data, array $expected)
    {
        $classReflection = $this->getReflectionClass(CustomerImport::class);
        $method = $this->setAccessibleProtectedMethod($classReflection, 'prepareAttributesToSave');

        $excludedCustomerIdsProperty = $this->setAccessibleProtectedProperty(
            $classReflection,
            'excludedCustomerIds'
        );

        $excludedCustomerIdsProperty->setValue($this->customerImportExtended, $data['excluded_customer_ids']);

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
                    'excluded_customer_ids' => [25],
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
                    'excluded_customer_ids' => [],
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
                    'excluded_customer_ids' => [],
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
        $reflectionClass = $this->getReflectionClass(CustomerImport::class);
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
                        ],
                    ],
                    'action' => 'create',

                ],
                [
                    [
                        'group_id' => '1',
                        'store_id' => '1',
                        'created_at' => '2017-05-02 07:13:15',
                        'updated_at' => '2017-05-02 07:13:15',
                        'entity_id' => '25',
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',
                        'prefix' => 'UPDATEDprefix',
                        'taxvat' => 'UPDATEDtaxvat0',
                        'website_id' => '1',
                        'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
                        ],
                    ],
                    'action' => 'create',
                ],
                [
                    [
                        'group_id' => '1',
                        'store_id' => '1',
                        'created_at' => '2017-05-02 07:13:15',
                        'updated_at' => '2017-05-02 07:13:15',
                        'entity_id' => '25',
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',
                        'prefix' => 'UPDATEDprefix',
                        'taxvat' => 'UPDATEDtaxvat0',
                        'website_id' => '1',
                        'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
                        ],
                    ],
                    'action' => 'create',
                ],
                [
                    [
                        'group_id' => '1',
                        'store_id' => '1',
                        'created_at' => '2017-05-02 07:13:15',
                        'updated_at' => '2017-05-02 07:13:15',
                        'entity_id' => '25',
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',
                        'prefix' => 'UPDATEDprefix',
                        'taxvat' => 'UPDATEDtaxvat0',
                        'website_id' => '1',
                        'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
                        ],
                    ],
                    'action' => 'create',
                ],
                [
                    [
                        'group_id' => '1',
                        'store_id' => '1',
                        'created_at' => '2017-05-02 07:13:15',
                        'updated_at' => '2017-05-02 07:13:15',
                        'entity_id' => '25',
                        'lastname' => 'UPDATEDlast nae0',
                        'firstname' => 'UPDATEDfirst name0',
                        'middlename' => 'UPDATEDsecond name0',
                        'email' => 'customer_integration+0@digidirect.com',
                        'prefix' => 'UPDATEDprefix',
                        'taxvat' => 'UPDATEDtaxvat0',
                        'website_id' => '1',
                        'is_active' => 1,
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
                            'group_id' => '1',
                            'store_id' => '1',
                            'created_at' => '2017-05-02 07:13:15',
                            'updated_at' => '2017-05-02 07:13:15',
                            'entity_id' => '25',
                            'lastname' => 'UPDATEDlast nae0',
                            'firstname' => 'UPDATEDfirst name0',
                            'middlename' => 'UPDATEDsecond name0',
                            'email' => 'customer_integration+0@digidirect.com',
                            'prefix' => 'UPDATEDprefix',
                            'taxvat' => 'UPDATEDtaxvat0',
                            'website_id' => '1',
                            'is_active' => 1,
                        ],
                    ],
                    'action' => 'update',
                ],
                [
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideDataForTestPrepareDataForUpdate
     * @param array $data
     * @param array $expected
     * @return void
     */
    public function testConvertMultiselect(array $data, array $expected)
    {
        $reflection = $this->getReflectionClass(CustomerImport::class);

        $multipleSelectProperty = $this->setAccessibleProtectedProperty(
            $reflection,
            'multipleSelectAttributes'
        );

        $multipleSelectProperty->setValue($this->customerImportExtended, [
            'posopompkunsy' =>
                [
                    'id' => '209',
                    'code' => 'posopompkunsy',
                    'table' => 'customer_entity_varchar',
                    'is_required' => '0',
                    'is_static' => false,
                    'rules' => [],

                    'type' => 'multiselect',
                    'options' =>
                        [
                            'pos1' => '225',
                            'pos2' => '226',
                            'pos3' => '227',
                            'pos4' => '228',
                            'pos5' => '229',
                            'pos6' => '230',
                            'pos7' => '231',
                            'pos8' => '232',
                            'pos0' => '233',
                        ],
                ],
        ]);

        $attrCode = $data['attribute_code'];
        $attributeValue = $data['attribute_value'];
        $rowData = $data['row_data'];
        $method = $this->setAccessibleProtectedMethod($reflection, 'convertMultiselect');
        // @codingStandardsIgnoreStart
        $method->invokeArgs($this->customerImportExtended, [$attrCode, $attributeValue, &$rowData]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals($expected, $rowData);
    }

    /**
     * @return array
     */
    public function provideDataForTestPrepareDataForUpdate()
    {
        return [
            [
                [
                    'attribute_code' => 'posopompkunsy',
                    'attribute_value' => 'pos1,238',
                    'row_data' => [
                        'posopompkunsy' => 'pos1,238',
                    ],
                ],
                ['posopompkunsy' => '225'],
            ],
            [
                [
                    'attribute_code' => 'posopompkunsy',
                    'attribute_value' => '228,238',
                    'row_data' => [
                        'posopompkunsy' => '228,238',
                    ],
                ],
                ['posopompkunsy' => '228',],
            ],
        ];
    }

    /**
     * @param [] $data
     * @param bool $expected
     * @dataProvider provideDataForValidate
     * @return void
     */
    public function testIsAttributeValid($data, $expected)
    {
        $attributeCode = $data['attribute_code'];
        $rowData = $data['row_data'];
        $attributeParams = $data['attribute_params'];
        $rowNumber = $data['row_number'];

        $this->assertEquals(
            $expected,
            $this->customerImportExtended->isAttributeValid(
                $attributeCode,
                $attributeParams,
                $rowData,
                $rowNumber
            )
        );
    }

    /**
     * @return array
     */
    public function provideDataForValidate()
    {
        return [
            [

                $this->getDataForValidate(
                    'test',
                    CustomerImport::MULTISELECT_ATTRIBUTE,
                    1,
                    [
                        'po1',
                        'pos2',
                    ]
                ),

                false,
            ],
            [
                $this->getDataForValidate(
                    'test',
                    CustomerImport::MULTISELECT_ATTRIBUTE,
                    1,
                    [
                        'po1',
                        'pos2',
                    ]
                ),
                false,
            ],
            [
                $this->getDataForValidate(
                    'test',
                    CustomerImport::MULTISELECT_ATTRIBUTE,
                    1,
                    [
                        'pos1',
                        '238',
                    ]
                ),
                true,
            ],
            [
                $this->getDataForValidate(
                    'test',
                    CustomerImport::MULTISELECT_ATTRIBUTE,
                    1,
                    [
                        'po1',
                        '238',
                    ]
                ),
                true,
            ],
        ];
    }

    /**
     * @param string $attributeCode
     * @param string $attributeType
     * @param int $rowNumber
     * @param [] $options
     * @return array
     */
    protected function getDataForValidate($attributeCode, $attributeType, $rowNumber, $options)
    {
        return [
            'attribute_code' => $attributeCode,
            'attribute_params' => [
                'type' => $attributeType,
                'options' => $options,
            ],
            'row_number' => $rowNumber,
            'row_data' => [
                'test' => 'pos1,238',
            ],

        ];
    }

    /**
     * @dataProvider provideDataIsMultipleSelect
     * @param [] $data
     * @param bool $expected
     */
    public function testIsMultipleSelect($data, $expected)
    {
        $class = $this->getReflectionClass(CustomerImport::class);
        $property = $this->setAccessibleProtectedProperty($class, 'multipleSelectAttributes');
        $property->setValue($this->customerImportExtended, $data['config']);
        $method = $this->setAccessibleProtectedMethod($class, 'isMultipleSelect');
        $this->assertEquals($expected, $method->invokeArgs($this->customerImportExtended, [$data['attribute_code']]));
    }

    /**
     * @return array
     */
    public function provideDataIsMultipleSelect()
    {
        return [
            [
                [
                    'config' =>
                        ['m_s' => ['some_config']],
                    'attribute_code' => 'test',
                ],
                false,
            ],

            [
                [
                    'config' =>
                        ['m_s' => ['some_config']],
                    'attribute_code' => 'm_s',
                ],
                true,
            ],
        ];
    }
}
