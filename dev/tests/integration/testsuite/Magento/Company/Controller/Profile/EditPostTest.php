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

namespace Magento\Company\Controller\Profile;

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
class EditPostTest extends AbstractController
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
                        'resource_id' => 'Magento_Company::edit_account',
                        'permission' => 'allow'
                    ]
                ]
            ],
            'role'
        ),
    ]
    public function testEditPostControllerUpdatesWithProfileOnlyACL()
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $companyId = $company->getId();
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->getById($customer->getId());
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $this->session->loginById($customer->getId());
        $requestData = [
            'company_name' => 'Updated Company Name',
            'legal_name' => 'Updated Legal Name',
            'company_email' => 'updated@example.com',
            'vat_tax_id' => '987654321',
            'reseller_id' => '123456789',
            'street[0]' => 'updated ' . $company->getStreet()[0],
            'city' => 'updated ' .  $company->getCity(),
            'country_id' => 20,
            'region_id' => 45,
            'region' => $company->getRegion(),
            'postcode' => '11001',
            'telephone' => '1111222233'
        ];

        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $request->setMethod(HttpRequest::METHOD_POST);
        $request->setPostValue($requestData);
        $request->setParam('company_id', $companyId);
        $this->dispatch('/company/profile/editPost');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $updatedCompany = $this->companyRepository->get($companyId);
        $this->assertEquals('Updated Company Name', $updatedCompany->getCompanyName());
        $this->assertEquals('Updated Legal Name', $updatedCompany->getLegalName());
        $this->assertEquals('updated@example.com', $updatedCompany->getCompanyEmail());
        $this->assertEquals('987654321', $updatedCompany->getVatTaxId());
        $this->assertEquals('123456789', $updatedCompany->getResellerId());
        $this->assertEquals($company->getStreet(), $updatedCompany->getStreet());
        $this->assertEquals($company->getCity(), $updatedCompany->getCity());
        $this->assertEquals($company->getCountryId(), $updatedCompany->getCountryId());
        $this->assertEquals($company->getRegionId(), $updatedCompany->getRegionId());
        $this->assertEquals($company->getRegion(), $updatedCompany->getRegion());
        $this->assertEquals($company->getPostCode(), $updatedCompany->getPostcode());
        $this->assertEquals($company->getTelephone(), $updatedCompany->getTelephone());
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
                        'resource_id' => 'Magento_Company::view_address',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::edit_address',
                        'permission' => 'allow'
                    ]
                ]
            ],
            'role'
        ),
    ]
    public function testEditPostControllerUpdatesWithEditAddressOnlyACL()
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $companyId = $company->getId();
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->getById($customer->getId());
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $this->session->loginById($customer->getId());

        $requestData = [
            'company_name' => 'Updated Company Name',
            'legal_name' => 'Updated Legal Name',
            'company_email' => 'updated@example.com',
            'vat_tax_id' => '987654321',
            'reseller_id' => '123456789',
            'street' => ['updated ' . $company->getStreet()[0]],
            'city' => 'updated ' .  $company->getCity(),
            'country_id' => $company->getCountryId(),
            'region_id' => 45,
            'region' => $company->getRegion(),
            'postcode' => '11001',
            'telephone' => '1111222233'
        ];

        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $request->setMethod(HttpRequest::METHOD_POST);
        $request->setPostValue($requestData);
        $request->setParam('company_id', $companyId);
        $this->dispatch('/company/profile/editPost');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $updatedCompany = $this->companyRepository->get($companyId);
        $this->assertEquals($company->getCompanyName(), $updatedCompany->getCompanyName());
        $this->assertEquals($company->getLegalName(), $updatedCompany->getLegalName());
        $this->assertEquals($company->getCompanyEmail(), $updatedCompany->getCompanyEmail());
        $this->assertEquals($company->getVatTaxId(), $updatedCompany->getVatTaxId());
        $this->assertEquals($company->getResellerId(), $updatedCompany->getResellerId());
        $this->assertEquals('updated ' . $company->getStreet()[0], $updatedCompany->getStreet()[0]);
        $this->assertEquals('updated ' . $company->getCity(), $updatedCompany->getCity());
        $this->assertEquals($company->getCountryId(), $updatedCompany->getCountryId());
        $this->assertEquals(45, $updatedCompany->getRegionId());
        $this->assertEquals($company->getRegion(), $updatedCompany->getRegion());
        $this->assertEquals('11001', $updatedCompany->getPostcode());
        $this->assertEquals('1111222233', $updatedCompany->getTelephone());
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
                        'resource_id' => 'Magento_Company::view_address',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::view_account',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role'
        ),
    ]
    public function testEditPostControllerUpdatesWithNoAddressOrProfileEditACL()
    {
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $companyId = $company->getId();
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->getById($customer->getId());
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $this->session->loginById($customer->getId());
        $requestData = [
            'company_name' => 'Updated Company Name',
            'legal_name' => 'Updated Legal Name',
            'company_email' => 'updated@example.com',
            'vat_tax_id' => '987654321',
            'reseller_id' => '123456789',
            'street' => 'updated ' . $company->getStreet()[0],
            'city' => 'updated ' .  $company->getCity(),
            'country_id' => 20,
            'region_id' => 45,
            'region' => $company->getRegion(),
            'postcode' => '11001',
            'telephone' => '1111222233'
        ];

        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $request->setMethod(HttpRequest::METHOD_POST);
        $request->setPostValue($requestData);
        $request->setParam('company_id', $companyId);
        $this->dispatch('/company/profile/editPost');
        $response = $this->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $redirectUrl = $response->getHeaders()->get('Location')->getFieldValue();
        $this->assertStringContainsString('company/accessdenied', $redirectUrl);
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
