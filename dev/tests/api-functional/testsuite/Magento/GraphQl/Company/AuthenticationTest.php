<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
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
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test to check that not allowed to log in users will not obtain token
 */
class AuthenticationTest extends GraphQlAbstract
{
    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            Company::class,
            [
                'status' => CompanyInterface::STATUS_REJECTED,
                'super_user_id' => '$customer.id$',
                'reject_reason' => 'rejected',
                'rejected_at' => '2000-01-01 10:10:10'
            ],
            'company'
        )
    ]
    public function testGenerateCustomerTokenWithRejectedCompany()
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account sign-in was incorrect or your account is disabled temporarily.');
        $this->graphQlMutation($this->getQuery($customer->getEmail()));
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            Company::class,
            [
                'status' => CompanyInterface::STATUS_BLOCKED,
                'super_user_id' => '$customer.id$'
            ],
            'company'
        )
    ]
    public function testGenerateCustomerTokenWithBlockedCompany()
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $response = $this->graphQlMutation($this->getQuery($customer->getEmail()));
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertIsArray($response['generateCustomerToken']);
    }

    /**
     * Prepare graphql query to generate token
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    private function getQuery(string $email = 'customer@example.com', string $password = 'password'): string
    {
        return <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;
    }
}
