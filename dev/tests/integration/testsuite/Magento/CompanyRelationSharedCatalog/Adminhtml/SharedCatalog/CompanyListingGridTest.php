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

namespace Magento\CompanyRelationSharedCatalog\Adminhtml\SharedCatalog;

use Laminas\Http\Headers;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\CompanyRelation\Api\Data\RelationInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;
use Magento\SharedCatalog\Model\Form\Storage\Company\Builder;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory;
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
 * Tests presence and functionality of additional columns added to the admin shared catalog company listing grid.
 */
class CompanyListingGridTest extends AbstractController
{
    /** @var UrlInterface */
    private $url;

    /** @var Auth */
    private $auth;

    /** @var SharedCatalogRepositoryInterface */
    private $sharedCatalogRepository;

    /** @var CompanyFactory */
    private $companyStorageFactory;

    /** @var Builder */
    private $companyStorageBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = $this->_objectManager->create(UrlInterface::class);
        $this->auth = $this->_objectManager->get(Auth::class);
        $this->sharedCatalogRepository = $this->_objectManager->get(SharedCatalogRepositoryInterface::class);
        $this->companyStorageFactory = $this->_objectManager->get(CompanyFactory::class);
        $this->companyStorageBuilder = $this->_objectManager->get(Builder::class);

        $this->auth->login(Bootstrap::ADMIN_NAME, Bootstrap::ADMIN_PASSWORD);
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
        // Construct the request
        $params = [
            'namespace' => 'shared_catalog_company_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $sharedCatalogId = 1;
        $configureKey = hash('sha256', implode('', [$sharedCatalogId, microtime()]));
        $companyStorage = $this->companyStorageFactory->create(['key' => $configureKey]);
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $this->companyStorageBuilder->build($companyStorage, $sharedCatalog);

        $this->dispatch("backend/mui/index/render/configure_key/$configureKey?" . http_build_query($params));

        // Assertions
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);

        $this->assertCount(5, $responseBody['items']);

        $dataFixtureStorage = DataFixtureStorageManager::getStorage();
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
        // Construct the request
        $params = [
            'namespace' => 'shared_catalog_company_listing',
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
            'filters' => [
                'company_type' => $filter
            ],
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $sharedCatalogId = 1;
        $configureKey = hash('sha256', implode('', [$sharedCatalogId, microtime()]));
        $companyStorage = $this->companyStorageFactory->create(['key' => $configureKey]);
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $this->companyStorageBuilder->build($companyStorage, $sharedCatalog);

        $this->dispatch("backend/mui/index/render/configure_key/$configureKey?" . http_build_query($params));

        // Assertions
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->auth->logout();
    }
}
