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

namespace Magento\SharedCatalog\Controller\Category;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany as AssignCompanyFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\Request\Http;
use Magento\SharedCatalog\Test\Fixture\AssignCategory as AssignCategorySharedCatalogFixture;
use Magento\SharedCatalog\Test\Fixture\AssignCompany as AssignCompanySharedCatalogFixture;
use Magento\SharedCatalog\Test\Fixture\AssignProducts as AssignProductsSharedCatalogFixture;
use Magento\SharedCatalog\Test\Fixture\AssignProductsCategory as AssignProductsCategoryFixture;
use Magento\SharedCatalog\Test\Fixture\AssignTierPricesToProduct as AssignTierPricesToProductFixture;
use Magento\SharedCatalog\Test\Fixture\SetPermission as SetCatalogPermissionFixture;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog as SharedCatalogFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\User\Test\Fixture\User as UserFixture;
use Magento\TestFramework\TestCase\AbstractController;

#[
    AppArea('frontend'),
    AppIsolation(true),
    DbIsolation(false),
    DataFixture(SharedCatalogFixture::class, as: 'sc_', count: 2),
    DataFixture(CustomerFixture::class, as: 'customer_ab'),
    DataFixture(CustomerFixture::class, as: 'company_user_a'),
    DataFixture(CustomerFixture::class, as: 'company_user_b'),
    DataFixture(CustomerFixture::class, [CustomerInterface::GROUP_ID => 2], as: 'customer_c'),
    DataFixture(UserFixture::class, as: 'admin_user'),
    DataFixture(
        CompanyFixture::class,
        [
            CompanyInterface::SUPER_USER_ID => '$company_user_a.id$',
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin_user.id$'
        ],
        'company_a'
    ),
    DataFixture(
        CompanyFixture::class,
        [
            CompanyInterface::SUPER_USER_ID => '$company_user_b.id$',
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin_user.id$'
        ],
        'company_b'
    ),
    DataFixture(
        AssignCompanyFixture::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$customer_ab.id$'
        ],
    ),
    DataFixture(
        AssignCompanyFixture::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$customer_ab.id$',
        ],
    ),
    DataFixture(CategoryFixture::class, as: 'cat_', count: 2),
    DataFixture(ProductFixture::class, ['price' => 100], as: 'product_a'),
    DataFixture(
        ProductFixture::class,
        [
            'price' => 50,
            'tier_prices' => [
                [
                    'customer_group_id' => 2,
                    'qty' => 1,
                    'value' => 33
                ]
            ]
        ],
        as: 'product_b'
    ),
    DataFixture(AssignProductsCategoryFixture::class, ['products' => ['$product_a$'], 'category' => '$cat_1$']),
    DataFixture(AssignProductsCategoryFixture::class, ['products' => ['$product_b$'], 'category' => '$cat_2$']),
    DataFixture(
        AssignProductsSharedCatalogFixture::class,
        [
            'product_ids' => [
                '$product_a.id$'
            ],
            'catalog_id' => '$sc_1.id$'
        ]
    ),
    DataFixture(
        AssignProductsSharedCatalogFixture::class,
        [
            'product_ids' => [
                '$product_b.id$'
            ],
            'catalog_id' => '$sc_2.id$'
        ]
    ),
    DataFixture(
        AssignCategorySharedCatalogFixture::class,
        [
            'category' => '$cat_1$',
            'catalog_id' => '$sc_1.id$'
        ]
    ),
    DataFixture(
        AssignCategorySharedCatalogFixture::class,
        [
            'category' => '$cat_2$',
            'catalog_id' => '$sc_2.id$'
        ]
    ),
    DataFixture(
        AssignCompanySharedCatalogFixture::class,
        [
            'company' => '$company_a$',
            'catalog_id' => '$sc_1.id$'
        ]
    ),
    DataFixture(
        AssignCompanySharedCatalogFixture::class,
        [
            'company' => '$company_b$',
            'catalog_id' => '$sc_2.id$'
        ]
    ),
    DataFixture(
        AssignTierPricesToProductFixture::class,
        [
            'product_id' => '$product_a.id$',
            'shared_catalog_id' => '$sc_1.id$',
            'prices' => [
                [
                    'qty' => 1,
                    'value' => 90,
                    'value_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                ]
            ]
        ]
    ),
    DataFixture(
        AssignTierPricesToProductFixture::class,
        [
            'product_id' => '$product_b.id$',
            'shared_catalog_id' => '$sc_2.id$',
            'prices' => [
                [
                    'qty' => 1,
                    'value' => 40,
                    'value_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                ]
            ]
        ]
    ),
    DataFixture(
        SetCatalogPermissionFixture::class,
        [
            'category_id' => '$cat_1.id$',
            'catalog' => '$sc_2$',
            'type' => 'deny'
        ]
    ),
    DataFixture(
        SetCatalogPermissionFixture::class,
        [
            'category_id' => '$cat_2.id$',
            'catalog' => '$sc_1$',
            'type' => 'deny'
        ]
    )
]
class ViewTest extends AbstractController
{
    private const XML_COMPANY_PATH = 'btob/website_configuration/company_active';

    /**
     * @var bool
     */
    private $defaultCompanyChanged = false;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorageManager
     */
    private $fixture;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        $this->session = $this->_objectManager->get(\Magento\Customer\Model\Session::class);

        $scopeConfig = $this->_objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(self::XML_COMPANY_PATH, '1', ScopeInterface::SCOPE_WEBSITE);

        $this->reindexAllInvalid();
    }

    /**
     * Assert default company category access
     *
     * @param int $categoryId
     * @param bool $unauthorisedAccess
     * @param string $productName
     * @param string $specialPrice
     * @return void
     */
    private function assertCustomerCategoryAccess(
        int $categoryId,
        bool $unauthorisedAccess,
        string $productName,
        string $specialPrice
    ): void {
        $responseBody = $this->getResponseBody($categoryId);
        if ($unauthorisedAccess) {
            //if category permission is denied for this user group
            $this->assertEmpty($responseBody);
        } else {
            $this->assertNotEmpty($responseBody);
            $this->assertStringContainsString($productName, $responseBody);
            $this->assertStringContainsString($specialPrice, $responseBody);
        }
    }

    /**
     * User logs in with default company. Expected to see only Category A with Product A and price 90$
     *
     * @magentoConfigFixture btob/website_configuration/sharedcatalog_active 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @dataProvider getCustomerABDataDefaultCompanyA
     */
    public function testCategoryPermissionDefaultCompany(
        callable $categoryId,
        callable $productName,
        bool $unauthorisedAccess,
        string $specialPrice
    ) {
        $customerId = (int)$this->fixture->get('customer_ab')->getId();
        $this->session->loginById($customerId);
        $this->assertCustomerCategoryAccess(
            $categoryId(),
            $unauthorisedAccess,
            $productName(),
            $specialPrice
        );
    }

    /**
     * User's default company changed to company_b. Expected to see only Category B with Product B and price 40$
     *
     * @magentoConfigFixture btob/website_configuration/sharedcatalog_active 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @dataProvider getCustomerABDataDefaultCompanyB
     */
    public function testCategoryPermissionDefaultCompanyChanged(
        callable $categoryId,
        callable $productName,
        bool $unauthorisedAccess,
        string $specialPrice
    ) {
        $customerId = (int)$this->fixture->get('customer_ab')->getId();
        $this->changeDefaultCompany();
        $this->session->loginById($customerId);
        $this->assertCustomerCategoryAccess(
            $categoryId(),
            $unauthorisedAccess,
            $productName(),
            $specialPrice
        );
    }

    /**
     * User logs in and switches to Company B. Expected to see only Category B with product B and price 40$
     *
     * @magentoConfigFixture btob/website_configuration/sharedcatalog_active 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @dataProvider getCustomerABDataCompanySwitch
     */
    public function testCategoryPermissionSwitchCompany(
        callable $categoryId,
        callable $productName,
        bool $unauthorisedAccess,
        string $specialPrice
    ) {
        $customerId = (int)$this->fixture->get('customer_ab')->getId();
        $this->session->loginById($customerId);

        //switch to company_b
        $companyId = (int)$this->fixture->get('company_b')->getId();
        $this->setHttpContext($companyId);

        $this->assertCustomerCategoryAccess(
            $categoryId(),
            $unauthorisedAccess,
            $productName(),
            $specialPrice
        );
    }

    /**
     * User is logged out. Default catalog is displayed
     *
     * @magentoConfigFixture btob/website_configuration/sharedcatalog_active 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @dataProvider getNotLoggedCustomerData
     */
    public function testCategoryPermissionNotLoggedCustomer(
        callable $categoryId,
        callable $productSku,
        string $regularPrice,
        string $specialPrice
    ) {
        $responseBody = $this->getResponseBody($categoryId());
        $this->assertStringContainsString($productSku(), $responseBody);
        $this->assertStringContainsString($regularPrice, $responseBody);
        $this->assertStringNotContainsString($specialPrice, $responseBody);
    }

    /**
     * Customer C is logged in and sees whole default catalog, Product B price is 33$
     *
     * @magentoConfigFixture btob/website_configuration/sharedcatalog_active 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_category_view 1
     * @magentoConfigFixture current_store catalog/magento_catalogpermissions/grant_catalog_product_price 1
     * @dataProvider getWholesaleCustomerData
     */
    public function testCategoryPermissionWholesaleCustomer(
        callable $categoryId,
        callable $productSku,
        string $regularPrice,
        string $specialPrice
    ) {
        $customerId = (int)$this->fixture->get('customer_c')->getId();
        $this->session->loginById($customerId);

        $responseBody = $this->getResponseBody($categoryId());
        $this->assertStringContainsString($productSku(), $responseBody);
        $this->assertStringContainsString($regularPrice, $responseBody);
        if ($specialPrice) {
            $this->assertStringContainsString($specialPrice, $responseBody);
        }
    }

    /**
     * Returns customer_ab data for testing category permission and shared catalog product special price
     *
     * @return array[]
     */
    public function getCustomerABDataDefaultCompanyA(): array
    {
        $fixture = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        return [
            'catalog_a_company_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_1')->getId();
                },
                'productName' => function () use ($fixture) {
                    return $fixture->get('product_a')->getName();
                },
                'unauthorisedAccess' => false,
                'specialPrice' => '90.00'
            ],
            'catalog_b_company_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_2')->getId();
                },
                'productName' => function () {
                    return '';
                },
                'unauthorisedAccess' => true,
                'specialPrice' => ''
            ],
        ];
    }

    /**
     * Returns customer_ab data for testing category permission and shared catalog product special price
     *
     * @return array[]
     */
    public function getCustomerABDataDefaultCompanyB(): array
    {
        $fixture = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        return [
            'catalog_a_company_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_1')->getId();
                },
                'productName' => function () {
                    return '';
                },
                'unauthorisedAccess' => true,
                'specialPrice' => ''
            ],
            'catalog_b_company_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_2')->getId();
                },
                'productName' => function () use ($fixture) {
                    return $fixture->get('product_b')->getName();
                },
                'unauthorisedAccess' => false,
                'specialPrice' => '40.00'
            ],
        ];
    }

    /**
     * Returns customer_ab data for testing category permission and shared catalog product special price
     *
     * @return array[]
     */
    public function getCustomerABDataCompanySwitch(): array
    {
        $fixture = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        return [
            'catalog_a_company_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_1')->getId();
                },
                'productName' => function () {
                    return '';
                },
                'unauthorisedAccess' => true,
                'specialPrice' => ''
            ],
            'catalog_b_company_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_2')->getId();
                },
                'productName' => function () use ($fixture) {
                    return $fixture->get('product_b')->getName();
                },
                'unauthorisedAccess' => false,
                'specialPrice' => '40.00'
            ],
        ];
    }

    /**
     * Returns guest data for testing category permission and shared catalog product special price
     *
     * @return array[]
     */
    public function getNotLoggedCustomerData(): array
    {
        $fixture = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        return [
            'guest_catalog_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_1')->getId();
                },
                'productSku' => function () use ($fixture) {
                    return $fixture->get('product_a')->getName();
                },
                'regularPrice' => '100.00',
                'specialPrice' => '90.00'
            ],
            'guest_catalog_b' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_2')->getId();
                },
                'productSku' => function () use ($fixture) {
                    return $fixture->get('product_b')->getName();
                },
                'regularPrice' => '50.00',
                'specialPrice' => '40.00'
            ]
        ];
    }

    /**
     * Returns wholesale customer data for testing category permission and product tier price
     *
     * @return array[]
     */
    public function getWholesaleCustomerData(): array
    {
        $fixture = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        return [
            'customer_c_catalog_a' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_1')->getId();
                },
                'productSku' => function () use ($fixture) {
                    return $fixture->get('product_a')->getName();
                },
                'regularPrice' => '100.00',
                'specialPrice' => ''
            ],
            'customer_c_catalog_b' => [
                'categoryId' => function () use ($fixture) {
                    return (int) $fixture->get('cat_2')->getId();
                },
                'productSku' => function () use ($fixture) {
                    return $fixture->get('product_b')->getName();
                },
                'regularPrice' => '50.00',
                'specialPrice' => '33.00'
            ]
        ];
    }

    /**
     * Returns response body after url dispatch
     *
     * @param int $categoryId
     * @return string
     */
    private function getResponseBody(int $categoryId): string
    {
        $this->dispatch('catalog/category/view/id/' . $categoryId);
        return $this->getResponse()->getBody();
    }

    /**
     * Switch company for logged in users
     *
     * @param int $companyId
     * @return void
     */
    private function setHttpContext(int $companyId): void
    {
        $baseUrl = 'http://localhost/index.php/';
        $companyProfilePath = 'company/profile/index';
        $companySelectUri = 'company/company/select';

        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setParam('company_id', $companyId);
        $this->getRequest()->setParam('referer_url', $baseUrl . $companyProfilePath);
        $this->dispatch($companySelectUri);

        //Simulate follow redirect
        $this->resetState();
    }

    /**
     * Remove shared instances to avoid re-use of internally cached values between queries
     *
     * @return void
     */
    private function resetState(): void
    {
        $this->_request = null;
        $instances = [
            \Magento\Framework\View\Element\Template\Context::class,
            \Magento\Framework\App\Http::class,
            \Magento\TestFramework\Request::class
        ];
        foreach ($instances as $instance) {
            $this->_objectManager->removeSharedInstance($instance);
        }
    }

    /**
     * Reindex all invalid indexes
     *
     * @return void
     */
    private function reindexAllInvalid(): void
    {
        /** @var \Magento\Indexer\Model\Processor $processor */
        $processor = $this->_objectManager->create(\Magento\Indexer\Model\Processor::class);
        $processor->reindexAllInvalid();
        $processor->updateMview();
    }

    /**
     * Change Default Company
     *
     * @return void
     */
    private function changeDefaultCompany(): void
    {
        $customerId = (int)$this->fixture->get('customer_ab')->getId();
        $idCompany_a = (int)$this->fixture->get('company_a')->getId();
        $idCompany_b = (int)$this->fixture->get('company_b')->getId();

        /** @var \Magento\Company\Model\ResourceModel\Customer $customerCompanyResource */
        $customerCompanyResource = $this->_objectManager->get(
            \Magento\Company\Model\ResourceModel\Customer::class
        );

        $originalCompanyId = $this->defaultCompanyChanged ? $idCompany_b : $idCompany_a;
        $newCompanyId = $this->defaultCompanyChanged ? $idCompany_a : $idCompany_b;

        $customerCompanyResource->getConnection()->update(
            $customerCompanyResource->getMainTable(),
            [CompanyCustomerInterface::IS_DEFAULT => 0],
            [
                'customer_id = ?' => $customerId,
                'company_id = ?' => $originalCompanyId
            ]
        );
        $customerCompanyResource->getConnection()->update(
            $customerCompanyResource->getMainTable(),
            [CompanyCustomerInterface::IS_DEFAULT => 1],
            [
                'customer_id = ?' => $customerId,
                'company_id = ?' => $newCompanyId
            ]
        );

        $this->defaultCompanyChanged = true;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->defaultCompanyChanged) {
            $this->changeDefaultCompany();
        }
        $this->session->logout();
    }
}
