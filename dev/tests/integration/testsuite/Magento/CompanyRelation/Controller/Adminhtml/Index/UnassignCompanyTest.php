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
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\CompanyRelation\Api\RelationManagerInterface;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Logging\Model\ResourceModel\Event\Collection;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\User\Test\Fixture\User;

class UnassignCompanyTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Magento_Company::manage';

    /**
     * @var string
     */
    protected $uri = 'backend/company_relation/index/unassignCompany';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtureStorage;

    /**
     * @var mixed
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
        DataFixture(Customer::class, as: 'parent_company_a_admin'),
        DataFixture(Customer::class, as: 'parent_company_b_admin'),
        DataFixture(Customer::class, as: 'assigned_company_a_admin'),
        DataFixture(Customer::class, as: 'assigned_company_b_admin'),
        DataFixture(Customer::class, as: 'assigned_company_c_admin'),
        DataFixture(Customer::class, as: 'normal_company_a_admin'),
        DataFixture(User::class, as: 'sales_rep_user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::NAME => 'Parent Company A',
                CompanyInterface::SUPER_USER_ID => '$parent_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company_a'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::NAME => 'Parent Company B',
                CompanyInterface::SUPER_USER_ID => '$parent_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'parent_company_b'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company A',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_a'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company B',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_b_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_b'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::NAME => 'Assigned Company C',
                CompanyInterface::SUPER_USER_ID => '$assigned_company_c_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'assigned_company_c'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::NAME => 'Normal Company A',
                CompanyInterface::SUPER_USER_ID => '$normal_company_a_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'normal_company_a'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID => '$parent_company_a.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$assigned_company_a.id$'],
                    [RelationInterface::COMPANY_ID => '$assigned_company_b.id$'],
                ],
            ],
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID => '$parent_company_b.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$assigned_company_c.id$'],
                ],
            ],
        ),
    ]
    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        int|string|null $parentId,
        int|string|null $companyId,
        string $expectedMsg,
        bool $isError,
        array $expectedRelationsAfterUnassign,
        array $expectedLogEntry
    ): void {
        $parentId = is_int($parentId) || empty($parentId) ?
            $parentId : (int) $this->fixtureStorage->get($parentId)->getData(CompanyInterface::COMPANY_ID);
        $companyId = is_int($companyId) || empty($companyId) ?
            $companyId : (int) $this->fixtureStorage->get($companyId)->getData(CompanyInterface::COMPANY_ID);

        foreach ($expectedRelationsAfterUnassign as $key => $fixtureName) {
            $company = $this->fixtureStorage->get($fixtureName);
            $expectedRelationsAfterUnassign[$key] = (int) $company->getData(CompanyInterface::COMPANY_ID);
        }

        $this->getRequest()->setMethod($this->httpMethod);
        $this->getRequest()->setPostValue([
            'parent_id' => $parentId,
            'company_id' => $companyId
        ]);
        $this->dispatch($this->uri);
        $responseContent = \json_decode($this->getResponse()->getContent(), true);
        $this->assertEquals(
            $isError,
            array_key_exists('error', $responseContent) && $responseContent['error'] === true
        );
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertRelationExists($parentId, $expectedRelationsAfterUnassign);
        $this->assertLastAdminLogEntry($expectedLogEntry);
        $this->assertEquals($expectedMsg, $responseContent['message']);
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function executeDataProvider(): array
    {
        return [
            'parent_id_does_not_exists' => [
                'parentId' => 100500,
                'companyId' => 'assigned_company_a',
                'expectedMsg' => 'Parent company 100500 does not exist.',
                'isError' => true,
                'expectedRelationsAfterUnassign' => [],
                'expectedLogEntry' => [
                    'message' => "Invalid parent company id 100500.\n"
                        . "Failed to unassign company %d from parent company 100500.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                    ]
                ],
            ],
            'parent_id_is_empty' => [
                'parentId' => '',
                'companyId' => 'assigned_company_a',
                'expectedMsg' => 'Parent company 0 does not exist.',
                'isError' => true,
                'expectedRelationsAfterUnassign' => [],
                'expectedLogEntry' => [
                    'message' => "Invalid parent company id .\n"
                        . "Failed to unassign company %d from parent company .",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                    ]
                ],
            ],
            'parent_id_is_null' => [
                'parentId' => null,
                'companyId' => 'assigned_company_a',
                'expectedMsg' => 'Parent company 0 does not exist.',
                'isError' => true,
                'expectedRelationsAfterUnassign' => [],
                'expectedLogEntry' => [
                    'message' => "Invalid parent company id .\n"
                        . "Failed to unassign company %d from parent company .",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                    ]
                ],
            ],
            'company_id_does_not_exists' => [
                'parentId' => 'parent_company_a',
                'companyId' => 100500,
                'expectedMsg' => 'Company 100500 does not exist.',
                'isError' => true,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Invalid company id 100500.\n"
                        . "Failed to unassign company 100500 from parent company %d.",
                    'companyIdentifiers' => [
                        'parent_company_a',
                    ]
                ],
            ],
            'company_id_is_empty' => [
                'parentId' => 'parent_company_a',
                'companyId' => '',
                'expectedMsg' => 'Company 0 does not exist.',
                'isError' => true,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Invalid company id .\n"
                        . "Failed to unassign company  from parent company %d.",
                    'companyIdentifiers' => [
                        'parent_company_a',
                    ]
                ],
            ],
            'company_id_is_null' => [
                'parentId' => 'parent_company_a',
                'companyId' => null,
                'expectedMsg' => 'Company 0 does not exist.',
                'isError' => true,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Invalid company id .\n"
                        . "Failed to unassign company  from parent company %d.",
                    'companyIdentifiers' => [
                        'parent_company_a',
                    ]
                ],
            ],
            'parent_id_is_a_normal_company' => [
                'parentId' => 'normal_company_a',
                'companyId' => 'assigned_company_a',
                'expectedMsg' => 'You have unassigned the <b>Assigned Company A</b> company from' .
                    ' parent company <b>Normal Company A</b>. Review the configuration for the Assigned Company A' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => [],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'normal_company_a',
                    ]
                ],
            ],
            'company_id_is_a_normal_company' => [
                'parentId' => 'parent_company_a',
                'companyId' => 'normal_company_a',
                'expectedMsg' => 'You have unassigned the <b>Normal Company A</b> company from' .
                    ' parent company <b>Parent Company A</b>. Review the configuration for the Normal Company A' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'normal_company_a',
                        'parent_company_a',
                    ]
                ],
            ],
            'parent_id_and_companyId_are_the_same' => [
                'parentId' => 'parent_company_a',
                'companyId' => 'parent_company_a',
                'expectedMsg' => 'You have unassigned the <b>Parent Company A</b> company from' .
                    ' parent company <b>Parent Company A</b>. Review the configuration for the Parent Company A' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'parent_company_a',
                        'parent_company_a',
                    ]
                ],
            ],
            'parent_id_is_not_a_parent_company' => [
                'parentId' => 'assigned_company_a',
                'companyId' => 'assigned_company_b',
                'expectedMsg' => 'You have unassigned the <b>Assigned Company B</b> company from' .
                    ' parent company <b>Assigned Company A</b>. Review the configuration for the Assigned Company B' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => [],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_b',
                        'assigned_company_a',
                    ]
                ],
            ],
            'company_id_is_not_a_child_company' => [
                'parentId' => 'parent_company_a',
                'companyId' => 'parent_company_b',
                'expectedMsg' => 'You have unassigned the <b>Parent Company B</b> company from' .
                    ' parent company <b>Parent Company A</b>. Review the configuration for the Parent Company B' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'parent_company_b',
                        'parent_company_a',
                    ]
                ],
            ],
            'company_id_has_a_different_parent_company' => [
                'parentId' => 'parent_company_a',
                'companyId' => 'assigned_company_c',
                'expectedMsg' => 'You have unassigned the <b>Assigned Company C</b> company from' .
                    ' parent company <b>Parent Company A</b>. Review the configuration for the Assigned Company C' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => ['assigned_company_a', 'assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_c',
                        'parent_company_a',
                    ]
                ],
            ],
            'valid_data' => [
                'parentId' => 'parent_company_a',
                'companyId' => 'assigned_company_a',
                'expectedMsg' => 'You have unassigned the <b>Assigned Company A</b> company from' .
                    ' parent company <b>Parent Company A</b>. Review the configuration for the Assigned Company A' .
                    ' company to verify that Customer Group and other settings are still valid.',
                'isError' => false,
                'expectedRelationsAfterUnassign' => ['assigned_company_b'],
                'expectedLogEntry' => [
                    'message' => "Successfully unassigned company %d from parent company %d.",
                    'companyIdentifiers' => [
                        'assigned_company_a',
                        'parent_company_a',
                    ]
                ],
            ],
        ];
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
     * Verify expected relation(s) exist for the given set of Company IDs
     *
     * @param int|string|null $parentId
     * @param int[] $expectedCompanyIds
     * @return void
     */
    private function assertRelationExists(int|string|null $parentId, array $expectedCompanyIds): void
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
