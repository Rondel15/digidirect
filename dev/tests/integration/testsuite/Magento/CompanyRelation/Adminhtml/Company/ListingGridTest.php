<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyRelation\Adminhtml\Company;

use Laminas\Http\Headers;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\CompanyRelation\Test\Fixture\AssignRelations;
use Magento\User\Test\Fixture\User as AdminUserFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Tests presence and functionality of additional columns added to the admin company listing grid.
 */
class ListingGridTest extends AbstractController
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = $this->_objectManager->create(UrlInterface::class);
        $this->auth = $this->_objectManager->get(Auth::class);

        $this->auth->login(Bootstrap::ADMIN_NAME, Bootstrap::ADMIN_PASSWORD);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->auth->logout();
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'regularCompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'parent1CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'parent2CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'child1CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'child2CompanyAdmin'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Regular Company',
                CompanyInterface::SUPER_USER_ID => '$regularCompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'regularCompany'
        ),
        // Company 1 hierarchy
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Parent 1 Company',
                CompanyInterface::SUPER_USER_ID => '$parent1CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parent1Company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Child 1 Company',
                CompanyInterface::SUPER_USER_ID => '$child1CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'child1Company'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent1Company.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child1Company.id$'],
                ]
            ]
        ),
        // Company 2 hierarchy
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Parent 2 Company',
                CompanyInterface::SUPER_USER_ID => '$parent2CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parent2Company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Child 2 Company',
                CompanyInterface::SUPER_USER_ID => '$child2CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'child2Company'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent2Company.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child2Company.id$'],
                ]
            ]
        ),
    ]
    public function testParentAndCompanyTypeColumnValues()
    {
        $dataFixtureStorage = DataFixtureStorageManager::getStorage();

        $params = [
            'namespace' => 'company_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);

        $this->assertCount(5, $responseBody['items']);

        $regularCompany = $dataFixtureStorage->get('regularCompany');
        $parent1Company = $dataFixtureStorage->get('parent1Company');
        $child1Company = $dataFixtureStorage->get('child1Company');
        $parent2Company = $dataFixtureStorage->get('parent2Company');
        $child2Company = $dataFixtureStorage->get('child2Company');

        $expectedCompanyData = [
            [
                'company_id' => $regularCompany->getId(),
                'parent_company_name' => null,
                'company_type' => 'Company'
            ],
            [
                'company_id' => $parent1Company->getId(),
                'parent_company_name' => null,
                'company_type' => 'Parent'
            ],
            [
                'company_id' => $child1Company->getId(),
                'parent_company_name' => 'Parent 1 Company',
                'company_type' => 'Child'
            ],
            [
                'company_id' => $parent2Company->getId(),
                'parent_company_name' => null,
                'company_type' => 'Parent'
            ],
            [
                'company_id' => $child2Company->getId(),
                'parent_company_name' => 'Parent 2 Company',
                'company_type' => 'Child'
            ],
        ];

        foreach ($responseBody['items'] as $idx => $companyItem) {
            $expectedCompanyItem = $expectedCompanyData[$idx];

            $this->assertEquals($expectedCompanyItem['company_id'], $companyItem['entity_id']);
            $this->assertEquals($expectedCompanyItem['parent_company_name'], $companyItem['parent_company_name']);
            $this->assertEquals($expectedCompanyItem['company_type'], $companyItem['company_type']);
        }
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'regularCompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'parent1CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'parent2CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'child1CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'child2CompanyAdmin'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        // First company hierarchy
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Alpha Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent1CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parent1Company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Beta Child Company',
                CompanyInterface::SUPER_USER_ID => '$child1CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'child1Company'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent1Company.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child1Company.id$'],
                ]
            ]
        ),
        // Second company hierarchy
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Gamma Parent Company',
                CompanyInterface::SUPER_USER_ID => '$parent2CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parent2Company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Delta Child Company',
                CompanyInterface::SUPER_USER_ID => '$child2CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'child2Company'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent2Company.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child2Company.id$'],
                ]
            ]
        ),
        // Regular company
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Epsilon Company',
                CompanyInterface::SUPER_USER_ID => '$regularCompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'regularCompany'
        ),
    ]
    /**
     * @dataProvider fulltextSearchTestDataProvider()
     */
    public function testFulltextSearchInHierarchy(string $searchTerm, array $expectedItems): void
    {
        $params = [
            'namespace' => 'company_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
            'keywordUpdated' => true,
            'search' => $searchTerm
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertCompanySearchResults($responseBody['items'], $expectedItems);
    }

    /**
     * Company search results assertion helper.
     *
     * @param array $actualItems
     * @param array $expectedItems
     * @return void
     */
    private function assertCompanySearchResults(array $actualItems, array $expectedItems): void
    {
        $this->assertEquals(
            count($actualItems),
            count($expectedItems),
            "Items count in search results does not match expected count."
        );
        foreach ($expectedItems as $itemKey => $expectedItem) {
            foreach ($expectedItem as $fieldKey => $expectedItemFieldValue) {
                $this->assertEquals(
                    $actualItems[$itemKey][$fieldKey],
                    $expectedItemFieldValue,
                    "The actual value of `{$fieldKey}` in the result does not match the expected value."
                );
            }
        }
    }

    /**
     * Data provider for company search test.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array[]
     */
    public static function fulltextSearchTestDataProvider(): array
    {
        return [
            "search by parent ambiguous" => [
                'searchTerm' => "Parent",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Alpha Parent Company",
                        'company_type' => "Parent",
                        'parent_company_name' => null,
                    ],
                    1 => [
                        'company_name' => "Beta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Alpha Parent Company",
                    ],
                    2 => [
                        'company_name' => "Gamma Parent Company",
                        'company_type' => "Parent",
                        'parent_company_name' => null,
                    ],
                    3 => [
                        'company_name' => "Delta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Gamma Parent Company",
                    ],
                ]
            ],
            "search by exact parent company name" => [
                'searchTerm' => "Alpha",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Alpha Parent Company",
                        'company_type' => "Parent",
                        'parent_company_name' => null,
                    ],
                    1 => [
                        'company_name' => "Beta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Alpha Parent Company",
                    ],
                ]
            ],
            "search by child company name ambiguous" => [
                'searchTerm' => "Child",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Beta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Alpha Parent Company",
                    ],
                    1 => [
                        'company_name' => "Delta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Gamma Parent Company",
                    ],
                ]
            ],
            "search by exact child company name" => [
                'searchTerm' => "Delta",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Delta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Gamma Parent Company",
                    ],
                ]
            ],
            "search by regular company name" => [
                'searchTerm' => "Epsilon",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Epsilon Company",
                        'company_type' => "Company",
                        'parent_company_name' => null,
                    ],
                ]
            ],
            "search by company name ambiguous" => [
                'searchTerm' => "Company",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Alpha Parent Company",
                        'company_type' => "Parent",
                        'parent_company_name' => null,
                    ],
                    1 => [
                        'company_name' => "Beta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Alpha Parent Company",
                    ],
                    2 => [
                        'company_name' => "Gamma Parent Company",
                        'company_type' => "Parent",
                        'parent_company_name' => null,
                    ],
                    3 => [
                        'company_name' => "Delta Child Company",
                        'company_type' => "Child",
                        'parent_company_name' => "Gamma Parent Company",
                    ],
                    4 => [
                        'company_name' => "Epsilon Company",
                        'company_type' => "Company",
                        'parent_company_name' => null,
                    ],
                ]
            ],
            "search by at most three searchTerm company name" => [
                'searchTerm' => "Eps",
                'expectedItems' => [
                    0 => [
                        'company_name' => "Epsilon Company",
                        'company_type' => "Company",
                        'parent_company_name' => null,
                    ],
                ]
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'regularCompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'parent1CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'parent2CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'child1CompanyAdmin'),
        DataFixture(CustomerFixture::class, as: 'child2CompanyAdmin'),
        DataFixture(AdminUserFixture::class, as: 'salesRepUser'),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Regular Company',
                CompanyInterface::SUPER_USER_ID => '$regularCompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'regularCompany'
        ),
        // Company 1 hierarchy
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Parent 1 Company',
                CompanyInterface::SUPER_USER_ID => '$parent1CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parent1Company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Child 1 Company',
                CompanyInterface::SUPER_USER_ID => '$child1CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'child1Company'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent1Company.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child1Company.id$'],
                ]
            ]
        ),
        // Company 2 hierarchy
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Parent 2 Company',
                CompanyInterface::SUPER_USER_ID => '$parent2CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'parent2Company'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                CompanyInterface::NAME => 'Child 2 Company',
                CompanyInterface::SUPER_USER_ID => '$child2CompanyAdmin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$salesRepUser.id$',
            ],
            as: 'child2Company'
        ),
        DataFixture(
            AssignRelations::class,
            [
                RelationInterface::PARENT_ID =>'$parent2Company.id$',
                'relations' => [
                    [RelationInterface::COMPANY_ID => '$child2Company.id$'],
                ]
            ]
        ),
    ]
    /**
     * @dataProvider filterByCompanyTypeDataProvider
     */
    public function testCanFilterByCompanyType(string $filter, int $expectedCount)
    {
        $params = [
            'namespace' => 'company_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
            'filters' => [
                'placeholder' => true,
                'company_type' => [
                    'value' => $filter,
                    'placeholder' => false,
                ],
            ],
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);

        $this->assertEquals(
            $expectedCount,
            $responseBody['totalRecords'],
        );

        $this->assertCount(
            $expectedCount,
            $responseBody['items'],
            sprintf('Company type filter "%s" should return %d companies', $filter, $expectedCount)
        );

        foreach ($responseBody['items'] as $companyItem) {
            $this->assertEquals(
                $filter,
                $companyItem['company_type'],
                sprintf('Company type filter "%s" should return only "%s" companies', $filter, $filter)
            );
        }
    }

    public static function filterByCompanyTypeDataProvider(): array
    {
        return [
            'Filter by Parent' => [
                'Parent',
                2
            ],
            'Filter by Child' => [
                'Child',
                2
            ],
            'Filter by Company' => [
                'Company',
                1
            ]
        ];
    }
}
