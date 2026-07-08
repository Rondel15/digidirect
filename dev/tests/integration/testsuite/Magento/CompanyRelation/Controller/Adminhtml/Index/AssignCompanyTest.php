<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2023 Adobe
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

namespace Magento\CompanyRelation\Controller\Adminhtml\Index;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\CompanyRelation\Api\RelationManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Logging\Model\ResourceModel\Event\Collection;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

class AssignCompanyTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Magento_Company::manage';

    /**
     * @var string
     */
    protected $uri = 'backend/company_relation/index/assignCompany';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtureStorage;

    /**
     * @var \Magento\Logging\Model\ResourceModel\Event\Collection
     */
    private $adminActionLogCollection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtureStorage = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->adminActionLogCollection = $objectManager->get(Collection::class);
    }

    #[
        AppArea('adminhtml'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_a_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_b_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_c_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_d_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent_company_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Parent Company A',
                CompanyInterface::SUPER_USER_ID => '$parent_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company A',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_a'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company B',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_b'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company C',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_c_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_c'
        ),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company C',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_d_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_d'
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_c.id$'],
                ],
            ],
        ),
        DataFixture(
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company_a.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_d.id$'],
                ],
            ],
        ),
    ]
    /**
     * @magentoDbIsolation disabled
     * @dataProvider executeDataProvider
     */
    public function testExecute($parentId, $companyIds, $expectedMsg, $expectedItems, array $expectedLogEntry)
    {
        $parentId = is_int($parentId) || empty($parentId) ?
            $parentId : (int) $this->fixtureStorage->get($parentId)->getData(CompanyInterface::COMPANY_ID);
        foreach ($companyIds as $key => $companyId) {
            $companyIds[$key] = is_int($companyId) || empty($companyId) ?
                $companyId : (int) $this->fixtureStorage->get($companyId)->getData(CompanyInterface::COMPANY_ID);
        }
        foreach ($expectedItems as $key => $fixture) {
            $expectedItems[$key] =
                (int) $this->fixtureStorage->get($fixture)->getData(CompanyInterface::COMPANY_ID);
        }

        $this->getRequest()->setMethod($this->httpMethod);
        $this->getRequest()->setPostValue([
            'parent_id' => $parentId,
            'company_ids' => $companyIds
        ]);
        $this->dispatch($this->uri);
        $responseContent = \json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('messages', $responseContent);
        $this->assertEquals($expectedMsg, $responseContent['messages']);
        $this->assertRelationExists($parentId, $expectedItems);
        $this->assertLastAdminLogEntry($expectedLogEntry);
    }

    /**
     * Assert last admin action log entry.
     *
     * @param array $logEntry
     * @return void
     */
    private function assertLastAdminLogEntry(array $logEntry): void
    {
        $entry = $this->adminActionLogCollection
            ->setOrder('log_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
            ->load()
            ->getFirstItem();
        $entryInfo = json_decode($entry->getData('info'), true);
        $companyIds = [];
        foreach ($logEntry['companyIdentifiers'] as $companyIdentifier) {
            $companyIds[] = $this->getFixtureCompanyId($companyIdentifier);
        }
        $this->assertEquals(
            sprintf($logEntry['message'], ...$companyIds),
            $entryInfo['general']
        );
    }

    /**
     * Get company id by fixture identifier.
     *
     * @param string $identifier
     * @return int
     */
    private function getFixtureCompanyId(string $identifier): int
    {
        return(int) $this->fixtureStorage->get($identifier)->getData(CompanyInterface::COMPANY_ID);
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function executeDataProvider(): array
    {
        return [
            'parentId_does_not_exists' => [
                'parentId' => 100500,
                'companyIds' => ['assigned_company_a', 'assigned_company_b'],
                'expectedMsg' => [
                    ['text' => 'Parent company 100500 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => [],
                'expectedLogEntry' => [
                    'message' => "Failed to assign company %d, %d to parent company 100500.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_b',
                    ]
                ],
            ],
            'parentId_is_empty' => [
                'parentId' => '',
                'companyIds' => ['assigned_company_a', 'assigned_company_b'],
                'expectedMsg' => [
                    ['text' => 'Parent company 0 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => [],
                'expectedLogEntry' => [
                    'message' => "Invalid parent company id .\nFailed to assign company %d, %d to parent company .",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_b',
                    ]
                ],
            ],
            'parentId_is_null' => [
                'parentId' => null,
                'companyIds' => ['assigned_company_a', 'assigned_company_b'],
                'expectedMsg' => [
                    ['text' => 'Parent company 0 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => [],
                'expectedLogEntry' => [
                    'message' => "Invalid parent company id .\nFailed to assign company %d, %d to parent company .",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_b',
                    ]
                ],
            ],
            'assign_parent_company_as_child' => [
                'parentId' => 'parent_company',
                'companyIds' => ['parent_company_a', 'assigned_company_a'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company '.
                            '<b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    ['text' =>
                        'Multi-tier assignments are not allowed. <b>Parent Company A</b> is a parent company and cannot'
                        .' be assigned to another parent company.',
                        'type' => 'error'
                    ],
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' =>
                        "Successfully assigned company %d to parent company %d." .
                        "\nFailed to assign company %d to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                        'parent_company_a',
                        'parent_company'
                    ]
                ],
            ],
            'company_already_assigned_to_different_parent_and_company_does_not_exist' => [
                'parentId' => 'parent_company',
                'companyIds' => ['assigned_company_a', 'assigned_company_d', 100500],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company '.
                            '<b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    [
                        'text' => 'A company cannot have multiple parents. The <b>Assigned Company C</b> company is '.
                            'already assigned to a different parent company.',
                        'type' => 'error'
                    ],
                    ['text' => 'Company 100500 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d to parent company %d.\n"
                        . "Failed to assign company %d, 100500 to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                        'assigned_company_d',
                        'parent_company',
                    ]
                ],
            ],
            'company1_does_not_exists' => [
                'parentId' => 'parent_company',
                'companyIds' => [100500, 'assigned_company_a'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company '.
                            '<b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    ['text' => 'Company 100500 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d to parent company %d.\n"
                        . "Failed to assign company 100500 to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                        'parent_company',
                    ]
                ],
            ],
            'company1_is_empty' => [
                'parentId' => 'parent_company',
                'companyIds' => ['', 'assigned_company_a'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company '.
                            '<b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    ['text' => 'Company 0 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d to parent company %d.\n"
                        . "Failed to assign company  to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                        'parent_company',
                    ]
                ],
            ],
            'company1_is_null' => [
                'parentId' => 'parent_company',
                'companyIds' => [null, 'assigned_company_a'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company '.
                            '<b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    ['text' => 'Company 0 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d to parent company %d.\n"
                        . "Failed to assign company  to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                        'parent_company',
                    ]
                ],
            ],
            'company2_does_not_exists' => [
                'parentId' => 'parent_company',
                'companyIds' => ['assigned_company_a', 100500],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company <b>Parent Company</b>.'
                        , 'type' => 'success'
                    ],
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    ['text' => 'Company 100500 does not exist.', 'type' => 'error']
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d to parent company %d.\n"
                        . "Failed to assign company 100500 to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                        'parent_company',
                    ]
                ],
            ],
            'empty_companyIds' => [
                'parentId' => 'parent_company',
                'companyIds' => [],
                'expectedMsg' => [
                    [
                        'text' => 'Cannot assign company to parent company <b>Parent Company</b>.'
                            .' No company specified.',
                        'type' => 'error'
                    ]
                ],
                'expectedItems' => ['assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Company id(s) not provided.",
                    'companyIdentifiers' => [
                    ]
                ],
            ],
            'duplicate_companyId' => [
                'parentId' => 'parent_company',
                'companyIds' => ['assigned_company_a', 'assigned_company_a'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A</b> to parent company '.
                            '<b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company',
                    ]
                ],
            ],
            'company_already_assigned_to_the_same_parent' => [
                'parentId' => 'parent_company',
                'companyIds' => ['assigned_company_a', 'assigned_company_c'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A, Assigned Company C</b> to parent company'
                            .' <b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d, %d to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_c',
                        'parent_company',
                    ]
                ],
            ],
            'child_company_as_parent' => [
                'parentId' => 'assigned_company_c',
                'companyIds' => ['assigned_company_a'],
                'expectedMsg' => [
                    [
                        'text' => 'Multi-tier assignments are not allowed. Cannot complete the company assignment'
                            .' because the selected parent company, <b>Assigned Company C</b> is already assigned'
                            .' to another parent company.',
                        'type' => 'error'
                    ],
                ],
                'expectedItems' => [],
                'expectedLogEntry' => [
                    'message' => "Failed to assign company %d to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_c'
                    ]
                ],
            ],
            'assign_to_itself' => [
                'parentId' => 'assigned_company_a',
                'companyIds' => ['assigned_company_a'],
                'expectedMsg' => [
                    ['text' => 'Cannot assign some companies to the selected parent.', 'type' => 'error'],
                    [
                        'text' => 'The <b>Assigned Company A</b> company cannot be assigned to itself.',
                        'type' => 'error'
                    ],
                ],
                'expectedItems' => [],
                'expectedLogEntry' => [
                    'message' => "Failed to assign company %d to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_a'
                    ]
                ],
            ],
            'valid_data' => [
                'parentId' => 'parent_company',
                'companyIds' => ['assigned_company_a', 'assigned_company_b'],
                'expectedMsg' => [
                    [
                        'text' => 'You have assigned <b>Assigned Company A, Assigned Company B</b> to parent company'
                            .' <b>Parent Company</b>.',
                        'type' => 'success'
                    ],
                ],
                'expectedItems' => ['assigned_company_a', 'assigned_company_b', 'assigned_company_c'],
                'expectedLogEntry' => [
                    'message' => "Successfully assigned company %d, %d to parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'assigned_company_b',
                        'parent_company',
                    ]
                ],
            ],
        ];
    }

    /**
     * Verify expected relation(s) exist for the given set of Company IDs
     *
     * @param int|null|string $parentId
     * @param int[] $expectedCompanyIds
     * @return void
     */
    private function assertRelationExists($parentId, $expectedCompanyIds): void
    {
        $relationManager = Bootstrap::getObjectManager()->get(RelationManagerInterface::class);
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(RelationInterface::PARENT_ID, $parentId)
            ->create();

        $relations = $relationManager->getList($searchCriteria);
        $companyIds = [];
        array_map(function ($relation) use (&$companyIds) {
            /** @var $relation RelationInterface */
            $companyIds[] = $relation->getCompanyId();
        }, $relations->getItems());
        $this->assertEquals($expectedCompanyIds, $companyIds);
    }
}
