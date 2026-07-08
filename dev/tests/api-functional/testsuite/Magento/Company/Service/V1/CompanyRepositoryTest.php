<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company\Service\V1;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyInterfaceFactory;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test Company CRUD operations.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyRepositoryTest extends WebapiAbstract
{
    public const SERVICE_READ_NAME = 'companyCompanyRepositoryV1';

    public const SERVICE_VERSION = 'V1';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var array
     */
    private $fieldsToCheck = [
        'status',
        'company_name',
        'company_email',
        'comment',
        'customer_group_id',
        'sales_representative_id',
        'super_user_id'
    ];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
        $this->companyManagement = $this->objectManager->get(CompanyManagementInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(DataObjectProcessor::class);

        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(CustomerGroup::class, as: 'group'),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testCreateCompany()
    {
        $storage = DataFixtureStorageManager::getStorage();
        $customerId = (int) $storage->get('customer')->getId();
        $adminId = (int) $storage->get('admin')->getId();
        $groupId = (int) $storage->get('group')->getId();

        $companyFactory = $this->objectManager->get(CompanyInterfaceFactory::class);
        /** @var CompanyInterface $company */
        $company = $companyFactory->create();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'Save',
            ],
        ];

        $company->setCompanyName('company');
        $company->setStatus(1);
        $company->setCompanyEmail(time() . '@example.com');
        $company->setComment('comment');
        $company->setSuperUserId($customerId);
        $company->setSalesRepresentativeId($adminId);
        $company->setCustomerGroupId($groupId);
        $company->setCountryId('TV');
        $company->setCity('City');
        $company->setStreet(['avenue, 30']);
        $company->setPostcode('postcode');
        $company->setTelephone('123456');

        $companyDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $company,
            CompanyInterface::class
        );
        $requestData = ['company' => $companyDataObject];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($this->compare($company, $response));
        $this->companyRepository->deleteById($response['id']);
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
    public function testUpdateCompany()
    {
        $company = $this->companyManagement->getByCustomerId(
            DataFixtureStorageManager::getStorage()->get('customer')->getId()
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/' . $company->getId(),
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'Save',
            ],
        ];

        $company->setCompanyName('other company');

        $companyDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $company,
            CompanyInterface::class
        );
        $requestData = ['company' => $companyDataObject];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($this->compare($company, $response));
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
    public function testGetCompany()
    {
        $storage = DataFixtureStorageManager::getStorage();
        $customerId = (int) $storage->get('customer')->getId();

        $company = $this->companyManagement->getByCustomerId($customerId);
        $response = $this->getCompany($company->getId());
        $this->assertTrue($this->compare($company, $response));
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
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer2.id$',
                CompanyInterface::NAME => 'Company 2'
            ],
            'company2'
        ),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testGetCompanyList()
    {
        $storage = DataFixtureStorageManager::getStorage();
        $adminId = (int) $storage->get('admin')->getId();
        $customer1Id = (int) $storage->get('customer')->getId();
        $customer2Id = (int) $storage->get('customer2')->getId();
        $company1 = $this->companyManagement->getByCustomerId($customer1Id);
        $company2 = $this->companyManagement->getByCustomerId($customer2Id);

        $filter = Bootstrap::getObjectManager()->create(FilterBuilder::class)
            ->setField(CompanyInterface::SALES_REPRESENTATIVE_ID)
            ->setValue($adminId)
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->searchCriteriaBuilder->create(),
            SearchCriteriaInterface::class
        );
        $requestData = ['searchCriteria' => $searchData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/' . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertTrue($this->compare($company1, $response['items'][0]));
        $this->assertTrue($this->compare($company2, $response['items'][1]));
    }

    /**
     * Compares company object with WebAPI response.
     *
     * @param CompanyInterface $company
     * @param array $response
     * @return bool
     */
    private function compare(CompanyInterface $company, array $response)
    {
        $originalData = $company->getData();
        $equal = true;
        foreach ($this->fieldsToCheck as $field) {
            if ($response[$field] != $originalData[$field]) {
                $equal = false;
                break;
            }
        }
        return $equal;
    }

    /**
     * Get company via WebAPI.
     *
     * @param int $companyId
     * @return array|bool|float|int|string
     */
    private function getCompany($companyId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/' . $companyId,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'Get',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['companyId' => $companyId]);
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
    public function testDeleteCompany()
    {
        $companyId = (int) DataFixtureStorageManager::getStorage()->get('company1')->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/' . $companyId,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'DeleteById',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['companyId' => $companyId]);
        $this->assertTrue($response);
    }
    
    /**
     *   #[
     *      DataFixture(
     *          User::class,
     *          [
     *            'password' => 'adminPassword123',
     *            'role_id' => 1,
     *          ],
     *          as: 'admin_user'
     *      ),
     *      DataFixture(
     *          CustomerFixture::class,
     *          [
     *          CustomerInterface::EMAIL => 'tést.cüstómèr@magento.com'],
     *          as: 'company_admin_customer'
     *      ),
     *      DataFixture(
     *          Company::class,
     *          [
     *              CompanyInterface::COMPANY_EMAIL => 'tést.cömpàny@magento.com',
     *              CompanyInterface::SUPER_USER_ID => '$company_admin_customer.id$',
     *              CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin_user.id$',
     *          ],
     *          as: 'company'
     *      )
     *    ]
     */
    public function testUpdateCompanyWithUtf8Email()
    {
        $this->markTestSkipped('B2B-3210: Implementation is not compatible with all magento ce versions');
        $company = $this->fixtures->get('company');
        $response = $this->getCompany($company->getId());
        $this->assertArrayHasKey('company_email', $response);
        $this->assertSame($company->getCompanyEmail(), $response['company_email']);

        $company->setCompanyEmail('tést.cömpàny.nêw@magento.com');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/' . $company->getId(),
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'Save',
            ],
        ];

        $companyDataObject = $this->dataObjectProcessor->buildOutputDataArray(
            $company,
            CompanyInterface::class
        );
        $requestData = ['company' => $companyDataObject];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($this->compare($company, $response));
    }
}
