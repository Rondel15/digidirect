<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
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

namespace Magento\CompanyCustomer\Api;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\CompanyContextInterface;
use Magento\Company\Model\Customer\CompanyUserHydrator;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;

#[
    DataFixture(Customer::class, as: 'company_admin_a'),
    DataFixture(Customer::class, as: 'company_admin_b'),
    DataFixture(Customer::class, as: 'company_user'),
    DataFixture(User::class, as: 'sales_rep'),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$company_admin_a.id$',
            CompanyInterface::NAME => 'Company A'
        ],
        'company_a'
    ),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$company_admin_b.id$',
            CompanyInterface::NAME => 'Company B'
        ],
        'company_b'
    ),
    DataFixture(
        AssignCompany::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_admin_a.id$',
            CompanyCustomerInterface::JOB_TITLE => 'Job A company_admin_a',
            CompanyCustomerInterface::TELEPHONE => '11111111',
        ]
    ),
    DataFixture(
        AssignCompany::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_admin_a.id$',
            CompanyCustomerInterface::JOB_TITLE => 'Job B company_admin_a',
            CompanyCustomerInterface::TELEPHONE => '12121212',
        ]
    ),
    DataFixture(
        AssignCompany::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user.id$',
            CompanyCustomerInterface::JOB_TITLE => 'Job A company_user',
            CompanyCustomerInterface::TELEPHONE => '21212121',
        ]
    ),
    DataFixture(
        AssignCompany::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user.id$',
            CompanyCustomerInterface::JOB_TITLE => 'Job B company_user',
            CompanyCustomerInterface::TELEPHONE => '22222222',
        ]
    ),
]
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementMeTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/customers/me';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->dataObjectProcessor = $objectManager->get(DataObjectProcessor::class);
        $this->httpContext = $objectManager->get(Context::class);
    }

    /**
     * @dataProvider getCustomerMeDataProvider
     * @param string $reqCustomer
     * @param string|null $reqCompany
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testGetCustomerMe(string $reqCustomer, ?string $reqCompany = null): void
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get($reqCustomer);

        if ($reqCompany) {
            $companyId = DataFixtureStorageManager::getStorage()->get($reqCompany)->getId();
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$companyId, 0);
        }

        // Get expected customer data from the Service
        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerRepository->getById($customer->getId()),
            CustomerInterface::class
        );

        $token = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token,
            ]
        ];

        if ($reqCompany) {
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $companyId];
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, 0, 0);
        }

        $response = $this->_webApiCall($serviceInfo, []);

        $this->assertEquals($customerDataArray, $response);
    }

    /**
     * @return array
     */
    public function getCustomerMeDataProvider(): array
    {
        return [
            ['company_admin_a', 'company_a'],
            ['company_admin_a', 'company_b'],
            ['company_admin_a', null],
            ['company_user', 'company_a'],
            ['company_user', 'company_b'],
            ['company_user', null],
        ];
    }

    /**
     * @dataProvider updateCustomerMeDataProvider
     * @param string $reqCustomer
     * @param string $expectedCompany
     * @param bool $isDefault
     * @param string $expectedJobTitle
     * @param string|null $companyInHeader
     * @param string|null $companyInParam
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testUpdateCustomerMe(
        string  $reqCustomer,
        string  $expectedCompany,
        bool    $isDefault,
        string  $expectedJobTitle,
        ?string $companyInHeader = null,
        ?string $companyInParam = null
    ): void {
        $serviceInfo = [];
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get($reqCustomer);

        $expectedCompanyId = DataFixtureStorageManager::getStorage()->get($expectedCompany)->getId();
        $companyIdInParam = null;
        $companyIdInHeader = null;
        if ($companyInParam) {
            $companyIdInParam = DataFixtureStorageManager::getStorage()->get($companyInParam)->getId();
        }
        if ($companyInHeader) {
            $companyIdInHeader = DataFixtureStorageManager::getStorage()->get($companyInHeader)->getId();
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $companyIdInHeader];
        }

        if (!$isDefault) {
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$companyIdInHeader, 0);
        }

        // Get update customer data from the Service
        $updateCustomerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerRepository->getById($customer->getId()),
            CustomerInterface::class
        );

        if ($companyIdInParam) {
            $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
            [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID]  = $companyIdInParam;
        } else {
            unset($updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
                [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID]);
        }
        $updateCustomerDataArray[CustomerInterface::FIRSTNAME] .= 'NEW';
        $updateCustomerDataArray[CustomerInterface::LASTNAME] .= 'NEW';
        $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
        [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::JOB_TITLE] .= ' NEW';

        $requestData = ['customer' => $updateCustomerDataArray];

        $token = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );

        $serviceInfo = array_merge_recursive($serviceInfo, [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_PUT,
                'token' => $token,
            ]
        ]);

        // Reset company context
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, 0, 0);

        $response = $this->_webApiCall($serviceInfo, $requestData);

        unset($updateCustomerDataArray[CustomerInterface::UPDATED_AT]);
        unset($response[CustomerInterface::UPDATED_AT]);
        $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
            [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID] = $expectedCompanyId;
        $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
        [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::JOB_TITLE] = $expectedJobTitle;

        $this->assertEquals($updateCustomerDataArray, $response);
    }

    /**
     * @return array
     */
    public function updateCustomerMeDataProvider(): array
    {
        return [
            // Set company id only in request header
            ['company_admin_a', 'company_a', true, 'Job A company_admin_a NEW', 'company_a', null],
            ['company_admin_a', 'company_b', false, 'Job B company_admin_a NEW', 'company_b', null],
            ['company_user', 'company_a',  true, 'Job A company_user NEW', 'company_a', null],
            ['company_user', 'company_b', false, 'Job B company_user NEW', 'company_b', null],
            // Set company id only in request parameter
            // For V1/customers/me PUT operation, company id in parameter will always be ignored
            ['company_admin_a', 'company_a',  true, 'Job A company_admin_a NEW', null, 'company_a'],
            ['company_admin_a', 'company_a',  false, 'Job A company_admin_a NEW', null, 'company_b'],
            ['company_user', 'company_a',  true, 'Job A company_user NEW', null, 'company_a'],
            ['company_user', 'company_a',  true, 'Job A company_user NEW', null, 'company_b'],
            // Set company id in both request header and request parameter
            ['company_admin_a', 'company_a',  true, 'Job A company_admin_a NEW', 'company_a', 'company_a'],
            ['company_admin_a', 'company_b', false, 'Job B company_admin_a NEW', 'company_b', 'company_b'],
            ['company_admin_a', 'company_a',  true, 'Job A company_admin_a NEW', 'company_a', 'company_b'],
            ['company_admin_a', 'company_b', false, 'Job B company_admin_a NEW', 'company_b', 'company_a'],
            ['company_user', 'company_a',  true, 'Job A company_user NEW', 'company_a', 'company_a'],
            ['company_user', 'company_b', false, 'Job B company_user NEW', 'company_b', 'company_b'],
            ['company_user', 'company_a',  true, 'Job A company_user NEW', 'company_a', 'company_b'],
            ['company_user', 'company_b', false, 'Job B company_user NEW', 'company_b', 'company_a'],
            // Test no company id is set
            ['company_admin_a', 'company_a',  true, 'Job A company_admin_a NEW', null, null],
            ['company_user', 'company_a',  true, 'Job A company_user NEW', null, null],
        ];
    }
}
