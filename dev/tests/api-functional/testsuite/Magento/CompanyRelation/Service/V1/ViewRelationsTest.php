<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyRelation\Service\V1;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Tests company relation service WebAPI
 */
#[
    Config('btob/website_configuration/company_active', 1)
]
class ViewRelationsTest extends WebapiAbstract
{
    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->dataObjectProcessor = $objectManager->get(DataObjectProcessor::class);
        parent::setUp();
    }

    #[
        DataFixture(Customer::class, as: 'regularCompanySuperUser'),
        DataFixture(User::class, as: 'regularCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'parentCompany1SuperUser'),
        DataFixture(User::class, as: 'parentCompany1SalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompany1SuperUser'),
        DataFixture(User::class, as: 'childCompany1SalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompany2SuperUser'),
        DataFixture(User::class, as: 'childCompany2SalesRepresentative'),
        DataFixture(Customer::class, as: 'parentCompany2SuperUser'),
        DataFixture(User::class, as: 'parentCompany2SalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompany3SuperUser'),
        DataFixture(User::class, as: 'childCompany3SalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$regularCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$regularCompanySuperUser.id$'
            ],
            as: 'regularCompany'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompany1SalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompany1SuperUser.id$'
            ],
            as: 'parentCompany1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompany2SalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompany2SuperUser.id$'
            ],
            as: 'parentCompany2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompany1SalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompany1SuperUser.id$'
            ],
            as: 'childCompany1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompany2SalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompany2SuperUser.id$'
            ],
            as: 'childCompany2'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompany3SalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompany3SuperUser.id$'
            ],
            as: 'childCompany3'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parentCompany1.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$childCompany1.id$'],
                    [RelationInterface::COMPANY_ID => '$childCompany2.id$']
                ]
            ]
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parentCompany2.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$childCompany3.id$']
                ]
            ]
        )
    ]
    /**
     * @dataProvider viewDataProvider
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testView(array $filters, array $resultRelations): void
    {
        $this->_markTestAsRestOnly('Functionality available in REST mode only.');

        $regularCompanyId = $this->fixtures->get('regularCompany')->getId();
        $parentCompany1Id = $this->fixtures->get('parentCompany1')->getId();
        $childCompany1Id = $this->fixtures->get('childCompany1')->getId();
        $childCompany2Id = $this->fixtures->get('childCompany2')->getId();
        $parentCompany2Id = $this->fixtures->get('parentCompany2')->getId();
        $childCompany3Id = $this->fixtures->get('childCompany3')->getId();
        $invalidParentCompanyId = -1;
        $invalidChildCompanyId = -1;
        $emptyParentCompanyId = null;
        $emptyChildCompanyId = null;

        $searchCriteriaBuilder = $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(20);

        // Add filters to searchCriteria
        foreach ($filters as $filter) {
            $searchCriteriaBuilder->addFilter($filter['field'], ${$filter['value']});
        }
        $searchCriteria = $searchCriteriaBuilder->create();

        $actualResult = $this->makeWebApiCall($searchCriteria);

        // Configure the expected result
        $expectedResult = [];
        foreach ($resultRelations as $resultRelation) {
            $expectedResult[] = [
                RelationInterface::COMPANY_ID => ${$resultRelation[RelationInterface::COMPANY_ID]},
                RelationInterface::PARENT_ID => ${$resultRelation[RelationInterface::PARENT_ID]}
            ];
        }

        $this->assertEquals($expectedResult, $actualResult['items']);
        $this->assertEquals($searchCriteria->__toArray(), $actualResult['search_criteria']);
        $this->assertEquals(count($expectedResult), $actualResult['total_count']);
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function viewDataProvider(): array
    {
        return [
            'testViewWithNoIds' => [
                'filters' => [],
                'resultRelations' => [
                    [
                        RelationInterface::COMPANY_ID => 'childCompany1Id',
                        RelationInterface::PARENT_ID => 'parentCompany1Id'
                    ],
                    [
                        RelationInterface::COMPANY_ID => 'childCompany2Id',
                        RelationInterface::PARENT_ID => 'parentCompany1Id'
                    ],
                    [
                        RelationInterface::COMPANY_ID => 'childCompany3Id',
                        RelationInterface::PARENT_ID => 'parentCompany2Id'
                    ]
                ]
            ],
            'testViewWithParentId' => [
                'filters' => [
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'parentCompany1Id']
                ],
                'resultRelations' => [
                    [
                        RelationInterface::COMPANY_ID => 'childCompany1Id',
                        RelationInterface::PARENT_ID => 'parentCompany1Id'
                    ],
                    [
                        RelationInterface::COMPANY_ID => 'childCompany2Id',
                        RelationInterface::PARENT_ID => 'parentCompany1Id'
                    ]
                ]
            ],
            'testViewWithCompanyId' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'childCompany1Id']
                ],
                'resultRelations' => [
                    [
                        RelationInterface::COMPANY_ID => 'childCompany1Id',
                        RelationInterface::PARENT_ID => 'parentCompany1Id'
                    ]
                ]
            ],
            'testViewWithParentAndCompanyId' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'childCompany1Id'],
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'parentCompany1Id']
                ],
                'resultRelations' => [
                    [
                        RelationInterface::COMPANY_ID => 'childCompany1Id',
                        RelationInterface::PARENT_ID => 'parentCompany1Id'
                    ]
                ]
            ],
            'testViewWithCompanyIdAsRegularCompany' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'regularCompanyId']
                ],
                'resultRelations' => []
            ],
            'testViewWithParentIdAsRegularCompany' => [
                'filters' => [
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'regularCompanyId']
                ],
                'resultRelations' => []
            ],
            'testInvalidCompanyId' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'invalidChildCompanyId'],
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'parentCompany1Id']
                ],
                'resultRelations' => []
            ],
            'testEmptyCompanyId' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'emptyChildCompanyId'],
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'parentCompany1Id']
                ],
                'resultRelations' => []
            ],
            'testInvalidParentId' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'childCompany1Id'],
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'invalidParentCompanyId']
                ],
                'resultRelations' => []
            ],
            'testEmptyParentId' => [
                'filters' => [
                    ['field' => RelationInterface::COMPANY_ID, 'value' => 'childCompany1Id'],
                    ['field' => RelationInterface::PARENT_ID, 'value' => 'emptyParentCompanyId']
                ],
                'resultRelations' => []
            ]
        ];
    }

    #[
        DataFixture(Customer::class, as: 'parentCompanySuperUser'),
        DataFixture(User::class, as: 'parentCompanySalesRepresentative'),
        DataFixture(Customer::class, as: 'childCompanySuperUser'),
        DataFixture(User::class, as: 'childCompanySalesRepresentative'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$parentCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$parentCompanySuperUser.id$'
            ],
            as: 'parentCompany'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$childCompanySalesRepresentative.id$',
                CompanyInterface::SUPER_USER_ID => '$childCompanySuperUser.id$'
            ],
            as: 'childCompany'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parentCompany.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$childCompany.id$'],
                ]
            ]
        )
    ]
    public function testInvalidParentIdKey()
    {
        $this->_markTestAsRestOnly('Functionality available in REST mode only.');

        $childCompanyId = $this->fixtures->get('childCompany')->getId();
        $parentCompanyId = $this->fixtures->get('parentCompany')->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(RelationInterface::COMPANY_ID, $childCompanyId)
            ->addFilter('test_id', $parentCompanyId)
            ->setCurrentPage(1)
            ->setPageSize(20)
            ->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches(
            '/Internal Error. Details are available in Magento log file. Report ID: webapi-*/'
        );

        $this->makeWebApiCall($searchCriteria);
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @return array
     */
    private function makeWebApiCall(SearchCriteria $searchCriteria): array
    {
        $searchData = $this->dataObjectProcessor
            ->buildOutputDataArray($searchCriteria, SearchCriteriaInterface::class);
        $requestData = ['searchCriteria' => $searchData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/company/relations/' . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        return $this->_webApiCall($serviceInfo);
    }
}
