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

namespace Magento\Company\Service\V1;

use Magento\Company\Api\CompanyCustomerAssignmentInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyInterfaceFactory;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test creating a company
 */
class CreateCompanyTest extends WebapiAbstract
{
    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(CustomerGroup::class, as: 'group'),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testCustomer(): void
    {
        $this->_markTestAsRestOnly();

        $storage = DataFixtureStorageManager::getStorage();
        $customerId = (int) $storage->get('customer')->getId();
        $adminId = (int) $storage->get('admin')->getId();
        $groupId = (int) $storage->get('group')->getId();

        $response = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => '/V1/company/',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ]
            ],
            [
                'company' => $this->getCompanyData($customerId, $adminId, $groupId)
            ]
        );
        $this->assertNotEmpty($response['id']);
        $this->assertEquals($customerId, $response['super_user_id']);
        Bootstrap::getObjectManager()->get(CompanyRepositoryInterface::class)->deleteById($response['id']);
    }

    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(CustomerGroup::class, as: 'group'),
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
        $this->_markTestAsRestOnly();

        $storage = DataFixtureStorageManager::getStorage();
        $customerId = (int) $storage->get('customer2')->getId();
        $adminId = (int) $storage->get('admin')->getId();
        $groupId = (int) $storage->get('group')->getId();

        $response = $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => '/V1/company/',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ]
            ],
            [
                'company' => $this->getCompanyData($customerId, $adminId, $groupId)
            ]
        );
        $this->assertNotEmpty($response['id']);
        $this->assertEquals($customerId, $response['super_user_id']);

        $customerAssignment = Bootstrap::getObjectManager()->get(CompanyCustomerAssignmentInterface::class);
        $this->assertCount(2, $customerAssignment->getAssignedCompanies($customerId));

        Bootstrap::getObjectManager()->get(CompanyRepositoryInterface::class)->deleteById($response['id']);
    }

    /**
     * Retrieve company data for request
     *
     * @param int $superUserId
     * @param int $salesRepresentativeId
     * @param int $customerGroupId
     * @return array
     */
    private function getCompanyData(int $superUserId, int $salesRepresentativeId, int $customerGroupId): array
    {
        $companyFactory = Bootstrap::getObjectManager()->get(CompanyInterfaceFactory::class);
        /** @var CompanyInterface $company */
        $company = $companyFactory->create([
            'data' => [
                'company_name' => 'company',
                'status' => 1,
                'company_email' => 'company' . time() . rand() . '@example.com',
                'super_user_id' => $superUserId,
                'sales_representative_id' => $salesRepresentativeId,
                'country_id' => 'TV',
                'city' => 'City',
                'street' => "avenue\n30",
                'postcode' => 'postcode',
                'telephone' => '123456',
                'customer_group_id' => $customerGroupId
            ]
        ]);
        return Bootstrap::getObjectManager()->get(DataObjectProcessor::class)->buildOutputDataArray(
            $company,
            CompanyInterface::class
        );
    }
}
