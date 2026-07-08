<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrderRule\Model\Rule;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\RoleInterface;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Company\Model\CompanyUser;
use Magento\Company\Api\AclInterface as UserRoleManagement;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Role;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\UrlInterface;
use Magento\PurchaseOrderRule\Api\Data\RuleInterface;
use Magento\PurchaseOrderRule\Test\Fixture\Rule;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Test\Fixture\User;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var UserRoleManagement
     */
    private $userRoleManagement;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var SearchCriteriaBuilder|mixed
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var CompanyRepositoryInterface|mixed
     */
    private CompanyRepositoryInterface $companyRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        $this->urlBuilder = $this->objectManager->create(UrlInterface::class);
        $this->session = $this->objectManager->get(Session::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->userRoleManagement = $this->objectManager->get(UserRoleManagement::class);
        $this->roleRepository = $this->objectManager->get(RoleRepositoryInterface::class);
        $this->dataProvider = $this->objectManager->create(
            DataProvider::class,
            [
                'name' => 'purchase_order_rule_listing_data_source',
                'primaryFieldName' => 'primary',
                'requestFieldName' => 'request'
            ]
        );

        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
    }

    /**
     * Test the sort order results for each sortable column
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/PurchaseOrderRule/_files/rules_for_sorting.php
     * @dataProvider sortOrderDataProvider
     */
    public function testGetData(string $sortByField, string $direction, array $expectedResults)
    {
        $customer = $this->customerRepository->get('veronica.costello@example.com');
        $this->session->loginById($customer->getId());
        $this->getDataTestSteps($sortByField, $direction, $expectedResults);
    }

    /**
     * @param string $sortByField
     * @param string $direction
     * @param array $expectedResults
     * @return void
     */
    private function getDataTestSteps(string $sortByField, string $direction, array $expectedResults): void
    {
        // @var SortOrder
        $sortOrder = $this->objectManager->get(SortOrder::class);
        $sortOrder->setField($sortByField);
        $sortOrder->setDirection($direction);
        $this->dataProvider->getSearchCriteria()->setSortOrders([$sortOrder]);
        $data = $this->dataProvider->getData();
        $actualResult = [];
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $actualResult[] = $item[$sortByField];
            }
        }
        $this->assertEquals($expectedResults, $actualResult);
    }

    /**
     * Test manage approval rules acl for different role configuration
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @dataProvider rulesPermissions
     */
    public function testGetDataRolePermissions($resourceId, $permissionValue, $expectedResult)
    {
        $customer = $this->customerRepository->get('veronica.costello@example.com');
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
        $roleRepository = $this->objectManager->get(RoleRepositoryInterface::class);

        $companies = $companyRepository->getList(
            $searchCriteriaBuilder->addFilter('company_name', 'Magento')->create()
        )->getItems();

        $roles = $roleRepository->getList(
            $searchCriteriaBuilder->addFilter('role_name', 'Default User')->create()
        )->getItems();

        /** @var CompanyInterface $company */
        $company = reset($companies);
        /** @var RoleInterface $role */
        $role = reset($roles);
        $permissions = $role->getPermissions();
        foreach ($permissions as $permission) {
            if ($permission->getResourceId() == $resourceId) {
                $permission->setPermission($permissionValue);
                $permission->save();
                break;
            }
        }

        $this->userRoleManagement->assignUserDefaultRole($customer->getId(), $company->getId());
        $this->session->loginById($customer->getId());
        $data = $this->dataProvider->getData();
        if (isset($expectedResult['createRuleUrl'])) {
            $expectedResult['createRuleUrl'] = $this->urlBuilder->getUrl('purchaseorderrule/create');
        }
        $this->assertEquals($expectedResult, $data);
    }

    /**
     * @param int $companyId
     * @param $permissionValue
     * @param $expectedResult
     * @return void
     */
    private function getDataRolePermissionsTestSteps(int $companyId, $permissionValue, $expectedResult): void
    {
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $roleRepository = $this->objectManager->get(RoleRepositoryInterface::class);
        $roles = $roleRepository->getList(
            $searchCriteriaBuilder
                ->addFilter('role_name', 'Default User')
                ->addFilter('company_id', $companyId)
                ->create()
        )->getItems();
        $this->assertCount(1, $roles);

        /** @var RoleInterface $role */
        $role = reset($roles);
        $permissions = $role->getPermissions();
        foreach ($permissions as $permission) {
            if ($permission->getResourceId() == 'Magento_PurchaseOrderRule::manage_approval_rules') {
                $permission->setPermission($permissionValue);
                $permission->save();
                break;
            }
        }

        $data = $this->dataProvider->getData();
        if (isset($expectedResult['createRuleUrl'])) {
            $expectedResult['createRuleUrl'] = $this->urlBuilder->getUrl('purchaseorderrule/create');
        }
        $this->assertEquals($expectedResult, $data);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'superUser1'),
        DataFixture(Customer::class, as: 'superUser2'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'superUser3'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser1.id$',
                CompanyInterface::NAME => 'Magento_A'
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser2.id$',
                CompanyInterface::NAME => 'Magento_B'
            ],
            'company2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser3.id$',
                CompanyInterface::NAME => 'Magento_C'
            ],
            'company3'
        ),
        DataFixture(
            Role::class,
            [
                RoleInterface::COMPANY_ID => '$company1.entity_id$'
            ],
            'approver_a'
        ),
        DataFixture(
            Role::class,
            [
                RoleInterface::COMPANY_ID => '$company2.entity_id$'
            ],
            'approver_b'
        ),
        DataFixture(
            Role::class,
            [
                RoleInterface::COMPANY_ID => '$company3.entity_id$'
            ],
            'approver_c'
        ),
        DataFixture(
            Rule::class,
            [
                RuleInterface::KEY_COMPANY_ID => '$company1.entity_id$',
                RuleInterface::KEY_APPROVER_ROLE_IDS => ['$approver_a.role_id$'],
                RuleInterface::KEY_CREATED_BY => '$superUser1.id$',
                RuleInterface::KEY_NAME => '1 A Integration Test Rule',
                RuleInterface::KEY_DESCRIPTION => 'rule1A test description'
            ],
            'rule1A'
        ),
        DataFixture(
            Rule::class,
            [
                RuleInterface::KEY_COMPANY_ID => '$company1.entity_id$',
                RuleInterface::KEY_APPROVER_ROLE_IDS => ['$approver_a.role_id$'],
                RuleInterface::KEY_CREATED_BY => '$superUser1.id$',
                RuleInterface::KEY_NAME => '2 A Integration Test Rule',
                RuleInterface::KEY_DESCRIPTION => 'rule2A test description'
            ],
            'rule2A'
        ),
        DataFixture(
            Rule::class,
            [
                RuleInterface::KEY_COMPANY_ID => '$company2.entity_id$',
                RuleInterface::KEY_APPROVER_ROLE_IDS => ['$approver_b.role_id$'],
                RuleInterface::KEY_CREATED_BY => '$superUser2.id$',
                RuleInterface::KEY_NAME => '1 B Integration Test Rule',
                RuleInterface::KEY_DESCRIPTION => 'rule1B test description'
            ],
            'rule1B'
        ),
        DataFixture(
            Rule::class,
            [
                RuleInterface::KEY_COMPANY_ID => '$company2.entity_id$',
                RuleInterface::KEY_APPROVER_ROLE_IDS => ['$approver_b.role_id$'],
                RuleInterface::KEY_CREATED_BY => '$superUser2.id$',
                RuleInterface::KEY_NAME => '2 B Integration Test Rule',
                RuleInterface::KEY_DESCRIPTION => 'rule2B test description',
            ],
            'rule2B'
        ),
        DataFixture(
            Rule::class,
            [
                RuleInterface::KEY_COMPANY_ID => '$company3.entity_id$',
                RuleInterface::KEY_APPROVER_ROLE_IDS => ['$approver_c.role_id$'],
                RuleInterface::KEY_CREATED_BY => '$superUser3.id$',
                RuleInterface::KEY_NAME => '1 D Integration Test Rule',
                RuleInterface::KEY_DESCRIPTION => 'rule1C test description',
            ],
            'rule1C'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        )
    ]
    /**
     * Test the sort order results for each sortable column
     * for the customer with various company_id set in Context in case
     * Customer is assigned to multiple Companies
     *
     * @dataProvider multiCompanySortOrderDataProvider
     */
    public function testGetDataWithMultiCompanyAssigment(
        string $companyName,
        string $sortByField,
        string $direction,
        array $expectedResults
    ) {
        $companyId = (int) FixtureManager::getStorage()->get($companyName)->getId();
        $customerId = (int) FixtureManager::getStorage()->get('customer')->getId();
        $this->session->loginById($customerId);
        $this->setCompanyContext($companyId);
        $this->getDataTestSteps($sortByField, $direction, $expectedResults);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'superUser1'),
        DataFixture(Customer::class, as: 'superUser2'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'superUser3'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser1.id$',
                CompanyInterface::NAME => 'Magento_A'
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser2.id$',
                CompanyInterface::NAME => 'Magento_B'
            ],
            'company2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$superUser3.id$',
                CompanyInterface::NAME => 'Magento_C'
            ],
            'company3'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        )
    ]
    /**
     * Test manage approval rules acl for different role configuration
     * for the customer with various company_id set in Context in case.
     * Customer is assigned to multiple Companies
     *
     * @dataProvider multiCompanyRulesPermissions
     */
    public function testGetDataRolePermissionsWithMultiCompanyAssigment(
        string $companyName,
        string $permissionValue,
        array $expectedResult
    ) {
        $companyId = (int) FixtureManager::getStorage()->get($companyName)->getId();
        $customerId = (int) FixtureManager::getStorage()->get('customer')->getId();
        $this->session->loginById($customerId);
        $this->setCompanyContext($companyId);
        $this->getDataRolePermissionsTestSteps($companyId, $permissionValue, $expectedResult);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->session->logout();
    }

    /**
     * Data provider containing expected manage approval rule permissions
     *
     * @return array[]
     */
    public static function rulesPermissions(): array
    {
        return [
            [
                'Magento_PurchaseOrderRule::manage_approval_rules',
                'deny',
                [
                    'isCreateRuleAllowed' => false,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            [
                'Magento_PurchaseOrderRule::manage_approval_rules',
                'allow',
                [
                    'isCreateRuleAllowed' => true,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            [
                'Magento_PurchaseOrderRule::view_approval_rules',
                'allow',
                [
                    'isCreateRuleAllowed' => false,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            [
                'Magento_PurchaseOrderRule::view_approval_rules',
                'deny',
                []
            ],

        ];
    }

    /**
     * Data provider containing expected sorting results
     *
     * @return array[]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function sortOrderDataProvider(): array
    {
        return [
            [
                'name',
                SortOrder::SORT_ASC,
                [
                    '1   Integration Test Rule Name 7',
                    '10   Integration Test Rule Name 9',
                    '10Alex Smith Integration Test Rule Name 6',
                    '1Alex Smith Integration Test Rule Name 4',
                    '2   Integration Test Rule Name 8',
                    '2Alex Smith Integration Test Rule Name 5',
                    'BeforeTest Smith Integration Test Rule Name 0',
                    'Test Smith Integration Test Rule Name 1',
                    'test Smith Integration Test Rule Name 3',
                    'Tests Smith Integration Test Rule Name 2',
                ]
            ],
            [
                'name',
                SortOrder::SORT_DESC,
                [
                    'Tests Smith Integration Test Rule Name 2',
                    'test Smith Integration Test Rule Name 3',
                    'Test Smith Integration Test Rule Name 1',
                    'BeforeTest Smith Integration Test Rule Name 0',
                    '2Alex Smith Integration Test Rule Name 5',
                    '2   Integration Test Rule Name 8',
                    '1Alex Smith Integration Test Rule Name 4',
                    '10Alex Smith Integration Test Rule Name 6',
                    '10   Integration Test Rule Name 9',
                    '1   Integration Test Rule Name 7',
                ]
            ],
            [
                'is_active',
                SortOrder::SORT_ASC,
                [
                    '0', '0', '1', '1', '1', '1', '1', '1', '1', '1'
                ]
            ],
            [
                'is_active',
                SortOrder::SORT_DESC,
                [
                    '1', '1', '1', '1', '1', '1', '1', '1', '0', '0'
                ]
            ],
            [
                'created_by_name',
                SortOrder::SORT_ASC,
                [
                    '1  ',
                    '10  ',
                    '10Alex Smith',
                    '1Alex Smith',
                    '2  ',
                    '2Alex Smith',
                    'BeforeTest Smith',
                    'Test Smith',
                    'test Smith',
                    'Tests Smith',
                ]
            ],
            [
                'created_by_name',
                SortOrder::SORT_DESC,
                [
                    'Tests Smith',
                    'Test Smith',
                    'test Smith',
                    'BeforeTest Smith',
                    '2Alex Smith',
                    '2  ',
                    '1Alex Smith',
                    '10Alex Smith',
                    '10  ',
                    '1  ',
                ]
            ]
        ];
    }

    /**
     * @param int $companyId
     * @return void
     */
    private function setCompanyContext(int $companyId): void
    {
        $httpContext = Bootstrap::getObjectManager()->get(Context::class);
        $httpContext->setValue('company_id', $companyId, null);
    }

    /**
     * Data provider containing expected sorting results for multi company
     *
     * @return array[]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function multiCompanySortOrderDataProvider(): array
    {
        return [
            'company_a_name_asc' => [
                'company1',
                'name',
                SortOrder::SORT_ASC,
                [
                    '1 A Integration Test Rule',
                    '2 A Integration Test Rule',
                ]
            ],
            'company_a_name_desc' => [
                'company1',
                'name',
                SortOrder::SORT_DESC,
                [
                    '2 A Integration Test Rule',
                    '1 A Integration Test Rule',
                ]
            ],
            'company_b_name_asc' => [
                'company2',
                'name',
                SortOrder::SORT_ASC,
                [
                    '1 B Integration Test Rule',
                    '2 B Integration Test Rule',
                ]
            ],
            'company_b_name_desc' => [
                'company2',
                'name',
                SortOrder::SORT_DESC,
                [
                    '2 B Integration Test Rule',
                    '1 B Integration Test Rule',
                ]
            ],
            'company_b_desc_asc' => [
                'company2',
                'description',
                SortOrder::SORT_ASC,
                [
                    'rule1B test description',
                    'rule2B test description'
                ]
            ],
            'company_b_desc_desc' => [
                'company2',
                'description',
                SortOrder::SORT_DESC,
                [
                    'rule2B test description',
                    'rule1B test description'
                ]
            ],
            'not_assigned_company' => [
                'company3',
                'name',
                SortOrder::SORT_DESC,
                []
            ]
        ];
    }

    /**
     * Data provider containing expected manage approval rule permissions
     *
     * @return array[]
     *
     */
    public function multiCompanyRulesPermissions(): array
    {
        return [
            'company_a_context_deny' => [
                'company1',
                'deny',
                [
                    'isCreateRuleAllowed' => false,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            'company_a_context_allow' => [
                'company1',
                'allow',
                [
                    'isCreateRuleAllowed' => true,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            'company_b_context_deny' => [
                'company2',
                'deny',
                [
                    'isCreateRuleAllowed' => false,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            'company_b_context_allow' => [
                'company2',
                'allow',
                [
                    'isCreateRuleAllowed' => true,
                    'totalRecords' => 0,
                    'items' => [],
                    'createRuleUrl' => ''
                ]
            ],
            'not_assigned_company' => [
                'company3',
                '',
                []
            ]
        ];
    }
}
