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

namespace Magento\CompanyAsynchronousOperations\Api;

use Exception;
use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Api\Data\CompanyInterface;

class AsyncBulkOperationTest extends WebapiAbstract
{
    private const BULK_RESOURCE_PATH = '/async/bulk';
    private const BULK_OPERATION_STATUS_RESOURCE_PATH = '/V1/bulk';
    private const CUSTOMER_RESOURCE_PATH = '/V1/customers';

    public function setUp(): void
    {
        $this->_markTestAsRestOnly();
        parent::setUp();
    }

    /**
     * Check multiple bulk operations sent followed by search via getList.
     * @dataProvider bulkOperationDataProvider
     * @param int $recordCnt
     * @param string|null $companyInHeader
     * @return void
     */
    #[
        Config('btob/website_configuration/company_active', 1),
        DataFixture(Customer::class, as: 'company_admin'),
        DataFixture(RoleFixture::class, as: 'adminRole'),
        DataFixture(User::class, ['role_id' => '$adminRole.id$'], 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$company_admin.id$',
                CompanyInterface::STATUS => 1,
            ],
            'company'
        )
     ]
    public function testBulkOperation(int $recordCnt, ?string $companyInHeader): void
    {
        $headerCompanyId = null;
        if ($companyInHeader) {
            $headerCompanyId = DataFixtureStorageManager::getStorage()->get($companyInHeader)->getId();
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $headerCompanyId];
        }

        // Create bulk customers with duplicates
        $duplicate = $this->getBulkCustomerData(1);
        $entityArray = $duplicate;
        $entityArray = array_merge($entityArray, $this->getBulkCustomerData(2));
        $entityArray = array_merge($entityArray, $duplicate);

        // Adding bulk records twice
        $this->sendBulk(self::CUSTOMER_RESOURCE_PATH, $entityArray, $headerCompanyId);
        $this->sendBulk(self::CUSTOMER_RESOURCE_PATH, $entityArray, $headerCompanyId);

        $searchCriteria = $this->getSearchFilterByStartTime('-1');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::BULK_OPERATION_STATUS_RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertEquals($recordCnt, $response['total_count']);
        $this->assertCount($recordCnt, $response['items']);
    }

    /**
     * @return array
     */
    public function bulkOperationDataProvider(): array
    {
        return [
            [8, null],
            [16, 'company'],
        ];
    }

    /**
     * @param string $resourcePath
     * @param array $bulkData
     * @param string|null $headerCompanyId
     * @return void
     */
    private function sendBulk(string $resourcePath, array $bulkData, ?string $headerCompanyId): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::BULK_RESOURCE_PATH . $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        if ($headerCompanyId) {
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $headerCompanyId];
        }

        $response = $this->_webApiCall($serviceInfo, $bulkData);

        // Assert bulk operation with no errors
        $this->assertFalse($response['errors']);
    }

    /**
     * @param int $count
     * @return array
     * @throws Exception
     */
    private function getBulkCustomerData(int $count): array
    {
        $bulkCustomerData = [];
        for ($i = 0; $i < $count; $i++) {
            $uniId = (string) random_int(10000, 99999);
            $bulkCustomerData[] = [
                'customer' => [
                    'email' => 'customer' . $uniId . '@xyz.com',
                    'firstname' => 'First' . $uniId,
                    'lastname' => 'Last' . $uniId,
                ],
                "password" => "Strong-Password"
            ];
        }
        return $bulkCustomerData;
    }

    /**
     * @param string|null $minute
     * @return array[]
     */
    private function getSearchFilterByStartTime(?string $minute = '-1'): array
    {
        return [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'start_time',
                                'value' => date('Y-m-d H:i:s', strtotime($minute . ' minute')),
                                'condition_type' => 'gt',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 20,
            ],
        ];
    }
}
