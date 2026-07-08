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

namespace Magento\GraphQl\CompanyGraphQl;

use Magento\Company\Api\AclInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\CompanyRepositoryInterfaceFactory;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Role as RoleModel;
use Magento\Company\Test\Fixture\Role;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Customer\Test\Fixture\Customer;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\User\Test\Fixture\User;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\Company\Model\CompanyRepository\Cache as CompanyCache;

/**
 * Test coverage for company update mutation with ACL.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCompanyMutationAclTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyCustomerInterfaceFactory
     */
    private $companyAttributesFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AclInterface
     */
    private $aclRoleManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CompanyRepositoryInterfaceFactory
     */
    private $companyRepositoryFactory;

    /**
     * @var CompanyCache
     */
    private $companyCache;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->companyRepository = Bootstrap::getObjectManager()->get(CompanyRepositoryInterface::class);
        $this->companyRepositoryFactory = $this->objectManager->get(CompanyRepositoryInterfaceFactory::class);
        $this->companyAttributesFactory = $this->objectManager->get(CompanyCustomerInterfaceFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->aclRoleManager = $this->objectManager->get(AclInterface::class);
        $this->resource = $this->objectManager::getInstance()->get(ResourceConnection::class);
        $this->companyCache = $this->objectManager->get(CompanyCache::class);
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(Customer::class, as: 'sub_customer'),
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
                        'resource_id' => 'Magento_Company::view_address',
                        'permission' => 'allow'
                    ]
                ]
            ],
            'role'
        ),
    ]
    public function testCompanyUpdateWithNoAddressOrProfileEditAcl()
    {
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->get($customer->getEmail());
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $adminCustomer = DataFixtureStorageManager::getStorage()->get('admin_customer');
        $connection = $this->resource->getConnection();
        $connection->delete(
            'company_advanced_customer_entity',
            ['company_id = ?' => 0, 'customer_id = ?' => $adminCustomer->getId()]
        );
        $companyData = [];
        $companyData['company_name'] = 'Updated Company Name';
        $companyData['company_email'] = 'updated@company.com';
        $companyData['legal_name'] = 'Updated Legal Name';
        $companyData['vat_tax_id'] = 'Updated12345';
        $companyData['reseller_id'] = 'Updated67890';
        $companyData['street'] = 'Updated 123 Main St';
        $companyData['city'] = 'Updated Example City';
        $companyData['country_id'] = 'US';
        $companyData['region_id'] = '20';
        $companyData['postcode'] = '54321';
        $companyData['telephone'] = '098-765-4321';
        $mutation = $this->getUpdateCompanyMutation($companyData);

        // Execute the query and retrieve the response
        $headers = $this->getHeaderAuthentication($customer->getEmail(), 'password');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You do not have authorization to perform this action.');
        $this->graphQlMutation($mutation, [], '', $headers);
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(Customer::class, as: 'sub_customer'),
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
    public function testCompanyUpdateWithAddressEditAcl()
    {
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $originalCompany = clone $company;
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->get($customer->getEmail());
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $adminCustomer = DataFixtureStorageManager::getStorage()->get('admin_customer');
        $connection = $this->resource->getConnection();
        $connection->delete(
            'company_advanced_customer_entity',
            ['company_id = ?' => 0, 'customer_id = ?' => $adminCustomer->getId()]
        );
        $companyData = [];
        $companyData['company_name'] = 'Updated Company Name';
        $companyData['company_email'] = 'updated@company.com';
        $companyData['legal_name'] = 'Updated Legal Name';
        $companyData['vat_tax_id'] = 'Updated12345';
        $companyData['reseller_id'] = 'Updated67890';
        $companyData['street'] = 'Updated 123 Main St';
        $companyData['city'] = 'Updated Example City';
        $companyData['country_id'] = 'US';
        $companyData['region_id'] = '20';
        $companyData['postcode'] = '54321';
        $companyData['telephone'] = '098-765-4321';
        $mutation = $this->getUpdateCompanyMutationWithAddressOnly($companyData);

        // Execute the query and retrieve the response
        $headers = $this->getHeaderAuthentication($customer->getEmail());
        $this->graphQlMutation($mutation, [], '', $headers);
        $this->companyCache->delete((int)$company->getId());
        $company = $this->companyRepository->get($company->getId());
        $this->assertEquals($originalCompany->getCompanyName(), $company->getCompanyName());
        $this->assertEquals($originalCompany->getCompanyEmail(), $company->getCompanyEmail());
        $this->assertEquals($originalCompany->getLegalName(), $company->getLegalName());
        $this->assertEquals($originalCompany->getVatTaxId(), $company->getVatTaxId());
        $this->assertEquals($originalCompany->getResellerId(), $company->getResellerId());
        $this->assertEquals($companyData['street'], $company->getStreetFull());
        $this->assertEquals($companyData['city'], $company->getCity());
        $this->assertEquals($companyData['country_id'], $companyData['country_id']);
        $this->assertEquals($companyData['region_id'], $company->getRegionId());
        $this->assertEquals($companyData['postcode'], $company->getPostcode());
        $this->assertEquals($companyData['telephone'], $company->getTelephone());
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'admin_customer'),
        DataFixture(Customer::class, as: 'sub_customer'),
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
    public function testCompanyUpdateWithAccountEditAcl()
    {
        $role = DataFixtureStorageManager::getStorage()->get('role');
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $originalCompany = clone $company;
        $customer = DataFixtureStorageManager::getStorage()->get('sub_customer');
        $customer = $this->customerRepository->get($customer->getEmail());
        $this->assignCustomerToCompany($customer, $company);
        $this->assignRoleToCustomer($customer, $role);
        $adminCustomer = DataFixtureStorageManager::getStorage()->get('admin_customer');
        $connection = $this->resource->getConnection();
        $connection->delete(
            'company_advanced_customer_entity',
            ['company_id = ?' => 0, 'customer_id = ?' => $adminCustomer->getId()]
        );
        $companyData = [];
        $companyData['company_name'] = 'Updated Company Name';
        $companyData['company_email'] = 'updated@company.com';
        $companyData['legal_name'] = 'Updated Legal Name';
        $companyData['vat_tax_id'] = 'Updated12345';
        $companyData['reseller_id'] = 'Updated67890';
        $companyData['street'] = 'Updated 123 Main St';
        $companyData['city'] = 'Updated Example City';
        $companyData['country_id'] = 'US';
        $companyData['region_id'] = '20';
        $companyData['postcode'] = '54321';
        $companyData['telephone'] = '098-765-4321';
        $mutation = $this->getUpdateCompanyMutationWithAccountOnly($companyData);

        // Execute the query and retrieve the response
        $headers = $this->getHeaderAuthentication($customer->getEmail());
        $this->graphQlMutation($mutation, [], '', $headers);
        $this->companyCache->delete((int)$company->getId());
        $company = $this->companyRepository->get($company->getId());
        $this->assertEquals($companyData['company_name'], $company->getCompanyName());
        $this->assertEquals($companyData['company_email'], $company->getCompanyEmail());
        $this->assertEquals($companyData['legal_name'], $company->getLegalName());
        $this->assertEquals($companyData['vat_tax_id'], $company->getVatTaxId());
        $this->assertEquals($companyData['reseller_id'], $company->getResellerId());
        $this->assertEquals($originalCompany->getStreet(), $company->getStreet());
        $this->assertEquals($originalCompany->getCity(), $company->getCity());
        $this->assertEquals($originalCompany->getCountryId(), $companyData['country_id']);
        $this->assertEquals($originalCompany->getRegionId(), $company->getRegionId());
        $this->assertEquals($originalCompany->getPostcode(), $company->getPostcode());
        $this->assertEquals($originalCompany->getTelephone(), $company->getTelephone());
    }

    /**
     * Authentication header mapping
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    private function getHeaderAuthentication(
        string $username = 'customer@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
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

    /**
     * Returns GraphQl mutation string
     *
     * @param array $data
     * @return string
     */
    private function getUpdateCompanyMutation($data): string
    {
        return <<<MUTATION
mutation {
    updateCompany(
        input: {
            company_name: "{$data['company_name']}",
            company_email: "{$data['company_email']}",
            legal_name: "{$data['legal_name']}",
            vat_tax_id: "{$data['vat_tax_id']}",
            reseller_id: "{$data['reseller_id']}",
            legal_address: {
                street: ["{$data['street']}"],
                city: "{$data['city']}",
                country_id: {$data['country_id']},
                region: {
                    region_id: "{$data['region_id']}"
                },
                postcode: "{$data['postcode']}",
                telephone: "{$data['telephone']}"
            }
        }
    ) {
        company {
            name
            email
            legal_name
            vat_tax_id
            reseller_id
            legal_address {
                street
                city
                country_code
                postcode
                telephone
            }
        }
    }
}
MUTATION;
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param array $data
     * @return string
     */
    private function getUpdateCompanyMutationWithAddressOnly(array $data): string
    {
        return <<<MUTATION
mutation {
    updateCompany(
        input: {
            company_name: "{$data['company_name']}",
            company_email: "{$data['company_email']}",
            legal_name: "{$data['legal_name']}",
            vat_tax_id: "{$data['vat_tax_id']}",
            reseller_id: "{$data['reseller_id']}",
            legal_address: {
                street: ["{$data['street']}"],
                city: "{$data['city']}",
                country_id: {$data['country_id']},
                region: {
                    region_id: "{$data['region_id']}"
                },
                postcode: "{$data['postcode']}",
                telephone: "{$data['telephone']}"
            }
        }
    ) {
        company {
            legal_address {
                street
                city
                country_code
                postcode
                telephone
            }
        }
    }
}
MUTATION;
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param array $data
     * @return string
     */
    private function getUpdateCompanyMutationWithAccountOnly(array $data): string
    {
        return <<<MUTATION
mutation {
    updateCompany(
        input: {
            company_name: "{$data['company_name']}",
            company_email: "{$data['company_email']}",
            legal_name: "{$data['legal_name']}",
            vat_tax_id: "{$data['vat_tax_id']}",
            reseller_id: "{$data['reseller_id']}",
            legal_address: {
                street: ["{$data['street']}"],
                city: "{$data['city']}",
                country_id: {$data['country_id']},
                region: {
                    region_id: "{$data['region_id']}"
                },
                postcode: "{$data['postcode']}",
                telephone: "{$data['telephone']}"
            }
        }
    ) {
        company {
            name
            email
            legal_name
            vat_tax_id
            reseller_id
        }
    }
}
MUTATION;
    }
}
