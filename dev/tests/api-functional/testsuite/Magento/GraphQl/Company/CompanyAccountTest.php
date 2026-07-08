<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2020 Adobe
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

namespace Magento\GraphQl\Company;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\ResourceModel\Company as CompanyResource;
use Magento\Company\Model\Company;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Test class for create and update of company account.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyAccountTest extends GraphQlAbstract
{
    private const VALID_HEADER = 1;
    private const INVALID_HEADER = 2;
    private const NO_HEADER = 3;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /** @var CompanyResource */
    private $companyResource;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Uid */
    private $uid;

    /** @var CustomerTokenServiceInterface */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
        $this->getCustomerAuthenticationHeader = $this->objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->companyResource = $this->objectManager->get(CompanyResource::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->uid = Bootstrap::getObjectManager()->get(Uid::class);
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test if company feature is activated.
     *
     * @magentoConfigFixture default_store btob/website_configuration/company_active 0
     */
    public function testConfigCompanyActive()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Company is not enabled or registration not allowed.');
        $mutationQuery = <<<MUTATION
mutation {
  createCompany(
    input: {
      company_name: "Company"
      company_email: "email@magento.com"
      legal_name: "Legalname"
      vat_tax_id: "12345"
      reseller_id: "123"
      company_admin:   {
        email: "admin@magento.com"
        firstname: "Company"
        lastname: "Admin"
        gender: 1
        job_title: "Manager"
        telephone: "12345"
      }
      legal_address: {
        city: "City"
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
    }
  }
}
MUTATION;

        $this->graphQlMutation($mutationQuery);
    }

    /**
     * Test if company registration is enabled.
     *
     * @magentoConfigFixture default_store company/general/allow_company_registration 0
     */
    public function testConfigAllowCompanyRegistration()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Company is not enabled or registration not allowed.');
        $mutationQuery = <<<MUTATION
mutation {
  createCompany(
    input: {
      company_name: "Company"
      company_email: "email@magento.com"
      legal_name: "Legalname"
      vat_tax_id: "12345"
      reseller_id: "123"
      company_admin:   {
        email: "admin@magento.com"
        firstname: "Company"
        lastname: "Admin"
        gender: 1
        job_title: "Manager"
        telephone: "12345"
      }
      legal_address: {
        city: "City"
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
    }
  }
}
MUTATION;

        $this->graphQlMutation($mutationQuery);
    }

    /**
     * Test creation of company.
     *
     * @dataProvider createCompanyAccountDataProvider
     * @param int $companyHeader
     * @param int $tokenHeader
     * @param string|null $exceptionMsg
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('company/general/allow_company_registration', 1),
        DataFixture('Magento/CustomerCustomAttributes/_files/customer_attribute_type_select.php'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer.id$',
            ],
            'company'
        )
    ]
    public function testCreateCompanyAccount(
        int $companyHeader,
        int $tokenHeader,
        ?string $exceptionMsg = null
    ): void {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        /** @var CompanyInterface $company */
        $company = DataFixtureStorageManager::getStorage()->get('company');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');
        $headers = [];
        if ($companyHeader == self::VALID_HEADER) {
            $headers = array_merge($headers, ['X-Adobe-Company' => $this->uid->encode((string)$company->getId())]);
        } elseif ($companyHeader == self::INVALID_HEADER) {
            $headers = array_merge($headers, ['X-Adobe-Company' => $this->uid->encode('999999')]);
        }
        if ($tokenHeader == self::VALID_HEADER) {
            $headers = array_merge($headers, ['Authorization' => 'Bearer ' . $customerToken]);
        } elseif ($tokenHeader == self::INVALID_HEADER) {
            $headers = array_merge($headers, ['Authorization' => 'Bearer invalidtoken']);
        }

        $companyEmail = "company" . (string)random_int(100000, 999999) . "@example.com";
        $legalName = "Legal name";
        $companyName = "Company name";
        $adminEmail = "company_user" . (string)random_int(100000, 999999) . "@example.com";
        $adminName = "Admin";
        $postcode = "12345";
        $city = "Example city";
        $jobTitle = "Manager";
        $telephone = "12345";

        $attributeRepository = $this->objectManager->get(AttributeRepositoryInterface::class);
        $attribute = $attributeRepository->get(CustomerModel::ENTITY, 'customer_attribute_type_select');
        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $attributeOption = array_last($attribute->getOptions());

        $mutationQuery = <<<MUTATION
mutation {
  createCompany(
    input: {
      company_name: "{$companyName}"
      company_email: "{$companyEmail}"
      legal_name: "{$legalName}"
      vat_tax_id: "12345"
      reseller_id: "123"
      company_admin:   {
        email: "{$adminEmail}"
        firstname: "{$adminName}"
        lastname: "Company"
        gender: 1
        job_title: "{$jobTitle}"
        telephone: "{$telephone}"
        custom_attributes: [
          {
            attribute_code: "{$attribute->getAttributeCode()}"
            value: "{$attributeOption->getId()}"
          }
        ]
      }
      legal_address: {
        city: "{$city}"
        country_id: US
        postcode: "{$postcode}"
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
      name
      legal_name
      vat_tax_id
      reseller_id
      company_admin {
        email
        firstname
        lastname
        gender
        job_title
        telephone
      }
      legal_address {
        street
        city
        postcode
        country_code
        telephone
        region {
          region_code
          region_id
          region
        }
      }
    }
  }
}
MUTATION;

        if ($exceptionMsg) {
            $this->expectException(ResponseContainsErrorsException::class);
            $this->expectExceptionMessage($exceptionMsg);
        }

        $result = $this->graphQlMutation(
            $mutationQuery,
            [],
            '',
            $headers
        );

        if (!$exceptionMsg) {
            $this->assertNotEmpty($result['createCompany']['company']);
            $this->assertNotEmpty($result['createCompany']['company']['id']);
            $this->assertEquals($result['createCompany']['company']['email'], $companyEmail);
            $this->assertEquals($result['createCompany']['company']['legal_name'], $legalName);
            $this->assertEquals($result['createCompany']['company']['name'], $companyName);
            $this->assertEquals($result['createCompany']['company']['company_admin']['email'], $adminEmail);
            $this->assertEquals($result['createCompany']['company']['company_admin']['firstname'], $adminName);
            $this->assertEquals($result['createCompany']['company']['company_admin']['job_title'], $jobTitle);
            $this->assertEquals($result['createCompany']['company']['company_admin']['telephone'], $telephone);
            $this->assertEquals($result['createCompany']['company']['legal_address']['postcode'], $postcode);
            $this->assertEquals($result['createCompany']['company']['legal_address']['city'], $city);
            $this->assertEquals($result['createCompany']['company']['legal_address']['region']['region_code'], 'MS');
        }
        $this->deleteCompany($companyEmail, $adminEmail);
    }

    /*
     * @return array[]
     */
    public function createCompanyAccountDataProvider(): array
    {
        return [
            [self::NO_HEADER, self::NO_HEADER, null],
            [self::NO_HEADER, self::VALID_HEADER, null],
            [self::NO_HEADER, self::INVALID_HEADER, null],
            [self::VALID_HEADER, self::VALID_HEADER, null],
            // The following nagative cases are caught by generic graphql `CompanyValidator` which
            // apply to all graphql operations. It validates that:
            // - Company header is allowed only for customers
            // - Access to company is allowed
            [self::VALID_HEADER, self::NO_HEADER, 'The current customer isn\'t authorized.'],
            [self::VALID_HEADER, self::INVALID_HEADER, 'The current customer isn\'t authorized.'],
            [self::INVALID_HEADER, self::NO_HEADER, 'The current customer isn\'t authorized.'],
            [self::INVALID_HEADER, self::VALID_HEADER, 'Company with ID "OTk5OTk5" is not available'],
            [self::INVALID_HEADER, self::INVALID_HEADER, 'The current customer isn\'t authorized.'],
        ];
    }

    /**
     * Test if company account exists.
     *
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture default_store btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store company/general/allow_company_registration 1
     * @magentoDbIsolation disabled
     */
    public function testCreateCompanyAccountExistingEmail()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );
        $mutationQuery = <<<MUTATION
mutation {
  createCompany(
    input: {
      company_name: "Company"
      company_email: "email@magento.com"
      legal_name: "Legalname"
      vat_tax_id: "12345"
      reseller_id: "123"
      company_admin:   {
        email: "admin@magento.com"
        firstname: "Company"
        lastname: "Admin"
        gender: 1
        job_title: "Manager"
        telephone: "12345"
      }
      legal_address: {
        city: "City"
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
    }
  }
}
MUTATION;

        $this->graphQlMutation($mutationQuery);
    }

    /**
     * Test customer access for updating company.
     *
     * @magentoConfigFixture btob/website_configuration/company_active 1
     * @magentoConfigFixture default_store company/general/allow_company_registration 1
     */
    public function testCustomerAccessForUpdateCompany()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            'Customer is not a company user.'
        );
        $mutationQuery = <<<MUTATION
