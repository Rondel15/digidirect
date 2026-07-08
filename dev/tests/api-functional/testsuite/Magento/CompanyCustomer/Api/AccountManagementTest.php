<?php
/************************************************************************
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
 ***********************************************************************/
declare(strict_types=1);

namespace Magento\CompanyCustomer\Api;

use Magento\Authorization\Test\Fixture\Role as RoleFixture;
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
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Test\Fixture\User;

#[
    DataFixture(Customer::class, as: 'company_admin_a'),
    DataFixture(Customer::class, as: 'company_admin_b'),
    DataFixture(Customer::class, as: 'company_user'),
    DataFixture(RoleFixture::class, as: 'adminRole'),
    DataFixture(User::class, ['role_id' => '$adminRole.id$'], 'sales_rep'),
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
class AccountManagementTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/customers';
    private const NO_COMPANY_CONTEXT = 0;

    /**
     * @var string
     */
    private $adminToken;

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
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        if (method_exists($this, 'name')) {
            // phpstan:ignore
            $name = $this->name();
        } else {
            // phpstan:ignore
            $name = $this->getName();
        }
        $this->customerHelper = new CustomerHelper($name);
        $objectManager = Bootstrap::getObjectManager();
        /** @var UserInterface $admin */
        $admin = DataFixtureStorageManager::getStorage()->get('sales_rep');
        $this->adminToken = $objectManager->get(AdminTokenServiceInterface::class)->createAdminAccessToken(
            $admin->getUserName(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
        );
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->dataObjectProcessor = $objectManager->get(DataObjectProcessor::class);
        $this->httpContext = $objectManager->get(Context::class);
    }

    /**
     * @dataProvider createCustomerDataProvider
     * @param string|null $companyInHeader
     * @param string|null $companyInParam
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testCreateCustomer(
        ?string $companyInHeader = null,
        ?string $companyInParam = null
    ): void {
        $serviceInfo = [];
        $companyIdInParam = null;
        $companyIdInHeader = null;
        if ($companyInParam) {
            $companyIdInParam = DataFixtureStorageManager::getStorage()->get($companyInParam)->getId();
        }
        if ($companyInHeader) {
            $companyIdInHeader = DataFixtureStorageManager::getStorage()->get($companyInHeader)->getId();
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $companyIdInHeader];
        }

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerHelper->createSampleCustomerDataObject(
                $this->getCustomerCompanyExtensionAttribute((string)$companyIdInParam)
            ),
            CustomerInterface::class
        );

        unset($customerDataArray['store_id']);
        unset($customerDataArray['website_id']);

        $serviceInfo = array_merge_recursive($serviceInfo, [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ]);

        $requestData = ['customer' => $customerDataArray, 'password' => CustomerHelper::PASSWORD];

        // Reset company context
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, 0, 0);

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertNotNull($response['id']);
        $this->assertEquals($customerDataArray['email'], $response['email']);
        $this->assertEquals($customerDataArray['firstname'], $response['firstname']);
        $this->assertEquals($customerDataArray['lastname'], $response['lastname']);

        $this->assertEquals(
            $companyIdInHeader ?: self::NO_COMPANY_CONTEXT,
            $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY][CompanyUserHydrator::COMPANY_ATTRIBUTES]
            [CompanyCustomerInterface::COMPANY_ID]
        );
    }

    /**
     * @return array
     */
    public function createCustomerDataProvider(): array
    {
        return [
            // Test no company id is set
            [null, null],
            // Set company id only in request header
            ['company_a', null],
            // Set company id only in request parameter
            // For V1/customers POST operation, company id in parameter will always be ignored
            [null, 'company_b'],
            // Set company id in both request header and request parameter
            ['company_a', 'company_a'],
            ['company_a', 'company_b'],
        ];
    }

    /**
     * @dataProvider getCustomerDataProvider
     * @param string $customerInReq
     * @param string|null $companyInHeader
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testGetCustomer(string $customerInReq, ?string $companyInHeader = null): void
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get($customerInReq);

        if ($companyInHeader) {
            $companyId = DataFixtureStorageManager::getStorage()->get($companyInHeader)->getId();
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$companyId, 0);
        }

        // Get customer data from the Service
        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerRepository->getById($customer->getId()),
            CustomerInterface::class
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customer->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $this->adminToken,
            ]
        ];

        if ($companyInHeader) {
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $companyId];
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, 0, 0);
        }

        $response = $this->_webApiCall($serviceInfo, []);

        $this->assertEquals($customerDataArray, $response);
    }

    /**
     * @return array
     */
    public function getCustomerDataProvider(): array
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
     * @dataProvider updateCustomerDataProvider
     * @param string $customerInReq
     * @param string $jobTitle
     * @param string|null $companyInHeader
     * @param string|null $companyInParam
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    #[
        Config('btob/website_configuration/company_active', 1)
    ]
    public function testUpdateCustomer(
        string  $customerInReq,
        string  $expectedCompany,
        string  $expectedJobTitle,
        ?string $companyInHeader = null,
        ?string $companyInParam = null
    ): void {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get($customerInReq);

        $expectedCompanyId = DataFixtureStorageManager::getStorage()->get($expectedCompany)->getId();
        $companyIdInParam = null;
        $companyIdInHeader = null;
        // For operations other than /V1/customers POST and /V1/customers/me PUT, if company_id is explicitly defined
        // via parameter, the parameter value is used as the company context, not the header value.
        if ($companyInParam) {
            $companyIdInParam = DataFixtureStorageManager::getStorage()->get($companyInParam)->getId();
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$companyIdInParam, 0);
        } elseif ($companyInHeader) {
            $companyIdInHeader = DataFixtureStorageManager::getStorage()->get($companyInHeader)->getId();
            $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$companyIdInHeader, 0);
        }

        // Get update customer data from the Service
        $updateCustomerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $this->customerRepository->getById($customer->getId()),
            CustomerInterface::class
        );

        if ($companyIdInParam) {
            $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
            [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID] = $companyIdInParam;
        } elseif (isset($updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
            [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID])) {
                unset($updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
                    [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID]);
        }
        $updateCustomerDataArray[CustomerInterface::FIRSTNAME] .= 'NEW';
        $updateCustomerDataArray[CustomerInterface::LASTNAME] .= 'NEW';
        $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
        [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::JOB_TITLE] .= ' NEW';
        $requestData = ['customer' => $updateCustomerDataArray];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $customer->getId(),
                'httpMethod' => Request::HTTP_METHOD_PUT,
                'token' => $this->adminToken,
            ]
        ];

        if ($companyIdInHeader) {
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $companyIdInHeader];
        }
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, 0, 0);

        $response = $this->_webApiCall($serviceInfo, $requestData);

        if (isset($updateCustomerDataArray[CustomerInterface::UPDATED_AT])) {
            unset($updateCustomerDataArray[CustomerInterface::UPDATED_AT]);
        }
        if (isset($response[CustomerInterface::UPDATED_AT])) {
            unset($response[CustomerInterface::UPDATED_AT]);
        }
        $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
        [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::COMPANY_ID] = $expectedCompanyId;
        $updateCustomerDataArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
        [CompanyUserHydrator::COMPANY_ATTRIBUTES][CompanyCustomerInterface::JOB_TITLE] = $expectedJobTitle;

        $this->assertEquals($updateCustomerDataArray, $response);
    }

    /**
     * @return array
     */
    public function updateCustomerDataProvider(): array
    {
        return [
            // Test company context in request header
            ['company_admin_a', 'company_a', 'Job A company_admin_a NEW', 'company_a', null],
            ['company_admin_a', 'company_b', 'Job B company_admin_a NEW', 'company_b', null],
            ['company_user', 'company_a', 'Job A company_user NEW', 'company_a', null],
            ['company_user', 'company_b', 'Job B company_user NEW', 'company_b', null],
            // Test company context in request parameter
            ['company_admin_a', 'company_a', 'Job A company_admin_a NEW', null, 'company_a'],
            ['company_admin_a', 'company_b', 'Job B company_admin_a NEW', null, 'company_b'],
            ['company_user', 'company_a', 'Job A company_user NEW', null, 'company_a'],
            ['company_user', 'company_b', 'Job B company_user NEW', null, 'company_b'],
            // Test company context in both request header and request parameter
            ['company_admin_a', 'company_b', 'Job B company_admin_a NEW', 'company_b', 'company_b'],
            ['company_admin_a', 'company_b', 'Job B company_admin_a NEW', 'company_a', 'company_b'],
            ['company_admin_a', 'company_a', 'Job A company_admin_a NEW', 'company_b', 'company_a'],
            ['company_admin_a', 'company_a', 'Job A company_admin_a NEW', 'company_a', 'company_a'],
            ['company_user', 'company_b', 'Job B company_user NEW', 'company_b', 'company_b'],
            ['company_user', 'company_b', 'Job B company_user NEW', 'company_a', 'company_b'],
            ['company_user', 'company_a', 'Job A company_user NEW', 'company_b', 'company_a'],
            ['company_user', 'company_a', 'Job A company_user NEW', 'company_a', 'company_a'],
            ['company_admin_a', 'company_a', 'Job A company_admin_a NEW', null, null],
            ['company_user', 'company_a', 'Job A company_user NEW', null, null],
        ];
    }

    /**
     * @return array
     */

    private function getCustomerCompanyExtensionAttribute(string $companyId): array
    {
        return [ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => [
                    CompanyUserHydrator::COMPANY_ATTRIBUTES => [CompanyCustomerInterface::COMPANY_ID => $companyId]
                ]
            ];
    }
}
