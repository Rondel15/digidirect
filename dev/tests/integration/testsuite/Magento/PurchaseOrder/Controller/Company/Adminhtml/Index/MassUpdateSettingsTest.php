<?php
/**
 * ADOBE CONFIDENTIAL
 * Copyright 2023 Adobe
 * All Rights Reserved.
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

namespace Magento\PurchaseOrder\Controller\Company\Adminhtml\Index;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Company\Test\Fixture\CustomerGroup as CustomerGroupFixture;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Logging\Model\Event as AdminLoggingEvent;
use Magento\Logging\Model\Event\Changes as LogChangeEntry;
use Magento\Logging\Model\ResourceModel\Event\Changes\Collection as AdminActionLoggingChangeCollection;
use Magento\Logging\Model\ResourceModel\Event\Collection as AdminActionLoggingCollection;
use Magento\PurchaseOrder\Model\Company\Config\Repository as PurchaseOrderCompanyConfig;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Magento\User\Test\Fixture\User as AdminUserFixture;

/**
 * Test for mass company update settings controller.
 * This test passes in purchase order-related params to the controller.
 * @see \Magento\Company\Controller\Adminhtml\Index\MassUpdateSettings
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassUpdateSettingsTest extends AbstractBackendController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var AdminActionLoggingCollection
     */
    private $adminActionLoggingCollection;

    /**
     * @var AdminActionLoggingChangeCollection
     */
    private $adminActionLoggingChangeCollection;

    /**
     * @var PurchaseOrderCompanyConfig
     */
    private $purchaseOrderCompanyConfig;

    /**
     * @var string
     */
    protected $uri = 'backend/company/index/massUpdateSettings';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->messageManager = $this->objectManager->get(MessageManagerInterface::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
        $this->adminActionLoggingCollection = $this->objectManager->get(AdminActionLoggingCollection::class);
        $this->adminActionLoggingChangeCollection = $this->objectManager->get(
            AdminActionLoggingChangeCollection::class
        );

        $this->purchaseOrderCompanyConfig = $this->objectManager->get(PurchaseOrderCompanyConfig::class);

        parent::setUp();
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            CustomerGroupFixture::class,
            as: 'customerGroup'
        ),
        DataFixture(CustomerFixture::class, as: 'companyAdmin'),
        DataFixture(CustomerFixture::class, as: 'companyAdmin2'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser2'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Company',
                CompanyInterface::SUPER_USER_ID => '$companyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Company2',
                CompanyInterface::SUPER_USER_ID => '$companyAdmin2.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser2.id$',
            ],
            as: 'company2'
        ),
    ]
    /**
     * @param $requestParameters
     * @param $originalData
     * @param $resultData
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider requestDataProvider
     */
    public function testCanUpdateMultipleCompaniesInOneRequest($requestParameters, $originalData, $resultData)
    {
        $requestParameters = array_map(function ($value) {
            return (is_callable($value)) ? call_user_func($value) : $value;
        }, $requestParameters);
        $resultData = array_map(function ($value) {
            return (is_callable($value)) ? call_user_func($value) : $value;
        }, $resultData);

        $updateCustomerGroup = isset($requestParameters['customer_group_id']);

        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        /** @var CompanyInterface $company2 */
        $company2 = $this->fixtures->get('company2');

        /** @var CustomerGroupInterface $customerGroup */
        $customerGroup = $this->fixtures->get('customerGroup');

        foreach ([$company, $company2] as $originalCompany) {
            // assert each company is initially not assigned to $customerGroup
            $this->assertNotEquals($customerGroup->getId(), $originalCompany->getCustomerGroupId());
            // assert each company does not initially have purchase orders enabled
            $this->assertFalse(
                $this->purchaseOrderCompanyConfig->get($originalCompany->getId())->isPurchaseOrderEnabled()
            );
        }

        $request = $this->getRequest();
        $request->setMethod($this->httpMethod);

        $params = [
            MassActionFilter::SELECTED_PARAM => [
                $company->getId(),
                $company2->getId(),
            ],
            'namespace' => 'company_listing',
            'filters' => [
                'placeholder' => true,
            ]
        ];

        $params = array_merge($params, $requestParameters);

        $request->setParams($params);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals(
            [
                'success' => true,
                'messages' => [
                    0 => [
                        'type' => 'success',
                        'text' => (string) __('Successfully changed settings for %1 companies.', 2),
                    ],
                ]
            ],
            $responseBody
        );

        // refresh the companies
        $companies = [
            $this->companyRepository->get($company->getId()),
            $this->companyRepository->get($company2->getId())
        ];
        foreach ($companies as $updatedCompany) {
            // assert each company's purchase order status is set to enabled
            $this->assertTrue(
                $updatedCompany->getExtensionAttributes()->getIsPurchaseOrderEnabled()
            );
            // assert that each company's customer group was updated to $customerGroup
            if ($updateCustomerGroup) {
                $this->assertEquals($customerGroup->getId(), $updatedCompany->getCustomerGroupId());
            }
        }

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_SUCCESS, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Successfully updated the configuration for 2 companies: {$company->getId()}, {$company2->getId()}",
            sprintf('Request Parameters: %s', json_encode($requestParameters, JSON_PRETTY_PRINT))
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly(
            [$company->getId(), $company2->getId()],
            $originalData,
            $resultData,
            (int) $logInfoEntry->getLogId()
        );
    }

    /**
     * @return array
     */
    public static function requestDataProvider(): array
    {
        $getCustomerGroup = function () {
            return DataFixtureStorageManager::getStorage()->get('customerGroup')->getId();
        };
        return [
            'update customer group' => [
                'requestParameters' => [
                    'customer_group_id' => $getCustomerGroup,
                    'extension_attributes' => [
                        'is_purchase_order_enabled' => "1"
                    ]
                ],
                'originalData' => [
                    'customer_group_id' => 1,
                    'is_purchase_order_enabled' => false
                ],
                'resultData' => [
                    'is_purchase_order_enabled' => true,
                    'customer_group_id' => $getCustomerGroup
                ],
            ],
            'do not update customer group' => [
                'requestParameters' => [
                    'extension_attributes' => [
                        'is_purchase_order_enabled' => "1"
                    ]
                ],
                'originalData' => [
                    'is_purchase_order_enabled' => false
                ],
                'resultData' => [
                    'is_purchase_order_enabled' => true
                ],
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'companyAdmin'),
        DataFixture(CustomerFixture::class, as: 'companyAdmin2'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser2'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Company',
                CompanyInterface::SUPER_USER_ID => '$companyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Company2',
                CompanyInterface::SUPER_USER_ID => '$companyAdmin2.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser2.id$',
            ],
            as: 'company2'
        ),
    ]
    public function testUpdatingCompanyToSameValuesDoesNotCreateChangeRecordForThatCompany()
    {
        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        /** @var CompanyInterface $company2 */
        $company2 = $this->fixtures->get('company2');

        // modify company2's purchase order status to enabled
        $company2->getExtensionAttributes()->setIsPurchaseOrderEnabled(true);
        $this->companyRepository->save($company2);

        $request = $this->getRequest();
        $request->setMethod($this->httpMethod);

        $params = [
            MassActionFilter::SELECTED_PARAM => [
                $company->getId(),
                $company2->getId(),
            ],
            'namespace' => 'company_listing',
            'filters' => [
                'placeholder' => true,
            ]
        ];
        $requestParameters =  [
            'customer_group_id' => 1,
            'extension_attributes' => [
                'is_purchase_order_enabled' => '0',
            ]
        ];
        $params = array_merge($params, $requestParameters);
        $request->setParams($params);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals(
            [
                'success' => true,
                'messages' => [
                    0 => [
                        'type' => 'success',
                        'text' => (string) __('Successfully changed settings for %1 companies.', 2),
                    ],
                ]
            ],
            $responseBody
        );

        // assert that only company2 has a change record
        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_SUCCESS, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Successfully updated the configuration for 2 companies: {$company->getId()}, {$company2->getId()}",
            sprintf('Request Parameters: %s', json_encode($requestParameters, JSON_PRETTY_PRINT))
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);

        $logChangeEntries = $this->getLogChangeEntries((int) $logInfoEntry->getLogId());

        $this->assertCount(1, $logChangeEntries);

        /** @var LogChangeEntry $logChangeEntry */
        $logChangeEntry = reset($logChangeEntries);

        $this->assertEquals(
            ['is_purchase_order_enabled' => true],
            json_decode($logChangeEntry->getOriginalData(), true)
        );

        $this->assertEquals(
            ['is_purchase_order_enabled' => false],
            json_decode($logChangeEntry->getResultData(), true)
        );
    }

    /**
     * @param array $companyIds
     * @param array $failedCompanyIds
     * @param array $resultData
     * @param array|null $settingsRequestData
     * @return void
     */
    private function assertMassCompanyUpdateIsLoggedCorrectly(
        array $companyIds,
        array $originalData,
        array $resultData,
        int   $logId
    ) {
        $logChangeEntries = $this->getLogChangeEntries($logId);

        $this->assertCount(count($companyIds), $logChangeEntries);

        $successfulCompanyIdsLogged = [];
        /** @var LogChangeEntry $logChangeEntry */
        foreach ($logChangeEntries as $logChangeEntry) {
            $successfulEventCompanyId = $logChangeEntry->getSourceId();

            $this->assertContains(
                $successfulEventCompanyId,
                $companyIds
            );

            $this->assertNotContains(
                $successfulEventCompanyId,
                $successfulCompanyIdsLogged,
                'Company with ID ' . $successfulEventCompanyId . ' was logged multiple times'
            );

            $successfulCompanyIdsLogged[] = $successfulEventCompanyId;

            $this->assertEquals($originalData, json_decode($logChangeEntry->getOriginalData(), true));
            $this->assertEquals($resultData, json_decode($logChangeEntry->getResultData(), true));
        }
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function getLogInfo()
    {
        return $this->adminActionLoggingCollection
            ->addFieldToFilter('event_code', 'company')
            ->addFieldToFilter('action', 'massUpdateSettings')
            ->setOrder('log_id')
            ->getFirstItem();
    }

    /**
     * @param int $logId
     * @return \Magento\Framework\DataObject[]
     */
    private function getLogChangeEntries(int $logId)
    {
        return $this->adminActionLoggingChangeCollection
            ->addFieldToFilter('event_id', $logId)
            ->getItems();
    }
}