mutation {
  updateCompany(
    input: {
      company_email: "company@example.com",
    }) {
    company {
      id
      email
      name
    }
  }
}
MUTATION;
        $this->graphQlMutation($mutationQuery);
    }

    /**
     * Test for updating of company.
     *
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     * @magentoDbIsolation disabled
     */
    public function testUpdateCompanyAccount()
    {
        $companyEmail = "company@example.com";
        $companyName = "Company name updated";
        $legalName = "Legal name updated";
        $postcode = "12346";
        $city = "Example city updated";
        $street = "New Street  123";
        $telephone = "0121221211211";
        $adminEmail = "admin@magento.com";
        $this->approveCompany();
        $mutationQuery = <<<MUTATION
mutation {
  updateCompany(
    input: {
      company_name: "{$companyName}",
      company_email: "{$companyEmail}",
      legal_name: "{$legalName}",
      vat_tax_id: "1212111",
      reseller_id: "13311",
      legal_address: {
        city: "{$city}",
        country_id: US,
        postcode: "{$postcode}",
        region: {
          region_code: "MN",
          region_id: 34
        },
        street: ["{$street}"],
        telephone: "0121221211211"
      }
    }) {
    company {
      id
      email
      name
      legal_name
      vat_tax_id
      reseller_id
      legal_address {
        street
        city
        postcode
        country_code
        telephone
        region {
          region_code
          region_id
          region
        }
      }
    }
  }
}
MUTATION;
        $result = $this->graphQlMutation(
            $mutationQuery,
            [],
            '',
            $this->getCustomerHeader($adminEmail)
        );
        $this->assertNotEmpty($result['updateCompany']['company']);
        $this->assertNotEmpty($result['updateCompany']['company']['id']);
        $this->assertEquals($result['updateCompany']['company']['email'], $companyEmail);
        $this->assertEquals($result['updateCompany']['company']['legal_name'], $legalName);
        $this->assertEquals($result['updateCompany']['company']['name'], $companyName);
        $this->assertEquals($result['updateCompany']['company']['legal_address']['postcode'], $postcode);
        $this->assertEquals($result['updateCompany']['company']['legal_address']['city'], $city);
        $this->assertEquals($result['updateCompany']['company']['legal_address']['telephone'], $telephone);
        $this->assertEquals($result['updateCompany']['company']['legal_address']['region']['region_code'], 'MN');
    }

    /**
     * Test access of guest customer for updating company.
     *
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testUpdateCompanyAsGuest()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Customer is not a company user.');
        $mutationQuery = <<<MUTATION
mutation {
  updateCompany(
    input: {
      company_name: "Company",
      company_email: "email@magento.com",
      legal_name: "Legal name",
      vat_tax_id: "1212111",
      reseller_id: "13311",
      legal_address: {
        city: "City",
        country_id: US,
        postcode: "12345",
        region: {
          region_code: "MN",
          region_id: 34
        },
        street: ["Street 1"],
        telephone: "0121221211211"
      }
    }) {
    company {
      id
      email
    }
  }
}
MUTATION;
        $this->graphQlMutation(
            $mutationQuery,
            [],
            '',
            []
        );
    }

    /**
     * Test access of non company customer for updating company.
     *
     * @magentoApiDataFixture Magento/Company/_files/company.php
     * @magentoConfigFixture btob/website_configuration/company_active 1
     */
    public function testUpdateCompanyAsNonCompanyUser()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Customer is not a company user.');
        $customer = $this->createNonCompanyCustomer($password = 'SomePassword123');

        $mutationQuery = <<<MUTATION
