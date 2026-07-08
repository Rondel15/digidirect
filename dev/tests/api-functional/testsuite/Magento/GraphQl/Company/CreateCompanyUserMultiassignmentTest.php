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
use Magento\Company\Api\RoleManagementInterface;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Role;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\User\Test\Fixture\User;

/**
 * Test to verify multiple company assignment
 */
class CreateCompanyUserMultiassignmentTest extends GraphQLPageCacheAbstract
{
    private const CREATE = <<<QUERY
mutation {
    createCompanyUser(
        input: {
            job_title: "Shopper",
            role_id: "%role_id",
            firstname: "John",
            lastname: "Doe",
            email: "%email",
            telephone: "15156614488",
            status: ACTIVE
        }
    ) {
        user {
            companies {
                items {
                    id
                }
            }
        }
    }
}
QUERY;

    #[
        DataFixture(Customer::class, as: 'customer1'),
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
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testAddToFirstCompany(): void
    {
        $this->expectExceptionMessage(
            'Invitation was sent to an existing customer, '
            . 'they will be added to your organization once they accept the invitation.'
        );
        $this->inviteCustomerToCompany('company1', 'customer1', 'customer3');
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
    public function testAddToSecondCompany(): void
    {
        $this->expectExceptionMessage(
            'Invitation was sent to an existing customer, '
            . 'they will be added to your organization once they accept the invitation.'
        );
        $this->inviteCustomerToCompany('company2', 'customer2', 'customer3');
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
                CompanyCustomerInterface::CUSTOMER_ID => '$customer4.id$',
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
                CompanyCustomerInterface::CUSTOMER_ID => '$customer4.id$',
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
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testAddNonDefaultCompany(): void
    {
        $this->expectExceptionMessage(
            'Invitation was sent to an existing customer, '
            . 'they will be added to your organization once they accept the invitation.'
        );
        $this->inviteCustomerToCompany('company1', 'customer4', 'customer3');
    }

    /**
     * Call createCompanyUser mutation
     *
     * @param string $company
     * @param string $admin
     * @param string $customer
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    private function inviteCustomerToCompany(string $company, string $admin, string $customer): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        $companyId = DataFixtureStorageManager::getStorage()->get($company)->getId();
        $defaultRole = Bootstrap::getObjectManager()->get(RoleManagementInterface::class)
            ->getCompanyDefaultRole($companyId);

        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute(DataFixtureStorageManager::getStorage()->get($admin)->getEmail());
        $headers['X-Adobe-Company'] = $uid->encode((string)$companyId);

        $this->graphQlMutation(
            strtr(
                self::CREATE,
                [
                    '%role_id' => $uid->encode((string)$defaultRole->getId()),
                    '%email' => DataFixtureStorageManager::getStorage()->get($customer)->getEmail()
                ]
            ),
            [],
            '',
            $headers
        );
    }
}
