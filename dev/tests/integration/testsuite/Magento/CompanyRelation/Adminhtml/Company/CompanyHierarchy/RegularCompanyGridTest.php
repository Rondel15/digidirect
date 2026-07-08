<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2023 Adobe
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

namespace Magento\CompanyRelation\Adminhtml\Company\CompanyHierarchy;

use Laminas\Http\Headers;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

class RegularCompanyGridTest extends AbstractController
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->url = Bootstrap::getObjectManager()->create(UrlInterface::class);
        $this->_objectManager->get(Auth::class)->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            \Magento\Customer\Test\Fixture\Customer::class,
            [
                CustomerInterface::FIRSTNAME => 'Wood',
                CustomerInterface::LASTNAME => 'Woody'

            ],
            'regular_companyA_admin'
        ),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'regular_companyB_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'parent_company_admin'),
        DataFixture(\Magento\Customer\Test\Fixture\Customer::class, as: 'assigned_company_a_admin'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'sales_rep_user'),
        DataFixture(
            \Magento\Company\Test\Fixture\Company::class,
            [
                CompanyInterface::NAME => 'Regular Company A',
                CompanyInterface::LEGAL_NAME => 'Happy Company A',
                CompanyInterface::COMPANY_EMAIL => 'sales@regular-company-a.adobe.com',
                CompanyInterface::COUNTRY_ID => 'UA',
                CompanyInterface::REGION_ID => 1095,
                CompanyInterface::SUPER_USER_ID => '$regular_companyA_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'regular_companyA'
        ),
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
                CompanyInterface::NAME => 'Regular Company B',
                CompanyInterface::COMPANY_EMAIL => 'sales@regular-company-b.adobe.com',
                CompanyInterface::SUPER_USER_ID => '$regular_companyB_admin.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep_user.id$',
            ],
            'regular_companyB'
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
            \Magento\CompanyRelation\Test\Fixture\AssignRelations::class,
            [
                \Magento\CompanyRelation\Api\Data\RelationInterface::PARENT_ID => '$parent_company.id$',
                'relations' => [
                    [\Magento\CompanyRelation\Api\Data\RelationInterface::COMPANY_ID => '$assigned_company_a.id$'],
                ],
            ],
        ),
        DataFixture(Indexer::class, as: 'indexer')
    ]
    /**
     * @dataProvider totalItemsCountDataProvider
     */
    public function testGridItems($fixture, $filters, $expectedItems)
    {
        $filters = array_map(function ($value) {
            return (is_callable($value)) ? call_user_func($value) : $value;
        }, $filters);

        $entityFilter = $fixture ? [
            'entity_id' => DataFixtureStorageManager::getStorage()->get($fixture)->getData(CompanyInterface::COMPANY_ID)
        ] : [];

        $params = array_merge($filters, $entityFilter, [
            'namespace' => 'regular_company_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ]);

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('items', $responseBody);
        $resultCompanyNames = array_column($responseBody['items'], 'company_name');
        $this->assertEqualsCanonicalizing($expectedItems, $resultCompanyNames);
    }

    public static function totalItemsCountDataProvider(): array
    {
        return [
            'should_see_all_unassigned' => [
                'fixture' => 'parent_company',
                'filters' => [],
                'expectedItems' => ["Regular Company A", "Regular Company B"]
            ],
            'should_be_empty_for_nonsaved_new_company' => [
                'fixture' => '',
                'filters' => [],
                'expectedItems' => []
            ],
            'current_company_excluded' => [
                'fixture' => 'regular_companyA',
                'filters' => [],
                'expectedItems' => ["Regular Company B"]
            ],
            'search' => [
                'fixture' => 'parent_company',
                'filters' => ['search' => 'Happy'], // Match regular_companyA LEGAL_NAME
                'expectedItems' => ["Regular Company A"]
            ],
            'search_without_results' => [
                'fixture' => 'parent_company',
                'filters' => ['search' => 'TextWithNoSense'],
                'expectedItems' => []
            ],
            'filter_entity_ids' => [
                'fixture' => 'parent_company',
                'filters' => [
                    'filters[entity_id][from]' => function () {
                        // first company in the list of applied fixtures
                        return DataFixtureStorageManager::getStorage()
                            ->get('regular_companyA')->getData(CompanyInterface::COMPANY_ID);
                    },
                    'filters[entity_id][to]' => function () {
                        // last company in the list of applied fixtures
                        return DataFixtureStorageManager::getStorage()
                            ->get('assigned_company_a')->getData(CompanyInterface::COMPANY_ID);
                    },
                ],
                'expectedItems' => ["Regular Company A", "Regular Company B"]
            ],
            'filter_entity_ids_exclude_second_result' => [
                'fixture' => 'parent_company',
                'filters' => [
                    'filters[entity_id][from]' => function () {
                        // first company in the list of applied fixtures
                        return DataFixtureStorageManager::getStorage()
                            ->get('regular_companyA')->getData(CompanyInterface::COMPANY_ID);
                    },
                    'filters[entity_id][to]' => function () {
                        // company fixture before the target company in the list of applied fixtures
                        return DataFixtureStorageManager::getStorage()
                            ->get('parent_company')->getData(CompanyInterface::COMPANY_ID);
                    },
                ],
                'expectedItems' => ["Regular Company A"]
            ],
            'filter_by_company_name' => [
                'fixture' => 'parent_company',
                'filters' => ['filters[company_name]' => 'Regular Company B'],
                'expectedItems' => ["Regular Company B"]
            ],
            'filter_by_country' => [
                'fixture' => 'parent_company',
                'filters' => ['filters[country_id]' => 'UA'],
                'expectedItems' => ["Regular Company A"]
            ],
            'filter_by_company_admin' => [
                'fixture' => 'parent_company',
                'filters' => ['filters[company_admin]' => 'Woody'],
                'expectedItems' => ["Regular Company A"]
            ],
            'filter_by_company_email' => [
                'fixture' => 'parent_company',
                'filters' => ['filters[company_email]' => 'sales@regular-company-a.adobe.com'],
                'expectedItems' => ["Regular Company A"]
            ],
            'filters_combined' => [
                'fixture' => 'parent_company',
                'filters' => [
                    'search' => 'Company', // Match Regular Company A and Regular Company B
                    'filters[country_id]' => 'UA',
                    'filters[company_admin]' => 'Woody',
                    'filters[company_email]' => 'sales@regular-company-a.adobe.com',
                    'filters[entity_id][from]' => function () {
                        // first company in the list of applied fixtures
                        return DataFixtureStorageManager::getStorage()
                            ->get('regular_companyA')->getData(CompanyInterface::COMPANY_ID);
                    },
                ],
                'expectedItems' => ["Regular Company A"]
            ],
        ];
    }

    #[
        AppArea('adminhtml'),
    ]
    public function testNonExistingCompany()
    {
        $params = [
            'namespace' => 'regular_company_listing',
            'entity_id' => 100501,
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);
        $this->assertArrayHasKey('totalRecords', $responseBody);
        $this->assertEquals(0, $responseBody['totalRecords']);
        $this->assertCount(0, $responseBody['items']);
    }
}