mutation {
  updateCompany(
    input: {
      company_name: "Company",
      company_email: "email@magento.com",
      legal_name: "Legal name",
      vat_tax_id: "1212111",
      reseller_id: "13311",
      legal_address: {
        city: "City",
        country_id: US,
        postcode: "12345",
        region: {
          region_code: "MN",
          region_id: 34
        },
        street: ["Street 1"],
        telephone: "0121221211211"
      }
    }) {
    company {
      id
      email
    }
  }
}
MUTATION;
        $this->graphQlMutation(
            $mutationQuery,
            [],
            '',
            ['Authorization' => 'Bearer ' .
                $this->customerTokenService->createCustomerAccessToken($customer['email'], $password)]
        );
    }

    /**
     * Get http header with customer access token.
     *
     * @param string $email
     * @return string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws AuthenticationException
     * @throws InputException
     */
    private function getCustomerHeader($email)
    {
        return $this->getCustomerAuthenticationHeader->execute($email, 'password');
    }

    /**
     * Update company status before login.
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function approveCompany()
    {
        $company = $this->objectManager->get(Company::class);
        $this->companyResource->load($company, 'company@example.com', 'company_email');
        $company->setStatus(CompanyInterface::STATUS_APPROVED);
        $this->companyResource->save($company);
    }

    /**
     * Clear company data.
     *
     * @param string $companyEmail
     * @param string $customerEmail
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    private function deleteCompany($companyEmail, $customerEmail)
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $company = $this->objectManager->get(Company::class);
        $this->companyResource->load($company, $companyEmail, 'company_email');
        $this->companyRepository->deleteById($company->getId());

        $customer = $this->customerRepository->get($customerEmail);
        $this->customerRepository->delete($customer);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * Create new random customer.
     *
     * @param string $password Customer's password.
     * @return array New customer data.
     */
    private function createNonCompanyCustomer(string $password = 'Test123'): array
    {
        $newFirstname = 'John';
        $newLastname = 'Smith';
        $newEmail = 'new_random_customer' . random_int(1000, 9999) . '@magento.com';

        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$password}"
            is_subscribed: false
        }
    ) {
        customer {
            id
            email
            created_at
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);

        return $response['createCustomerV2']['customer'];
    }
}
