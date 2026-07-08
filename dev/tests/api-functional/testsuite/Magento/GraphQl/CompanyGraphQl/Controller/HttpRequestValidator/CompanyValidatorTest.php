<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
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
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\CompanyGraphQl\Controller\HttpRequestValidator;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\User\Test\Fixture\User;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\Company\Test\Fixture\Company;

#[
    AppIsolation(true),
    Config('btob/website_configuration/company_active', 1),
    DataFixture(Customer::class, as: 'company_admin_a'),
    DataFixture(Customer::class, as: 'company_admin_b'),
    DataFixture(Customer::class, as: 'company_user_ab'),
    DataFixture(User::class, as: 'sales_rep'),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$company_admin_a.id$',
            CompanyInterface::NAME => 'company_a'
        ],
        'company_a'
    ),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$company_admin_b.id$',
            CompanyInterface::NAME => 'company_b'
        ],
        'company_b'
    ),
    DataFixture(
        AssignCompany::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user_ab.id$',
        ]
    ),
    DataFixture(
        AssignCompany::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user_ab.id$',
        ]
    ),
]
class CompanyValidatorTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var Uid
     */
    private $uidEncoder;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->uidEncoder = $objectManager->get(Uid::class);
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @param string $user
     * @param string $company
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @dataProvider inputDataProviderForTestCompanyValidator
     */
    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidator(string $user, string $companyName): void
    {
        $customer = FixtureManager::getStorage()->get($user);
        $company = FixtureManager::getStorage()->get($companyName);

        $response = $this->executeQuery(
            true,
            true,
            $customer->getEmail(),
            $company->getId()
        );

        $this->assertEquals($companyName, $response['company']['name']);
        $this->assertEquals($customer->getEmail(), $response['customer']['email']);
    }

    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidatorWithAbsentHeader(): void
    {
        $customer = FixtureManager::getStorage()->get('company_user_ab');
        $company = FixtureManager::getStorage()->get('company_a');

        $response = $this->executeQuery(
            false,
            true,
            $customer->getEmail(),
            (string) $company->getId()
        );

        $this->assertEquals('company_a', $response['company']['name']);
        $this->assertEquals($customer->getEmail(), $response['customer']['email']);
    }

    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidatorWithEmptyHeaderValue(): void
    {
        $customer = FixtureManager::getStorage()->get('company_user_ab');

        $response = $this->executeQuery(
            true,
            true,
            $customer->getEmail(),
            ''
        );

        $this->assertEquals('company_a', $response['company']['name']);
        $this->assertEquals($customer->getEmail(), $response['customer']['email']);
    }

    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidatorWithNegativeHeaderValue(): void
    {
        $customer = FixtureManager::getStorage()->get('company_user_ab');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Invalid company ID.'
        );

        $this->executeQuery(
            true,
            true,
            $customer->getEmail(),
            '-1'
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidatorUserDoNotHaveCompanyAccess(): void
    {
        $customer = FixtureManager::getStorage()->get('company_admin_b');
        $company = FixtureManager::getStorage()->get('company_a');

        $this->expectException(\Exception::class);
        $companyId = $this->uidEncoder->encode((string) $company->getId());
        $this->expectExceptionMessage(
            'Company with ID "'.$companyId.'" is not available.'
        );

        $this->executeQuery(
            true,
            true,
            $customer->getEmail(),
            $company->getId()
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidatorWithInvalidCompany(): void
    {
        $customer = FixtureManager::getStorage()->get('company_admin_b');
        $invalidCompanyId = '12345';

        $this->expectException(\Exception::class);
        $companyId = $this->uidEncoder->encode($invalidCompanyId);
        $this->expectExceptionMessage(
            'Company with ID "'.$companyId.'" is not available.'
        );

        $this->executeQuery(
            true,
            true,
            $customer->getEmail(),
            $invalidCompanyId
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCompanyValidatorWithNonLoggedInCustomer(): void
    {
        $customer = FixtureManager::getStorage()->get('company_admin_b');
        $company = FixtureManager::getStorage()->get('company_b');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $this->executeQuery(
            true,
            false,
            $customer->getEmail(),
            (string) $company->getId()
        );
    }

    /**
     * @return array[]
     */
    public function inputDataProviderForTestCompanyValidator(): array
    {
        return [
            'user_ab_with_company_a' => ['company_user_ab', 'company_a'],
            'user_ab_with_company_b' => ['company_user_ab', 'company_b'],
            'user_b_with_company_b' => ['company_admin_b', 'company_b']
        ];
    }

    /**
     * @param bool $isCompanyHeader
     * @param string $email
     * @param string $companyId
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function executeQuery(
        bool $isCompanyHeader,
        bool $isToken,
        string $email,
        string $companyId
    ): array {
        $header = [];

        if ($isCompanyHeader) {
            $header['X-Adobe-Company'] = $this->uidEncoder->encode($companyId);
        }

        if ($isToken) {
            $token = $this->customerTokenService
                ->createCustomerAccessToken($email, 'password');
            $header['Authorization'] = 'Bearer ' . $token;
        }

        $query = $this->getCustomerQuery();

        return $this->graphQlQuery(
            $query,
            [],
            '',
            $header
        );
    }

    /**
     * @return string
     */
    private function getCustomerQuery(): string
    {
        return <<<QUERY
{
    customer {
        firstname
        email
    }
    company {
      id
      email
      name
    }
}
QUERY;
    }
}
