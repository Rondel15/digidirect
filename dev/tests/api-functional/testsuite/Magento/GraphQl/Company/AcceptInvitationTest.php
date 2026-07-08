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
use Magento\Company\Api\Data\RoleInterface;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\CompanyInvitation;
use Magento\Company\Test\Fixture\Role;
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
 * Test to verify accepting invitation to a company
 */
class AcceptInvitationTest extends GraphQLPageCacheAbstract
{
    private const ACCEPT = <<<MUTATION
mutation {
    acceptCompanyInvitation(
        input: {
            code: "%code",
            user: {
                company_id: "%company_id",
                customer_id: "%customer_id",
                job_title: "%job_title"
                status: ACTIVE
            },
            role_id: "%role_id"
        }
    ) {
        success
    }
}
MUTATION;

    private const COMPANIES = <<<QUERY
{
    customer {
        job_title
        role {
            name
        }
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
            'role1'
        ),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company2.id$',
                'permissions' => [
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
            'role2'
        ),
        DataFixture(Customer::class, as: 'customer3'),
        DataFixture(
            CompanyInvitation::class,
            [
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::JOB_TITLE => 'Company 1 Job Title',

            ],
            'invite1'
        ),
        DataFixture(
            CompanyInvitation::class,
            [
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::JOB_TITLE => 'Company 2 Job Title'
            ],
            'invite2'
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAcceptInvite(): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer3');
        /** @var CompanyInterface $company1 */
        $company1 = DataFixtureStorageManager::getStorage()->get('company1');
        /** @var CompanyInterface $company2 */
        $company2 = DataFixtureStorageManager::getStorage()->get('company2');
        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());
        $token = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($customer->getEmail(), 'password');

        /** @var RoleInterface $role1 */
        $role1 = DataFixtureStorageManager::getStorage()->get('role1');
        $this->assertEquals(
            [
                'acceptCompanyInvitation' => [
                    'success' => true
                ]
            ],
            $this->graphQlMutation(
                strtr(
                    self::ACCEPT,
                    [
                        '%code' => DataFixtureStorageManager::getStorage()->get('invite1')->getCode(),
                        '%customer_id' => $uid->encode((string)$customer->getId()),
                        '%company_id' => $uid->encode((string)$company1->getId()),
                        '%job_title' => 'Company 1 Job Title',
                        '%role_id' => $uid->encode((string)$role1->getId())
                    ]
                ),
                [],
                '',
                $headers
            )
        );

        $this->assertEquals(
            [
                'customer' => [
                    'job_title' => 'Company 1 Job Title',
                    'role' => [
                        'name' => $role1->getRoleName(),
                    ],
                    'companies' => [
                        'items' => [
                            [
                                'id' => $uid->encode((string)$company1->getId())
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                self::COMPANIES,
                [],
                '',
                [
                    'Authorization' => 'Bearer ' . $token,
                    'X-Adobe-Company' => $uid->encode((string)$company1->getId())
                ]
            )
        );

        /** @var RoleInterface $role2 */
        $role2 = DataFixtureStorageManager::getStorage()->get('role2');
        $this->assertEquals(
            [
                'acceptCompanyInvitation' => [
                    'success' => true
                ]
            ],
            $this->graphQlMutation(
                strtr(
                    self::ACCEPT,
                    [
                        '%code' => DataFixtureStorageManager::getStorage()->get('invite2')->getCode(),
                        '%customer_id' => $uid->encode((string)$customer->getId()),
                        '%company_id' => $uid->encode((string)$company2->getId()),
                        '%job_title' => 'Company 2 Job Title',
                        '%role_id' => $uid->encode((string) $role2->getId())
                    ]
                ),
                [],
                '',
                $headers
            )
        );

        $this->assertEquals(
            [
                'customer' => [
                    'job_title' => 'Company 1 Job Title',
                    'role' => [
                        'name' => $role1->getRoleName(),
                    ],
                    'companies' => [
                        'items' => [
                            [
                                'id' => $uid->encode((string)$company1->getId())
                            ],
                            [
                                'id' => $uid->encode((string)$company2->getId())
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                self::COMPANIES,
                [],
                '',
                [
                    'Authorization' => 'Bearer ' . $token,
                    'X-Adobe-Company' => $uid->encode((string)$company1->getId())
                ]
            )
        );

        $this->assertEquals(
            [
                'customer' => [
                    'job_title' => 'Company 2 Job Title',
                    'role' => [
                        'name' => $role2->getRoleName(),
                    ],
                    'companies' => [
                        'items' => [
                            [
                                'id' => $uid->encode((string)$company1->getId())
                            ],
                            [
                                'id' => $uid->encode((string)$company2->getId())
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                self::COMPANIES,
                [],
                '',
                [
                    'Authorization' => 'Bearer ' . $token,
                    'X-Adobe-Company' => $uid->encode((string)$company2->getId())
                ]
            )
        );
    }
}
