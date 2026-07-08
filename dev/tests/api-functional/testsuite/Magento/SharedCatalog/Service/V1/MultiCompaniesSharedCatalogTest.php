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

namespace Magento\SharedCatalog\Service\V1;

use Exception;
use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Store\Api\Data\GroupInterface as StoreGroupInterface;
use Magento\Store\Test\Fixture\Group;
use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\Website;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Test\Fixture\User;

/**
 * Tests shared catalog CRUD & company management operations with company header.
 */
#[
    DataFixture(Group::class, as: 'store_group'),
    DataFixture(Website::class, as: 'website'),
    DataFixture(Store::class, as: 'store'),
    DataFixture(Customer::class, as: 'company_admin_a'),
    DataFixture(Customer::class, as: 'company_admin_b'),
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
    DataFixture(SharedCatalog::class, as: 'shared_catalog')
]
class MultiCompaniesSharedCatalogTest extends AbstractSharedCatalogTestCase
{
    /**
     * @var string
     */
    private $adminToken;

    /**
     * @var string
     */
    private $companyAId;

    /**
     * @var string
     */
    private $companyBId;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
        if (!$this->adminToken) {
            /** @var UserInterface $admin */
            $admin = DataFixtureStorageManager::getStorage()->get('sales_rep');
            $this->adminToken = $this->objectManager->get(AdminTokenServiceInterface::class)->createAdminAccessToken(
                $admin->getUserName(),
                \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
            );
        }
        if (!$this->companyAId) {
            $this->companyAId = DataFixtureStorageManager::getStorage()->get('company_a')->getId();
        }
        if (!$this->companyBId) {
            $this->companyBId = DataFixtureStorageManager::getStorage()->get('company_b')->getId();
        }
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('catalog/magento_catalogpermissions/enabled', 1),
    ]
    public function testSharedCatalogCompanyManagementWithCompanyHeader()
    {
        /** @var SharedCatalogInterface $sharedCatalog */
        $sharedCatalog = DataFixtureStorageManager::getStorage()->get('shared_catalog');
        /** @var CompanyInterface $companyA */
        $companyA = DataFixtureStorageManager::getStorage()->get('company_a');
        /** @var CompanyInterface $companyB */
        $companyB = DataFixtureStorageManager::getStorage()->get('company_b');

        // Assign CompanyA and CompanyB to the SharedCatalog
        $response = $this->restApiCall(
            sprintf('/V1/sharedCatalog/%d/assignCompanies', $sharedCatalog->getId()),
            Request::HTTP_METHOD_POST,
            $this->companyAId,
            [
                'sharedCatalogId' => $sharedCatalog->getId(),
                'companies' => [$this->getCompanyData($companyA), $this->getCompanyData($companyB)]
            ]
        );
        $this->assertTrue($response);

        // Validate SharedCatalog's companies through GET request
        $respCompanyIds = $this->restApiCall(
            sprintf('/V1/sharedCatalog/%d/companies', $sharedCatalog->getId()),
            Request::HTTP_METHOD_GET,
            $this->companyAId,
            ['sharedCatalogId' => $sharedCatalog->getId()]
        );
        $this->assertEquals(
            implode('"', ['[', $this->companyAId, ',', $this->companyBId, ']']),
            $respCompanyIds
        );

        // Unassign CompanyA from the SharedCatalog
        $response = $this->restApiCall(
            sprintf('/V1/sharedCatalog/%d/unassignCompanies', $sharedCatalog->getId()),
            Request::HTTP_METHOD_POST,
            $this->companyBId,
            [
                'sharedCatalogId' => $sharedCatalog->getId(),
                'companies' => [$this->getCompanyData($companyA)]
            ]
        );
        $this->assertTrue($response);

        // Validate SharedCatalog's companies through GET request
        $respCompanyIds = $this->restApiCall(
            sprintf('/V1/sharedCatalog/%d/companies', $sharedCatalog->getId()),
            Request::HTTP_METHOD_GET,
            $this->companyAId,
            ['sharedCatalogId' => $sharedCatalog->getId()]
        );
        $this->assertEquals(
            implode('"', ['[', $this->companyBId, ']']),
            $respCompanyIds
        );

        // Unassign CompanyB from the SharedCatalog
        $response = $this->restApiCall(
            sprintf('/V1/sharedCatalog/%d/unassignCompanies', $sharedCatalog->getId()),
            Request::HTTP_METHOD_POST,
            $this->companyBId,
            [
                'sharedCatalogId' => $sharedCatalog->getId(),
                'companies' => [$this->getCompanyData($companyB)]
            ]
        );
        $this->assertTrue($response);

        // Validate SharedCatalog's companies through GET request
        $respCompanyIds = $this->restApiCall(
            sprintf('/V1/sharedCatalog/%d/companies', $sharedCatalog->getId()),
            Request::HTTP_METHOD_GET,
            $this->companyBId,
            ['sharedCatalogId' => $sharedCatalog->getId()]
        );
        $this->assertEquals(
            '[]',
            $respCompanyIds
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/sharedcatalog_active', 1),
    ]
    public function testSharedCatalogCRUDOperationWithCompanyHeader(): void
    {
        $defaultData = $this->getSharedCatalogData();

        // Create the SharedCatalog
        $sharedCatalogId = $this->restApiCall(
            '/V1/sharedCatalog',
            Request::HTTP_METHOD_POST,
            $this->companyAId,
            ['sharedCatalog' => $defaultData]
        );
        $this->assertNotEmpty($sharedCatalogId);
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $this->compareCatalogs($sharedCatalog, $defaultData);

        // Get the SharedCatalog
        $respSharedCatalog = $this->restApiCall(
            '/V1/sharedCatalog/' . $sharedCatalogId,
            Request::HTTP_METHOD_GET,
            $this->companyAId,
            ['sharedCatalogId' => $sharedCatalogId]
        );
        $this->compareCatalogs($sharedCatalog, $respSharedCatalog);

        // Update the SharedCatalog
        /** @var StoreGroupInterface $storeGroup */
        $storeGroup = DataFixtureStorageManager::getStorage()->get('store_group');

        $updateData = $respSharedCatalog;
        $updateData['name'] = 'shared_catalog_' . time();
        $updateData['description'] = 'shared catalog description NEW';
        $updateData['store_id'] = $storeGroup->getId();

        $updatedSharedCatalogId = $this->restApiCall(
            '/V1/sharedCatalog/' . $sharedCatalogId,
            Request::HTTP_METHOD_PUT,
            $this->companyBId,
            ['sharedCatalog' => $updateData]
        );
        $this->assertEquals($updatedSharedCatalogId, $sharedCatalogId);
        $updatedSharedCatalog = $this->getSharedCatalog();
        $this->compareCatalogs($updatedSharedCatalog, $updateData);

        // Delete the SharedCatalog
        $response = $this->restApiCall(
            '/V1/sharedCatalog/' . $updatedSharedCatalogId,
            Request::HTTP_METHOD_DELETE,
            $this->companyBId,
            ['sharedCatalogId' => $updatedSharedCatalogId]
        );
        $this->assertTrue($response);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Requested Shared Catalog is not found');
        $this->restApiCall(
            '/V1/sharedCatalog/' . $updatedSharedCatalogId,
            Request::HTTP_METHOD_GET,
            $this->companyAId,
            ['sharedCatalogId' => $updatedSharedCatalogId]
        );
    }

    /**
     * Get shared catalog test data.
     *
     * @return array
     */
    private function getSharedCatalogData()
    {
        return [
            'name' => 'shared_catalog_' . time(),
            'description' => 'shared catalog description',
            'type' => 0,
            'created_by' => null,
            'store_id' => 1,
            'tax_class_id' => 3,
            'created_at' => date('Y-m-d H:i:s', time()),
            'customer_group_id' => null
        ];
    }

    /**
     * Get company data from Company model.
     *
     * @param CompanyInterface $company
     * @return array
     */
    private function getCompanyData(CompanyInterface $company)
    {
        return [
            'id' => $company->getId(),
            'street' => $company->getStreet(),
            'sales_representative_id' => $company->getSalesRepresentativeId(),
            'reject_reason' => $company->getRejectReason(),
            'rejected_at' => $company->getRejectedAt(),
            'customer_group_id' => $company->getCustomerGroupId(),
            'super_user_id' => $company->getSuperUserId()
        ];
    }

    /**
     * Perform Rest Web API call to the system under test.
     *
     * @param string $resourcePath
     * @param string $httpMethod
     * @param string $companyAId
     * @param array $requestData
     * @return array|bool|float|int|string
     */
    private function restApiCall(
        string $resourcePath,
        string $httpMethod,
        string $companyAId,
        array $requestData
    ): array|bool|float|int|string {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => $httpMethod,
                'headers' => ['X-Adobe-Company: ' . $companyAId],
                'token' => $this->adminToken
            ]
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }
}
