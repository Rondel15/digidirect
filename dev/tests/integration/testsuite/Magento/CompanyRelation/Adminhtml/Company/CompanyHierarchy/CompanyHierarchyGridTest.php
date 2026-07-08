<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\CompanyRelation\Adminhtml\Company\CompanyHierarchy;

use Laminas\Http\Headers;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

class CompanyHierarchyGridTest extends AbstractController
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->url = Bootstrap::getObjectManager()->create(UrlInterface::class);
        $this->_objectManager->get(Auth::class)->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $this->acl = $this->_objectManager->get(AclBuilder::class)->getAcl();
        $indexerRegistry = Bootstrap::getObjectManager()->create(IndexerRegistry::class);
        $indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'regular_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_b_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Regular Company',
                CompanyInterface::SUPER_USER_ID => '$regular_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'regular_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company A',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company B',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_b'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_a.id$'],
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_b.id$']
                ],
            ],
        ),
    ]
    /**
     * @dataProvider totalItemsCountDataProvider
     */
    public function testTotalItemsCount($fixture, $expectedItems)
    {
        $companyFixture = DataFixtureStorageManager::getStorage()->get($fixture);

        $entityFilter = $fixture ? [
            'entity_id' => $companyFixture->getData(CompanyInterface::COMPANY_ID)
        ] : [];
        $params = array_merge($entityFilter, [
            'namespace' => 'company_hierarchy_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ]);

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('totalRecords', $responseBody);
        $this->assertArrayHasKey('items', $responseBody);
        $resultCompanyNames = array_column($responseBody['items'], 'company_name');
        $this->assertEqualsCanonicalizing($expectedItems, $resultCompanyNames);
    }

    public static function totalItemsCountDataProvider(): array
    {
        return [
            'should_be_empty' => ['fixture' => 'regular_company', 'expectedItems' => []],
            'should_be_empty_for_new_company' => ['fixture' => '', 'expectedItems' => []],
            'should_see_itself_and_children' => [
                'fixture' => 'parent_company',
                'expectedItems' => [
                    'Parent Company',
                    'Assigned Company A',
                    'Assigned Company B'
                ]
            ],
            'should_see_parent_and_siblings_a' => [
                'fixture' => 'assigned_company_a',
                'expectedItems' => [
                    'Parent Company',
                    'Assigned Company A',
                    'Assigned Company B'
                ]
            ],
            'should_see_parent_and_siblings_b' => [
                'fixture' => 'assigned_company_b',
                'expectedItems' => [
                    'Parent Company',
                    'Assigned Company A',
                    'Assigned Company B'
                ]
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
    ]
    public function testNonExistingCompany()
    {
        $params = [
            'namespace' => 'company_hierarchy_listing',
            'entity_id' => 100501,
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('totalRecords', $responseBody);
        $this->assertEquals(0, $responseBody['totalRecords']);
        $this->assertCount(0, $responseBody['items']);
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Customer\Test\Fixture\Customer::class,
            [
                CustomerInterface::FIRSTNAME => 'Woody',
                CustomerInterface::LASTNAME => 'Wood',
            ],
            'parent_company_admin'
        ),
        DataFixture(
            \Magento\Customer\Test\Fixture\Customer::class,
            [
                CustomerInterface::FIRSTNAME => 'John',
                CustomerInterface::LASTNAME => 'Doe',
            ],
            'assigned_company_a_admin'
        ),
        DataFixture(
            \Magento\Customer\Test\Fixture\Customer::class,
            [
                CustomerInterface::FIRSTNAME => 'Jane',
                CustomerInterface::LASTNAME => 'Doe',
            ],
            'assigned_company_b_admin'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company A',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company B',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_b'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_a.id$'],
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_b.id$']
                ],
            ],
        ),
    ]
    /**
     * @dataProvider sortItemsDataProvider
     */
    public function testSortItems($sorting, $expected)
    {
        $companyFixture = DataFixtureStorageManager::getStorage()->get('parent_company');

        $params = [
            'namespace' => 'company_hierarchy_listing',
            'entity_id' => $companyFixture->getData(CompanyInterface::COMPANY_ID),
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];
        $params = array_merge($params, $sorting);

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('items', $responseBody);
        $this->assertStringContainsString(
            'Parent Company',
            $responseBody['items'][0][CompanyInterface::NAME],
            'The first row of the grid should be the parent company.'
        );
        $this->assertEquals($expected, $responseBody['items'][1][CompanyInterface::NAME]);
    }

    public static function sortItemsDataProvider(): array
    {
        return [
            'default' => [
                'sorting' => ['sorting[field]' => 'company_name', 'sorting[direction]' => 'asc'],
                'expected' => 'Assigned Company A'
            ],
            'default_reverse' => [
                'sorting' => ['sorting[field]' => 'company_name', 'sorting[direction]' => 'desc'],
                'expected' => 'Assigned Company B'
            ],
            'company_admin' => [
                'sorting' => ['sorting[field]' => 'company_admin', 'sorting[direction]' => 'asc'],
                'expected' => 'Assigned Company B'
            ],
            'company_admin_reverse' => [
                'sorting' => ['sorting[field]' => 'company_admin', 'sorting[direction]' => 'desc'],
                'expected' => 'Assigned Company A'
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_b_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company A',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company B',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_b'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_a.id$'],
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_b.id$']
                ],
            ],
        ),
    ]
    /**
     * @dataProvider paginationDataProvider
     */
    public function testPagination($pagination, $expected)
    {
        $companyFixture = DataFixtureStorageManager::getStorage()->get('parent_company');

        $params = [
            'namespace' => 'company_hierarchy_listing',
            'entity_id' => $companyFixture->getData(CompanyInterface::COMPANY_ID),
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];
        $params = array_merge($params, $pagination);

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('totalRecords', $responseBody);
        $this->assertEquals($expected['totalRecords'], $responseBody['totalRecords']);
        $this->assertEquals($expected['items_count'], count($responseBody['items']));
        $this->assertEquals($expected['first_item'], $responseBody['items'][0][CompanyInterface::NAME]);
    }

    public static function paginationDataProvider()
    {
        return [
            'default' => [
                'pagination' => ['paging[pageSize]' => '20', 'paging[current]' => 1],
                'expected' => [
                    'items_count' => 3,
                    'totalRecords' => 3,
                    'first_item' => 'Parent Company'
                ]
            ],
            'one_per_page_first' => [
                'pagination' => ['paging[pageSize]' => '1', 'paging[current]' => 1],
                'expected' => [
                    'items_count' => 1,
                    'totalRecords' => 3,
                    'first_item' => 'Parent Company'
                ]
            ],
            'one_per_page_second' => [
                'pagination' => ['paging[pageSize]' => '1', 'paging[current]' => 2],
                'expected' => [
                    'items_count' => 1,
                    'totalRecords' => 3,
                    'first_item' => 'Assigned Company A'
                ]
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'child_company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'child_company_b_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Child Company A',
                CompanyInterface::SUPER_USER_ID => '$child_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'child_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Child Company B',
                CompanyInterface::SUPER_USER_ID => '$child_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'child_company_b'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$child_company_a.id$'],
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$child_company_b.id$']
                ],
            ],
        ),
    ]
    /**
     * @dataProvider editActionDataProvider
     */
    public function testEditActions(
        string $viewingCompanyFixtureId,
        bool $isAdminAllowed,
        array $expected
    ): void {
        $companyFixture = DataFixtureStorageManager::getStorage()->get($viewingCompanyFixtureId);

        if (!$isAdminAllowed) {
            $this->acl->deny(
                \Magento\TestFramework\Bootstrap::ADMIN_ROLE_ID,
                'Magento_Company::manage'
            );
        }
        $params =  [
            'entity_id' => $companyFixture->getData(CompanyInterface::COMPANY_ID),
            'namespace' => 'company_hierarchy_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey(
                'mui',
                'index',
                'render'
            ),
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));
        $this->dispatch('backend/mui/index/render?' . http_build_query($params));
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('items', $responseBody);

        // Assert actions based on the isAdminAllowed permission
        foreach ($responseBody['items'] as $item) {
            $currentItemCompanyName = $item[CompanyInterface::NAME];
            $this->assertEquals($expected[$currentItemCompanyName]['isVisible'], isset($item['action']['edit']));
            if ($expected[$currentItemCompanyName]['isVisible']) {
                $this->assertEquals($expected[$currentItemCompanyName]['label'], $item['action']['edit']['label']);
                $expectedHref = 'company/index/edit/id/' . $item['entity_id'];
                $this->assertStringContainsString($expectedHref, $item['action']['edit']['href']);
                $this->assertFalse($item['action']['edit']['hidden']);
            }
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function editActionDataProvider(): array
    {
        return [
            'Parent Company Admin is Allowed' => [
                'viewingCompanyFixtureId' => 'parent_company',
                'isAdminAllowed' => true,
                'expected' => [
                    'Parent Company' => ['isVisible' => false],
                    'Child Company A' => ['isVisible' => true, 'label' => 'Edit'],
                    'Child Company B' => ['isVisible' => true, 'label' => 'Edit']
                ]
            ],
            'Parent Company Admin is Not Allowed' => [
                'viewingCompanyFixtureId' => 'parent_company',
                'isAdminAllowed' => false,
                'expected' => [
                    'Parent Company' => ['isVisible' => false],
                    'Child Company A' => ['isVisible' => true, 'label' => 'View'],
                    'Child Company B' => ['isVisible' => true, 'label' => 'View']
                ]
            ],
            'Child Company A Admin is Allowed' => [
                'viewingCompanyFixtureId' => 'child_company_a',
                'isAdminAllowed' => true,
                'expected' => [
                    'Parent Company' => ['isVisible' => true, 'label' => 'Edit'],
                    'Child Company A' => ['isVisible' => false],
                    'Child Company B' => ['isVisible' => true, 'label' => 'Edit']
                ]
            ],
            'Child Company A Admin is Not Allowed' => [
                'viewingCompanyFixtureId' => 'child_company_a',
                'isAdminAllowed' => false,
                'expected' => [
                    'Parent Company' => ['isVisible' => true, 'label' => 'View'],
                    'Child Company A' => ['isVisible' => false],
                    'Child Company B' => ['isVisible' => true, 'label' => 'View']
                ]
            ],
            'Child Company B Admin is allowed' => [
                'viewingCompanyFixtureId' => 'child_company_b',
                'isAdminAllowed' => true,
                'expected' => [
                    'Parent Company' => ['isVisible' => true, 'label' => 'Edit'],
                    'Child Company A' => ['isVisible' => true, 'label' => 'Edit'],
                    'Child Company B' => ['isVisible' => false]
                ]
            ],
            'Child Company B Admin is not allowed' => [
                'viewingCompanyFixtureId' => 'child_company_b',
                'isAdminAllowed' => false,
                'expected' => [
                    'Parent Company' => ['isVisible' => true, 'label' => 'View'],
                    'Child Company A' => ['isVisible' => true, 'label' => 'View'],
                    'Child Company B' => ['isVisible' => false]
                ]
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'regular_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Regular Company',
                CompanyInterface::SUPER_USER_ID => '$regular_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'regular_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company.id$'],
                ],
            ],
        ),
    ]
    /**
     * @dataProvider unassignActionDataProvider
     */
    public function testUnassignAction(
        bool $isAdminAllowed,
        string $fixture,
        int $totalRecords,
        array $expectedUnassignActionData
    ): void {
        $fixtureStorage = DataFixtureStorageManager::getStorage();
        $companyFixture = $fixtureStorage->get($fixture);

        if (!$isAdminAllowed) {
            $this->acl->deny(
                \Magento\TestFramework\Bootstrap::ADMIN_ROLE_ID,
                'Magento_Company::manage'
            );
        }

        $entityFilter = $fixture ? [
            'entity_id' => $companyFixture->getData(CompanyInterface::COMPANY_ID)
        ] : [];
        $params = array_merge($entityFilter, [
            'namespace' => 'company_hierarchy_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ]);

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('items', $responseBody);
        $this->assertEquals($totalRecords, $responseBody['totalRecords']);
        $this->assertCount(count($expectedUnassignActionData), $responseBody['items']);
        foreach ($responseBody['items'] as $item) {
            $actualUnassignActionData = $item['action']['unassign'] ?? [];
            $this->assertEquals(
                empty($expectedUnassignActionData[$item['company_name']]),
                empty($actualUnassignActionData),
                'The expected and actual result for the visibility of the unassign button are different'
            );
            foreach ($expectedUnassignActionData[$item['company_name']] as $key => $expectedValue) {
                if ($key === 'href') {
                    $this->assertStringContainsString($expectedValue, $actualUnassignActionData[$key]);
                } else {
                    $this->assertEquals($expectedValue, $actualUnassignActionData[$key]);
                }
            }
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function unassignActionDataProvider(): array
    {
        return [
            'regular_company_with_acl' => [
                'isAdminAllowed' => true,
                'fixture' => 'regular_company',
                'totalRecords' => 0,
                'expectedUnassignActionData' => []
            ],
            'new_company_with_acl' => [
                'isAdminAllowed' => true,
                'fixture' => '',
                'totalRecords' => 0,
                'expectedUnassignActionData' => []
            ],
            'parent_company_with_acl' => [
                'isAdminAllowed' => true,
                'fixture' => 'parent_company',
                'totalRecords' => 2,
                'expectedUnassignActionData' => [
                    'Parent Company' => [],
                    'Assigned Company' => [
                        'label' => __('Unassign from parent'),
                        'href' => 'company_relation/index/unassignCompany',
                        'callback'=> [
                            'provider' => 'company_hierarchy_listing.company_hierarchy_listing.company_columns.action',
                            'target' => 'unassignCompany'
                        ]
                    ]
                ]
            ],
            'assigned_company_with_acl' => [
                'isAdminAllowed' => true,
                'fixture' => 'assigned_company',
                'totalRecords' => 2,
                'expectedUnassignActionData' => [
                    'Parent Company' => [],
                    'Assigned Company' => [
                        'label' => __('Unassign from parent'),
                        'href' => 'company_relation/index/unassignCompany',
                        'callback'=> [
                            'provider' => 'company_hierarchy_listing.company_hierarchy_listing.company_columns.action',
                            'target' => 'unassignCompany'
                        ]
                    ]
                ]
            ],
            'regular_company_without_acl' => [
                'isAdminAllowed' => false,
                'fixture' => 'regular_company',
                'totalRecords' => 0,
                'expectedUnassignActionData' => []
            ],
            'new_company_without_acl' => [
                'isAdminAllowed' => false,
                'fixture' => '',
                'totalRecords' => 0,
                'expectedUnassignActionData' => []
            ],
            'parent_company_without_acl' => [
                'isAdminAllowed' => false,
                'fixture' => 'parent_company',
                'totalRecords' => 2,
                'expectedUnassignActionData' => [
                    'Parent Company' => [],
                    'Assigned Company' => []
                ]
            ],
            'assigned_company_without_acl' => [
                'isAdminAllowed' => false,
                'fixture' => 'assigned_company',
                'totalRecords' => 2,
                'expectedUnassignActionData' => [
                    'Parent Company' => [],
                    'Assigned Company' => []
                ]
            ]
        ];
    }
}
