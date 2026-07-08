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
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\User\Test\Fixture\User;

/**
 * Test to verify multiple company assignment
 */
class UpdateCompanyUserMultiassignmentTest extends GraphQLPageCacheAbstract
{
    private const UPDATE = <<<QUERY
mutation {
    updateCompanyUser(
        input: {
            id: "%id"
            job_title: "%job_title"
        }
    ) {
        user {
            job_title
        }
    }
}
QUERY;

    private const CUSTOMER = <<<QUERY
{
    customer {
        job_title
    }
}
QUERY;

    #[
        DataFixture(Customer::class, as: 'customer1'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(Customer::class, as: 'customer3'),
        DataFixture(Customer::class, as: 'customer4'),
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
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer2.id$'
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testUpdateNoPermissions(): void
    {
        /** @var Uid $uid */
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CompanyInterface $company */
        $company = DataFixtureStorageManager::getStorage()->get('company1');
        /** @var CustomerInterface $admin */
        $admin = DataFixtureStorageManager::getStorage()->get('customer2');
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer3');

        $token = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($admin->getEmail(), 'password');

        $this->expectExceptionMessage('You do not have authorization to perform this action.');

        $this->graphQlMutation(
            strtr(
                self::UPDATE,
                [
                    '%id' => $uid->encode((string)$customer->getId()),
                    '%job_title' => 'New Job Title',
                ]
            ),
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Adobe-Company' => $uid->encode((string)$company->getId())
            ]
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
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
                CompanyCustomerInterface::IS_DEFAULT => 0
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testUpdateJobTitle(): void
    {
        $jobTitle = 'New Job Title';
        $this->updateJobTitle('company1', 'customer4', 'customer3', $jobTitle);
        $this->assertEquals($jobTitle, $this->getJobTitle('company1', 'customer3'));
        $this->assertEquals(null, $this->getJobTitle('company2', 'customer3'));

        $jobTitleCompany2 = 'Company 2 job title';
        $this->updateJobTitle('company2', 'customer4', 'customer3', $jobTitleCompany2);
        $this->assertEquals($jobTitle, $this->getJobTitle('company1', 'customer3'));
        $this->assertEquals($jobTitleCompany2, $this->getJobTitle('company2', 'customer3'));
    }

    /**
     * Get job title of customer in scope of the company
     *
     * @param string $company
     * @param string $customer
     * @return string|null
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    private function getJobTitle(string $company, string $customer): ?string
    {
        /** @var Uid $uid */
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CompanyInterface $companyModel */
        $companyModel = DataFixtureStorageManager::getStorage()->get($company);
        /** @var CustomerInterface $customerModel */
        $customerModel = DataFixtureStorageManager::getStorage()->get($customer);

        $token = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($customerModel->getEmail(), 'password');

        $queryResult = $this->graphQlQuery(
            self::CUSTOMER,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Adobe-Company' => $uid->encode((string)$companyModel->getId())
            ]
        );

        $this->assertArrayHasKey('job_title', $queryResult['customer']);

        return $queryResult['customer']['job_title'];
    }

    /**
     * Perform updateCompanyUser mutation by moderator to set job title to customer in scope of company
     *
     * @param string $company
     * @param string $moderator
     * @param string $customer
     * @param string $jobTitle
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    private function updateJobTitle(string $company, string $moderator, string $customer, string $jobTitle): void
    {
        /** @var Uid $uid */
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CompanyInterface $companyModel */
        $companyModel = DataFixtureStorageManager::getStorage()->get($company);
        /** @var CustomerInterface $moderatorModel */
        $moderatorModel = DataFixtureStorageManager::getStorage()->get($moderator);
        /** @var CustomerInterface $customerModel */
        $customerModel = DataFixtureStorageManager::getStorage()->get($customer);

        $moderatorToken = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($moderatorModel->getEmail(), 'password');

        $mutationResult = $this->graphQlMutation(
            strtr(
                self::UPDATE,
                [
                    '%id' => $uid->encode((string)$customerModel->getId()),
                    '%job_title' => $jobTitle,
                ]
            ),
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $moderatorToken,
                'X-Adobe-Company' => $uid->encode((string)$companyModel->getId())
            ]
        );

        $this->assertEquals(['updateCompanyUser' => ['user' => ['job_title' => $jobTitle]]], $mutationResult);
    }
}
