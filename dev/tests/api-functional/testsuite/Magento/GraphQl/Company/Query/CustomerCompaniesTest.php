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

namespace Magento\GraphQl\Company\Query;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
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
 * Test to verify the list of customer companies
 */
class CustomerCompaniesTest extends GraphQLPageCacheAbstract
{
    private const COMPANIES = <<<QUERY
{
    customer {
        companies {
            items {
                id
                name
                legal_name
            }
        }
    }
}
QUERY;

    /**
     * Retrieve and verify customer companies
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
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
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$'
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testQuery(): void
    {
        foreach ($this->getUseCases() as $args) {
            $this->verifyQuery(...$args);
        }
    }

    /**
     * Execute the query and verify the response
     *
     * @param string $customer
     * @param array $companies
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function verifyQuery(string $customer, array $companies): void
    {
        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        $expectedItems = [];
        foreach ($companies as $company) {
            $companyModel = DataFixtureStorageManager::getStorage()->get($company);
            $expectedItems[] = [
                'id' => $uid->encode((string)$companyModel->getId()),
                'name' => $companyModel->getCompanyName(),
                'legal_name' => $companyModel->getLegalName()
            ];
        }

        $customerModel = DataFixtureStorageManager::getStorage()->get($customer);
        $headers = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customerModel->getEmail());

        $this->assertEquals(
            ['customer' => ['companies' => ['items' => $expectedItems]]],
            $this->graphQlQuery(self::COMPANIES, [], '', $headers)
        );
    }

    /**
     * Retrieve use cases arguments
     *
     * @return array
     */
    public function getUseCases(): array
    {
        return [
            ['customer1', ['company1']],
            ['customer2', ['company2']],
            ['customer3', ['company1', 'company2']],
        ];
    }
}
