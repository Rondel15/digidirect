<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company\Query;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany as AssignCompanyFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User as UserFixture;

/**
 * Test company admin email resolver
 */
class CompanyAdminEmailTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader|null
     */
    private ?GetCustomerAuthenticationHeader $customerTokenService = null;

    /**
     * @var Uid
     */
    private $encode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->encode = $objectManager->get(Uid::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(CustomerFixture::class, as: 'company_admin_ab'),
        DataFixture(CustomerFixture::class, as: 'company_admin_b'),
        DataFixture(CustomerFixture::class, as: 'company_customer_c'),
        DataFixture(CustomerFixture::class, as: 'company_customer_a'),
        DataFixture(UserFixture::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
                CompanyInterface::SUPER_USER_ID => '$company_admin_ab.id$'
            ],
            'company_a',
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
                CompanyInterface::SUPER_USER_ID => '$company_admin_b.id$'
            ],
            'company_b',
        ),
        DataFixture(
            AssignCompanyFixture::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_customer_c.id$',
            ]
        ),
        DataFixture(
            AssignCompanyFixture::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_customer_c.id$',
            ]
        ),
        DataFixture(
            AssignCompanyFixture::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_customer_a.id$'
            ]
        )
    ]
    /**
     * @dataProvider isEmailAvailableDataProvider
     */
    public function testCompanyAdminEmailValid(
        bool $isEmailAvailable,
        string $email = '',
        string $customer = '',
        string $companyContext = ''
    ): void {
        $query = $this->getQuery(
            !empty($email) ? $email : DataFixtureStorageManager::getStorage()->get($customer)->getEmail()
        );
        $loginEmail = DataFixtureStorageManager::getStorage()->get('company_customer_c')->getEmail();
        $header = $this->customerTokenService->execute($loginEmail, 'password');

        if ($companyContext) {
            $companyId = DataFixtureStorageManager::getStorage()->get($companyContext)->getId();
            $header['X-Adobe-Company'] = $this->encode->encode((string)$companyId);
        }

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $header,
        );
        self::assertEquals($isEmailAvailable, $response['isCompanyAdminEmailAvailable']['is_email_available']);
    }

    /**
     * @return array[]
     */
    public function isEmailAvailableDataProvider(): array
    {
        return [
            'emailAvailable' => [
                'isEmailAvailable' => true,
                'email' => 'test@test.com',
                'customer' => '',
                'companyContext' => ''
            ],
            'emailIsNotAvailable' => [
                'isEmailAvailable' => false,
                'email' => '',
                'customer' => 'company_admin_ab',
                'companyContext' => ''
            ],
            'emailAvailable_customer_a' => [
                'isEmailAvailable' => true,
                'email' => '',
                'customer' => 'company_customer_a',
                'companyContext' => 'company_b'
            ],
            'emailAvailable_customer_a_default_company' => [
                'isEmailAvailable' => true,
                'email' => '',
                'customer' => 'company_customer_a',
                'companyContext' => ''
            ],
            'emailNotAvailable_company_admin_b' => [
                'isEmailAvailable' => false,
                'email' => '',
                'customer' => 'company_admin_b',
                'companyContext' => 'company_a'
            ],
            'emailNotAvailable_company_admin_b_default_company' => [
                'isEmailAvailable' => false,
                'email' => '',
                'customer' => 'company_admin_b',
                'companyContext' => ''
            ]
        ];
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(
            CustomerFixture::class,
            [
                'password' => 'password',
            ],
            'customer',
        ),
    ]
    /**
     * @dataProvider testFailureDataProvider
     */
    public function testCompanyAdminEmailCheckerFailures(string $email, string $expectedMessage, bool $login): void
    {
        $query = $this->getQuery($email);
        $header = ($login) ? $this->customerTokenService->execute(
            DataFixtureStorageManager::getStorage()->get('customer')->getEmail(),
            'password'
        ) : [];

        try {
            $this->graphQlQuery(
                $query,
                [],
                '',
                $header,
            );
            self::fail('Response should contains errors.');
        } catch (ResponseContainsErrorsException $e) {
            $responseData = $e->getResponseData();
            self::assertEquals($expectedMessage, $responseData['errors'][0]['message']);
        }
    }

    public function testFailureDataProvider(): array
    {
        return [
            'email_missing' => [
                'email' => '',
                'expectedMessage' => 'Field "isCompanyAdminEmailAvailable" argument "email" of type "String!" ' .
                    'is required but not provided.',
                'login' => true
            ],
            'unauthorized' => [
                'email' => 'customer@example.com',
                'expectedMessage' => 'The current customer isn\'t authorized.',
                'login' => false
            ],
            'email_invalid' => [
                'email' => 'customer@example',
                'expectedMessage' => 'Invalid value of "customer@example" provided for the email field.',
                'login' => true
            ],
        ];
    }

    #[
        Config('btob/website_configuration/company_active', 0),
        DataFixture(
            CustomerFixture::class,
            [
                'password' => 'password',
            ],
            'customer',
        ),
    ]
    public function testCompanyInActive(): void
    {
        $expectedMessage = 'Company feature is not available.';
        $query = $this->getQuery('admin@magento.com');

        try {
            $customerEmail = DataFixtureStorageManager::getStorage()->get('customer')->getEmail();
            $this->graphQlQuery(
                $query,
                [],
                '',
                $this->customerTokenService->execute($customerEmail, 'password'),
            );
            self::fail('Response should contains errors.');
        } catch (ResponseContainsErrorsException $e) {
            $responseData = $e->getResponseData();
            self::assertEquals($expectedMessage, $responseData['errors'][0]['message']);
        }
    }

    /**
     * @param $email
     * @return string
     */
    private function getQuery($email)
    {
        $emailInput = ($email) ? "(email: \"$email\")" : "";
        return <<<QUERY
{
    isCompanyAdminEmailAvailable $emailInput {
      is_email_available
    }
}
QUERY;
    }
}
