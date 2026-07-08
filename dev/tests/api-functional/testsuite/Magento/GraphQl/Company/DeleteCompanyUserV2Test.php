<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2024 Adobe
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

namespace Magento\GraphQl\Company;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\User\Test\Fixture\User;

/**
 * Test to verify customer unassignment
 */
class DeleteCompanyUserV2Test extends GraphQLPageCacheAbstract
{
    private const DELETE = <<<QUERY
mutation {
    deleteCompanyUserV2(id: "%s") {
        success
    }
}
QUERY;

    private const COMPANIES = <<<QUERY
{
    customer {
        companies {
            items {
                id
            }
        }
    }
}
QUERY;

    #[
        DataFixture(Customer::class, as: 'customer1'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(Customer::class, as: 'customer3'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer1.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer2.id$',
                CompanyInterface::NAME => 'Company 2'
            ],
            'company2'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
                CompanyCustomerInterface::IS_DEFAULT => 0
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testUnassign(): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer3');
        /** @var CustomerInterface $customer */
        $admin = DataFixtureStorageManager::getStorage()->get('customer1');
        $adminHeaders = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($admin->getEmail());

        $this->assertEquals(
            [
                'deleteCompanyUserV2' => [
                    'success' => true
                ]
            ],
            $this->graphQlMutation(
                sprintf(self::DELETE, $uid->encode((string)$customer->getId())),
                [],
                '',
                $adminHeaders
            )
        );

        /** @var CompanyInterface $company */
        $company = DataFixtureStorageManager::getStorage()->get('company2');
        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());
        $this->assertEquals(
            [
                'customer' => [
                    'companies' => [
                        'items' => [
                            [
                                'id' => $uid->encode((string)$company->getId())
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(self::COMPANIES, [], '', $headers)
        );
    }

    #[
        DataFixture(Customer::class, as: 'super'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$super.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testLastCompany(): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        /** @var CustomerInterface $customer */
        $admin = DataFixtureStorageManager::getStorage()->get('super');
        $adminHeaders = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($admin->getEmail());

        $this->assertEquals(
            [
                'deleteCompanyUserV2' => [
                    'success' => true
                ]
            ],
            $this->graphQlMutation(
                sprintf(self::DELETE, $uid->encode((string)$customer->getId())),
                [],
                '',
                $adminHeaders
            )
        );

        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());

        $this->expectExceptionMessage('The account is locked.');
        $this->graphQlQuery(self::COMPANIES, [], '', $headers);
    }

    #[
        DataFixture(Customer::class, as: 'customer1'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(Customer::class, as: 'customer3'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer1.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer2.id$',
                CompanyInterface::NAME => 'Company 2'
            ],
            'company2'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testDeleteAnotherCompany(): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer3');
        /** @var CustomerInterface $customer */
        $admin = DataFixtureStorageManager::getStorage()->get('customer2');
        $adminHeaders = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($admin->getEmail());

        $this->expectExceptionMessage('Company user with this ID is not assigned to the company.');
        $this->graphQlMutation(
            sprintf(self::DELETE, $uid->encode((string)$customer->getId())),
            [],
            '',
            $adminHeaders
        );
    }

    #[
        DataFixture(Customer::class, as: 'super'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$super.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company'
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testDeleteYourself(): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('super');
        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());

        $this->expectExceptionMessage('You cannot delete yourself.');
        $this->graphQlMutation(
            sprintf(self::DELETE, $uid->encode((string)$customer->getId())),
            [],
            '',
            $headers
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer1'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer1.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer2.id$',
                CompanyInterface::NAME => 'Company 2'
            ],
            'company2'
        ),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company1.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::user_management',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::users_view',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::users_edit',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role'
        ),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company2.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::user_management',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::users_view',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::users_edit',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role2'
        ),
        DataFixture(Customer::class, as: 'customer3'),
        DataFixture(Customer::class, as: 'customer4'),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer4.id$'
            ]
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer4.id$',
                'company_id' => '$company2.id$',
                'role_ids' => ['$role2.id$']
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer4.id$'
            ]
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer4.id$',
                'company_id' => '$company1.id$',
                'role_ids' => ['$role.id$']
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$'
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testDeleteNonDefaultCompany(): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer3');
        /** @var CustomerInterface $customer */
        $admin = DataFixtureStorageManager::getStorage()->get('customer4');
        /** @var CompanyInterface $company */
        $company1 = DataFixtureStorageManager::getStorage()->get('company1');
        $moderatorToken = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($admin->getEmail(), 'password');

        $this->assertEquals(
            [
                'deleteCompanyUserV2' => [
                    'success' => true
                ]
            ],
            $this->graphQlMutation(
                sprintf(self::DELETE, $uid->encode((string)$customer->getId())),
                [],
                '',
                [
                    'Authorization' => 'Bearer ' . $moderatorToken,
                    'X-Adobe-Company' => $uid->encode((string)$company1->getId())
                ]
            )
        );

        /** @var CompanyInterface $company */
        $company2 = DataFixtureStorageManager::getStorage()->get('company2');
        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());
        $this->assertEquals(
            [
                'customer' => [
                    'companies' => [
                        'items' => [
                            [
                                'id' => $uid->encode((string)$company2->getId())
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(self::COMPANIES, [], '', $headers)
        );
    }
}
