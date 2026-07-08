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

namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Company\Test\Fixture\CustomerGroup as CustomerGroupFixture;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Logging\Model\Event as AdminLoggingEvent;
use Magento\Logging\Model\Event\Changes as LogChangeEntry;
use Magento\Logging\Model\ResourceModel\Event\Collection as AdminActionLoggingCollection;
use Magento\Logging\Model\ResourceModel\Event\Changes\Collection as AdminActionLoggingChangeCollection;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Magento\User\Test\Fixture\User as AdminUserFixture;
use PHPUnit\Framework\Assert;

/**
 * Test for mass company update settings controller, calling from company grid listing page.
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
     * @var string
     */
    protected $resource = MassUpdateSettings::ADMIN_RESOURCE;

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
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
        $this->adminActionLoggingCollection = $this->objectManager->get(AdminActionLoggingCollection::class);
        $this->adminActionLoggingChangeCollection = $this->objectManager->get(
            AdminActionLoggingChangeCollection::class
        );

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
    public function testCanUpdateMultipleCompaniesInOneRequest()
    {
        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        /** @var CompanyInterface $company2 */
        $company2 = $this->fixtures->get('company2');

        /** @var CustomerGroupInterface $customerGroup */
        $customerGroup = $this->fixtures->get('customerGroup');

        // assert each company is initially not assigned to $customerGroup
        $this->assertNotEquals(
            $customerGroup->getId(),
            $company->getCustomerGroupId()
        );

        $this->assertNotEquals(
            $customerGroup->getId(),
            $company2->getCustomerGroupId()
        );

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
            ],
            'customer_group_id' => $customerGroup->getId(),
        ];

        $request->setParams($params);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertEquals(
            [
                'success' => true,
                'messages' => [
                    0 => [
                        'type' => 'success',
                        'text' => (string) __('Successfully changed settings for %1 companies.', 2),
                    ],
                ],
            ],
            json_decode($this->getResponse()->getContent(), true)
        );

        // refresh the companies
        $companyRefreshed = $this->companyRepository->get($company->getId());
        $company2Refreshed = $this->companyRepository->get($company2->getId());

        // assert that each company's customer group was updated to $customerGroup
        $this->assertEquals(
            $customerGroup->getId(),
            $companyRefreshed->getCustomerGroupId()
        );

        $this->assertEquals(
            $customerGroup->getId(),
            $company2Refreshed->getCustomerGroupId()
        );

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_SUCCESS, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Successfully updated the configuration for 2 companies: {$company->getId()}, {$company2->getId()}",
            sprintf(
                'Request Parameters: %s',
                json_encode(['customer_group_id' => $customerGroup->getId()], JSON_PRETTY_PRINT)
            )
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly(
            [$company->getId(), $company2->getId()],
            ['customer_group_id' => $customerGroup->getId()],
            (int) $logInfoEntry->getLogId()
        );
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
    public function testExcludedRequestParameter()
    {
        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        /** @var CompanyInterface $company2 */
        $company2 = $this->fixtures->get('company2');

        /** @var CustomerGroupInterface $customerGroup */
        $customerGroup = $this->fixtures->get('customerGroup');

        // get total company count to promote determinism in the test in case other companies exist besides these two
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)->create();
        $totalCompanyCount = $this->companyRepository->getList($searchCriteria)->getTotalCount();

        $request = $this->getRequest();
        $request->setMethod($this->httpMethod);

        $params = [
            MassActionFilter::EXCLUDED_PARAM => [ // update all companies except $company2
                $company2->getId(),
            ],
            'namespace' => 'company_listing',
            'filters' => [
                'placeholder' => true,
            ],
            'customer_group_id' => $customerGroup->getId(),
        ];

        $request->setParams($params);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals(true, $responseBody['success']);
        $this->assertEquals(
            [
                'type' => 'success',
                'text' => (string) __(
                    'Successfully changed settings for %1 companies.',
                    $totalCompanyCount - 1
                ),
            ],
            $responseBody['messages'][0]
        );

        // refresh the companies
        $companyRefreshed = $this->companyRepository->get($company->getId());
        $company2Refreshed = $this->companyRepository->get($company2->getId());

        // assert that the $company's customer group was updated to $customerGroup
        $this->assertEquals(
            $customerGroup->getId(),
            $companyRefreshed->getCustomerGroupId()
        );

        // assert that $company2's customer group remains the same because it was excluded in the request
        $this->assertEquals(
            $company2->getCustomerGroupId(),
            $company2Refreshed->getCustomerGroupId()
        );

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_SUCCESS, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Successfully updated the configuration for Company {$companyRefreshed->getId()}",
            sprintf(
                'Request Parameters: %s',
                json_encode(['customer_group_id' => $customerGroup->getId()], JSON_PRETTY_PRINT)
            )
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly(
            [$companyRefreshed->getId()],
            ['customer_group_id' => $customerGroup->getId()],
            (int) $logInfoEntry->getLogId()
        );
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
    public function testNoConfigurationPassedToRequestGeneratesErrorMessage()
    {
        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        /** @var CompanyInterface $company2 */
        $company2 = $this->fixtures->get('company2');

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
            ],
        ];

        $request->setParams($params);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals(1, count($responseBody['messages']));
        $this->assertEquals(false, $responseBody['success']);
        $this->assertEquals(MessageInterface::TYPE_ERROR, $responseBody['messages'][0]['type']);
        $this->assertEquals(
            (string) __(
                "Cannot apply changes because no configuration settings were updated " .
                "on the Change company settings form. Update at least one company setting, and try again."
            ),
            $responseBody['messages'][0]['text']
        );

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_FAILURE, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Failed to update the configuration for 2 companies: {$company->getId()}, {$company2->getId()}" ,
            sprintf('Request Parameters: %s', json_encode([]))
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly(
            [],
            [],
            (int) $logInfoEntry->getLogId()
        );
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
    public function testMassUpdatingCompaniesWithNonExistentCustomerGroupIdRaisesError()
    {
        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');

        /** @var CompanyInterface $company2 */
        $company2 = $this->fixtures->get('company2');

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
            ],
            'customer_group_id' => -1,
        ];

        $request->setParams($params);

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals(false, $responseBody['success']);
        $this->assertEquals(2, count($responseBody['messages']));
        $companies = [$company, $company2];
        foreach ($responseBody['messages'] as $key => $message) {
            $this->assertEquals(
                (string) __(
                    'An error occurred when updating the %companyName company. Please try again. ' .
                    'If the problem continues, contact your site administrator.',
                    ['companyName' => $companies[$key]->getCompanyName()]
                ),
                $message['text']
            );
        }

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_FAILURE, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Failed to update the configuration for 2 companies: {$company->getId()}, {$company2->getId()}" ,
            sprintf('Request Parameters: %s', json_encode(['customer_group_id' => -1], JSON_PRETTY_PRINT))
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly([], [], (int) $logInfoEntry->getLogId());
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'companyAdmin'),
        DataFixture(CustomerFixture::class, as: 'companyAdmin2'),
        DataFixture(CustomerFixture::class, as: 'companyAdmin3'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser2'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser3'),
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
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Company3',
                CompanyInterface::SUPER_USER_ID => '$companyAdmin3.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser3.id$',
            ],
            as: 'company3'
        ),
        DataFixture(
            CustomerGroupFixture::class,
            as: 'customerGroup'
        ),
    ]
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMassUpdateContinuesOnFailure()
    {
        /** @var CompanyInterface $erroneousCompany1 */
        $erroneousCompany1 = $this->fixtures->get('company');

        /** @var CompanyInterface $successfulCompany */
        $successfulCompany = $this->fixtures->get('company2');

        /** @var CompanyInterface $erroneousCompany2 */
        $erroneousCompany2 = $this->fixtures->get('company3');

        /** @var CustomerGroupInterface $customerGroup */
        $customerGroup = $this->fixtures->get('customerGroup');

        $request = $this->getRequest();
        $request->setMethod($this->httpMethod);

        $params = [
            MassActionFilter::SELECTED_PARAM => [
                $erroneousCompany1->getId(),
                $erroneousCompany2->getId(),
                $successfulCompany->getId(),
            ],
            'namespace' => 'company_listing',
            'filters' => [
                'placeholder' => true,
            ],
            'customer_group_id' => $customerGroup->getId(),
        ];

        $request->setParams($params);

        $companyRepositoryMock = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->getMockForAbstractClass();

        $companyRepositoryMock->expects($this->exactly(3))
            ->method('get')
            ->will($this->returnCallback(
                function (string $companyId) use (
                    $erroneousCompany1,
                    $erroneousCompany2,
                    $successfulCompany
                ) {
                    if ($companyId == $erroneousCompany1->getId()) {
                        // $erroneousCompany1 will throw an exception on get;
                        // its id will be included in the error message
                        throw NoSuchEntityException::singleField('id', $companyId);
                    }

                    if ($companyId == $erroneousCompany2->getId()) {
                        $company = $this->companyRepository->get($erroneousCompany2->getId());
                    } else {
                        $company = $this->companyRepository->get($successfulCompany->getId());
                    }

                    return $company;
                }
            ));

        $companyRepositoryMock->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnCallback(
                function (CompanyInterface $companyToSave) use (
                    $erroneousCompany2,
                    $successfulCompany,
                    $customerGroup
                ) {
                    if ($companyToSave->getId() == $erroneousCompany2->getId()) {
                        // $erroneousCompany2 will throw an exception on save;
                        // its name will be included in the error message
                        throw new CouldNotSaveException(__('Could not save company'));
                    }

                    $this->assertEquals($companyToSave->getId(), $successfulCompany->getId());
                    $this->assertEquals($customerGroup->getId(), $companyToSave->getCustomerGroupId());

                    $this->companyRepository->save($companyToSave);
                }
            ));

        $this->objectManager->configure([
            MassUpdateSettings::class => [
                'arguments' => [
                    'companyRepository' => [
                        'instance' => 'companyRepositoryMock',
                        'shared' => true,
                    ]
                ]
            ]
        ]);

        $this->objectManager->addSharedInstance($companyRepositoryMock, 'companyRepositoryMock');

        $this->dispatch($this->uri);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $responseBody = json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals(false, $responseBody['success']);
        $this->assertEquals(3, count($responseBody['messages']));

        $this->assertEquals(
            [
                'success' => false,
                'messages' => [
                    0 => [
                        'type' => 'success',
                        'text' => (string) __('Successfully changed settings for %1 companies.', 1),
                    ],
                    1 => [
                        'type' => 'error',
                        'text' => (string) __(
                            'An error occurred when updating company %companyId. Please try again. ' .
                            'If the problem continues, contact your site administrator.',
                            ['companyId' => $erroneousCompany1->getId()]
                        )
                    ],
                    2 => [
                        'type' => 'error',
                        'text' => (string) __(
                            'An error occurred when updating the %companyName company. Please try again. ' .
                            'If the problem continues, contact your site administrator.',
                            ['companyName' => $erroneousCompany2->getCompanyName()]
                        )
                    ]
                ],
            ],
            json_decode($this->getResponse()->getContent(), true)
        );

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_FAILURE, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Successfully updated the configuration for Company {$successfulCompany->getId()}",
            "Failed to update the configuration for 2 companies: "
                . "{$erroneousCompany1->getId()}, {$erroneousCompany2->getId()}",
            sprintf(
                'Request Parameters: %s',
                json_encode(['customer_group_id' => $customerGroup->getId()], JSON_PRETTY_PRINT)
            )
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly(
            [$successfulCompany->getId()],
            ['customer_group_id' => $customerGroup->getId()],
            (int) $logInfoEntry->getLogId()
        );
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'companyAdmin'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
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
            CustomerGroupFixture::class,
            as: 'customerGroup'
        ),
    ]
    /**
     * Test that data from the posted array is properly filtered out and does not contain
     * values not provided in form configuration.
     */
    public function testDataIsFilteredOut()
    {
        $customerGroup = $this->fixtures->get('customerGroup');

        /** @var CompanyInterface $company */
        $company = $this->fixtures->get('company');
        $request = $this->getRequest();
        $request->setMethod($this->httpMethod);

        $params = [
            MassActionFilter::SELECTED_PARAM => [$company->getId()],
            'namespace' => 'company_listing',
            'filters' => [
                'placeholder' => true,
            ],
            'customer_group_id' => $customerGroup->getId(),
            'unexpected_field' => 'unexpected_field_value',
            'extension_attributes' => [
                'unexpected_attribute' => 'unexpected_attribute_value'
            ]
        ];

        $request->setParams($params);

        $realDataHelper = $this->objectManager->get(DataObjectHelper::class);
        $dataAssertionErrors = [];
        $dataAssertionHappened = false;
        $proxyClosure = function (
            $dataObject,
            array $data,
            $interfaceName
        ) use (
            $realDataHelper,
            $company,
            $customerGroup,
            &$dataAssertionErrors,
            &$dataAssertionHappened
        ) {
            if ($dataObject instanceof CompanyInterface && $dataObject->getId() == $company->getId()) {
                try {
                    $dataAssertionHappened = true;
                    Assert::assertArrayNotHasKey('unexpected_field', $data);
                    Assert::assertArrayHasKey('customer_group_id', $data);
                    Assert::assertArrayHasKey('extension_attributes', $data);
                    Assert::assertArrayNotHasKey('unexpected_attribute', $data['extension_attributes']);
                    Assert::assertEquals($customerGroup->getId(), $data['customer_group_id']);
                } catch (\Throwable $e) {
                    $dataAssertionErrors[] = $e->getMessage();
                }
            }
            return $realDataHelper->populateWithArray($dataObject, $data, $interfaceName);
        };
        $dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataObjectHelperMock->expects($this->any())
            ->method('populateWithArray')
            ->willReturnCallback($proxyClosure);

        $this->objectManager->configure([
            MassUpdateSettings::class => [
                'arguments' => [
                    'dataObjectHelper' => [
                        'instance' => 'dataObjectHelperMock',
                        'shared' => true,
                    ]
                ]
            ]
        ]);

        $this->objectManager->addSharedInstance($dataObjectHelperMock, 'dataObjectHelperMock');

        $this->dispatch($this->uri);
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertTrue($dataAssertionHappened, 'Data assertion for the posted array did not happen');
        $this->assertEmpty(
            $dataAssertionErrors,
            'Failed to assert that POST data is filtered properly: ' . implode($dataAssertionErrors)
        );

        $logInfoEntry = $this->getLogInfo();
        $this->assertEquals(AdminLoggingEvent::RESULT_SUCCESS, $logInfoEntry->getStatus());
        $expectedMessages = [
            "Successfully updated the configuration for Company {$company->getId()}",
            sprintf(
                'Request Parameters: %s',
                json_encode(
                    ['customer_group_id' => $customerGroup->getId(), 'extension_attributes' => []],
                    JSON_PRETTY_PRINT
                )
            )
        ];
        $this->assertEquals($expectedMessages, json_decode($logInfoEntry->getInfo(), true)['general']);
        $this->assertMassCompanyUpdateIsLoggedCorrectly(
            [$company->getId()],
            ['customer_group_id' => $customerGroup->getId()],
            (int) $logInfoEntry->getLogId()
        );
    }

    /**
     * @param array $companyIds
     * @param array $changedData
     * @param int $logId
     * @return void
     */
    private function assertMassCompanyUpdateIsLoggedCorrectly(
        array  $companyIds,
        array  $changedData,
        int $logId
    ) {
        $logChangeEntries = $this->getLogChangeEntries($logId);

        $this->assertCount(count($companyIds), $logChangeEntries);

        $successfulCompanyIdsLogged = [];
        /** @var LogChangeEntry $logChangeEntry */
        foreach ($logChangeEntries as $logChangeEntry) {
            $successfulEventCompanyId = $logChangeEntry->getSourceId();

            $this->assertContains($successfulEventCompanyId, $companyIds);

            $this->assertNotContains(
                $successfulEventCompanyId,
                $successfulCompanyIdsLogged,
                'Company with ID ' . $successfulEventCompanyId . ' was logged multiple times'
            );

            $successfulCompanyIdsLogged[] = $successfulEventCompanyId;

            $this->assertEquals(
                ['customer_group_id' => 1],
                json_decode($logChangeEntry->getOriginalData(), true)
            );
            $this->assertEquals($changedData, json_decode($logChangeEntry->getResultData(), true));
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
