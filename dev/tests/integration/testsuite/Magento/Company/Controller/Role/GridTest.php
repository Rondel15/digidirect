<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 *  ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Company\Controller\Role;

use Laminas\Http\Headers;
use Magento\Company\Api\AclInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Role as RoleModel;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Company\Test\Fixture\Role;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\User\Test\Fixture\User;

/**
 * Integration test for the Company EditPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    AppArea('frontend')
]
class GridTest extends AbstractController
{
    /**
     * @var AclInterface
     */
    private $aclRoleManager;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CompanyCustomerInterfaceFactory
     */
    private $companyAttributesFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Set up environment for the test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->companyRepository = Bootstrap::getObjectManager()->get(CompanyRepositoryInterface::class);
        $this->session = $this->objectManager->get(Session::class);
        $this->companyAttributesFactory = $this->objectManager->get(CompanyCustomerInterfaceFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->aclRoleManager = $this->objectManager->get(AclInterface::class);
        $this->resource = $this->objectManager::getInstance()->get(ResourceConnection::class);
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1, ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer.id$'
            ],
            'company'
        ),
        DataFixture(Customer::class, as: 'sub_customer'),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::index',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::view',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::view_account',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::user_management',
                        'permission' => 'allow'
                    ]
                ]
            ],
            'role'
        ),
    ]
    public function testGridMuiControllerWithoutRoleViewPermissions()
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->getById($customer->getId());
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $this->session->loginById($customer->getId());

        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $headers = $this->objectManager->create(Headers::class)
            ->addHeaders(
                [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Accept' => 'application/json',
                ]
            );
        $request->setHeaders($headers);
        $request->setMethod(HttpRequest::METHOD_GET);
        $request->setParam('namespace', 'role_listing');
        $this->dispatch('company/ui/render');
        $response = $this->getResponse();
        $response = json_decode($response->getBody(), true);
        $this->assertEquals(0, $response['totalRecords']);
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1, ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer.id$'
            ],
            'company'
        ),
        DataFixture(Customer::class, as: 'sub_customer'),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::index',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::view',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::view_account',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::user_management',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::roles_view',
                        'permission' => 'allow'
                    ]
                ]
            ],
            'role'
        ),
    ]
    public function testGridMuiControllerWithRoleViewPermissions()
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->getById($customer->getId());
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $this->session->loginById($customer->getId());

        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $headers = $this->objectManager->create(Headers::class)
            ->addHeaders(
                [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Accept' => 'application/json',
                ]
            );
        $request->setHeaders($headers);
        $request->setMethod(HttpRequest::METHOD_GET);
        $request->setParam('namespace', 'role_listing');
        $this->dispatch('company/ui/render');
        $response = $this->getResponse();
        $response = json_decode($response->getBody(), true);
        $this->assertEquals(2, $response['totalRecords']);
    }

    /**
     * Assign customer to company.
     *
     * @param CustomerInterface $customer
     * @param CompanyInterface $company
     * @return void
     */
    private function assignCustomerToCompany(CustomerInterface $customer, CompanyInterface $company): void
    {
        $companyCustomerAttributes = $this->companyAttributesFactory->create();
        $companyCustomerAttributes->setCustomerId($customer->getId());
        $companyCustomerAttributes->setCompanyId($company->getId());
        $customer->getExtensionAttributes()->setCompanyAttributes($companyCustomerAttributes);
        $connection = $this->resource->getConnection();
        $connection->delete(
            'company_advanced_customer_entity',
            ['company_id = ?' => 0, 'customer_id = ?' => $customer->getId()]
        );
        $this->customerRepository->save($customer);
    }

    /**
     * Assign role to customer.
     *
     * @param CustomerInterface $customer
     * @param RoleModel $role
     * @return void
     * @throws NoSuchEntityException
     */
    private function assignRoleToCustomer(CustomerInterface $customer, RoleModel $role): void
    {
        $this->aclRoleManager->assignRoles($customer->getId(), [$role]);
    }
}
