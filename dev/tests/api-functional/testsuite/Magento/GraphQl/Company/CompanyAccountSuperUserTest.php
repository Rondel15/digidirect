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

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test class for create and update of company account.
 */
class CompanyAccountSuperUserTest extends GraphQlAbstract
{
    private const CREATE_COMPANY = <<<MUTATION
mutation {
  createCompany(
    input: {
      company_name: "The Company"
      company_email: "%company_email"
      legal_name: "The Company"
      vat_tax_id: "12345"
      reseller_id: "123"
      company_admin:   {
        email: "%admin_email"
        firstname: "Admin"
        lastname: "Company"
        gender: 1
        job_title: "Company Admin"
      }
      legal_address: {
        city: "Austin"
        country_id: US
        postcode: "12345"
        region: {
            region_id: 35
        }
        street: ["Street  123"]
        telephone: "0123456789"
      }
    }
  ) {
    company {
      id
      email
      company_admin {
        email
      }
    }
  }
}
MUTATION;

    #[
        DataFixture(Customer::class, as: 'customer'),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testGuestCreateCompanyAdminExistingCustomer(): void
    {
        $this->createCompanyVerifyAndDelete(
            'company' . rand() . '@example.com',
            DataFixtureStorageManager::getStorage()->get('customer')->getEmail()
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testCustomerCreateCompanyAdminSelf(): void
    {
        $customerEmail = DataFixtureStorageManager::getStorage()->get('customer')->getEmail();
        $this->createCompanyVerifyAndDelete(
            'company' . rand() . '@example.com',
            $customerEmail,
            Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)->execute($customerEmail)
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'customer2'),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testCustomerCreateCompanyAdminExistingCustomer(): void
    {
        $customerEmail = DataFixtureStorageManager::getStorage()->get('customer')->getEmail();
        $superUserEmail = DataFixtureStorageManager::getStorage()->get('customer2')->getEmail();

        $this->createCompanyVerifyAndDelete(
            'company' . rand() . '@example.com',
            $superUserEmail,
            Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)->execute($customerEmail)
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer2.id$',
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyUser(): void
    {
        $this->createCompanyVerifyAndDelete(
            'company' . rand() . '@example.com',
            DataFixtureStorageManager::getStorage()->get('customer2')->getEmail()
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer2.id$',
            ]
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyUserCompanyUser(): void
    {
        $this->createCompanyVerifyAndDelete(
            'company' . rand() . '@example.com',
            DataFixtureStorageManager::getStorage()->get('customer2')->getEmail(),
            Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)->execute(
                DataFixtureStorageManager::getStorage()->get('customer')->getEmail()
            )
        );
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testExistingSuperUser(): void
    {
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );

        $this->createCompanyVerifyAndDelete(
            'company' . rand() . '@example.com',
            DataFixtureStorageManager::getStorage()->get('customer')->getEmail()
        );
    }

    /**
     * Create company, verify response and delete company
     *
     * @param string $companyEmail
     * @param string $superUserEmail
     * @param array $header
     * @return void
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws GraphQlInputException
     */
    private function createCompanyVerifyAndDelete(
        string $companyEmail,
        string $superUserEmail,
        array $header = []
    ): void {
        $response = $this->graphQlMutation(
            strtr(
                self::CREATE_COMPANY,
                [
                    '%company_email' => $companyEmail,
                    '%admin_email' => $superUserEmail
                ]
            ),
            [],
            '',
            $header
        );

        $this->assertEquals($companyEmail, $response['createCompany']['company']['email']);
        $this->assertEquals($superUserEmail, $response['createCompany']['company']['company_admin']['email']);

        $uid = Bootstrap::getObjectManager()->get(Uid::class);
        $companyId = $uid->decode((string) $response['createCompany']['company']['id']);

        Bootstrap::getObjectManager()->get(CompanyRepositoryInterface::class)->deleteById($companyId);
    }
}
