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

namespace Magento\SharedCatalog\Controller;

use Magento\AdvancedCheckout\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Company\Test\Fixture\AssignCompany as AssignCompanyFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\SharedCatalog\Test\Fixture\AssignProductsCategory as AssignProductsCategoryFixture;
use Magento\SharedCatalog\Test\Fixture\AssignCategory as AssignCategorySharedCatalogFixture;
use Magento\SharedCatalog\Test\Fixture\AssignCompany as AssignCompanySharedCatalogFixture;
use Magento\SharedCatalog\Test\Fixture\AssignProducts as AssignProductsSharedCatalogFixture;
use Magento\SharedCatalog\Test\Fixture\SetPermission as SetCatalogPermissionFixture;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog as SharedCatalogFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\User\Test\Fixture\User as UserFixture;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Company\Model\CompanyContextInterface;

#[
    AppArea('frontend'),
    AppIsolation(true),
    Config('btob/website_configuration/company_active', 1),
    Config('btob/website_configuration/sharedcatalog_active', 1),
    Config('btob/website_configuration/quickorder_active', 1),
    Config('sales/product_sku/my_account_enable', 1),
    DataFixture(SharedCatalogFixture::class, as: 'sc_', count: 2),
    DataFixture(CustomerFixture::class, as: 'customer_ab'),
    DataFixture(CustomerFixture::class, as: 'admin_a'),
    DataFixture(CustomerFixture::class, as: 'admin_b'),
    DataFixture(UserFixture::class, as: 'sales_rep'),
    DataFixture(
        CompanyFixture::class,
        [
            'sales_representative_id' => '$sales_rep.id$',
            'super_user_id' => '$admin_a.id$'
        ],
        'company_a'
    ),
    DataFixture(
        CompanyFixture::class,
        [
            'sales_representative_id' => '$sales_rep.id$',
            'super_user_id' => '$admin_b.id$'
        ],
        'company_b'
    ),
    DataFixture(
        AssignCompanyFixture::class,
        [
            'company_id' => '$company_a.id$',
            'customer_id' => '$customer_ab.id$'
        ]
    ),
    DataFixture(
        AssignCompanyFixture::class,
        [
            'company_id' => '$company_b.id$',
            'customer_id' => '$customer_ab.id$'
        ]
    ),
    DataFixture(CategoryFixture::class, as: 'cat_', count: 2),
    DataFixture(ProductFixture::class, ['price' => 100, 'sku' => 'product_a'], as: 'product_a'),
    DataFixture(ProductFixture::class, ['price' => 50, 'sku' => 'product_aa'], as: 'product_aa'),
    DataFixture(
        AssignProductsCategoryFixture::class,
        ['products' => ['$product_a$', '$product_aa$'], 'category' => '$cat_1$']
    ),
    DataFixture(
        AssignProductsSharedCatalogFixture::class,
        [
            'product_ids' => [
                '$product_a.id$',
                '$product_aa.id$'
            ],
            'catalog_id' => '$sc_1.id$'
        ]
    ),
    DataFixture(
        AssignCompanySharedCatalogFixture::class,
        [
            'company' => '$company_a$',
            'catalog_id' => '$sc_1.id$'
        ]
    ),
    DataFixture(ProductFixture::class, ['price' => 200, 'sku' => 'product_b'], as: 'product_b'),
    DataFixture(ProductFixture::class, ['price' => 150, 'sku' => 'product_bb'], as: 'product_bb'),
    DataFixture(
        AssignProductsCategoryFixture::class,
        ['products' => ['$product_b$', '$product_bb$'], 'category' => '$cat_2$']
    ),
    DataFixture(
        AssignProductsSharedCatalogFixture::class,
        [
            'product_ids' => [
                '$product_b.id$',
                '$product_bb.id$'
            ],
            'catalog_id' => '$sc_2.id$'
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
class QuickOrderAndOrderBySkuInSharedCatalogTest extends AbstractController
{
    private const CONFIG_QUICK_ORDER = 'btob/website_configuration/quickorder_active';

    private const CONFIG_SHARED_CATALOG = 'btob/website_configuration/sharedcatalog_active';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SessionManagerInterface
     */
    private $companySession;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->session = $this->_objectManager->get(Session::class);
        $this->filesystem = $this->_objectManager->get(Filesystem::class);
        $this->companySession = $this->_objectManager->get(SessionManagerInterface::class);
        $this->httpContext = $this->_objectManager->get(HttpContext::class);
        $scopeConfig = $this->_objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $scopeConfig->setValue(self::CONFIG_QUICK_ORDER, '1', ScopeInterface::SCOPE_WEBSITE);
        $scopeConfig->setValue(self::CONFIG_SHARED_CATALOG, '1', ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @dataProvider oneProductDataProvider
     */
    public function testQuickOrderAndOrderBySkuFromOneSku(
        bool $isDefault,
        bool $isQuickOrder,
        string $company,
        string $sku,
        string $expectedMessage,
        array $expectedItems
    ): void {
        $customer = FixtureManager::getStorage()->get('customer_ab');
        $company = FixtureManager::getStorage()->get($company);
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$company->getId(), null);
        $this->session->loginById($customer->getId());

        if (!$isDefault) {
            $this->companySession->setCompanyId((int) $company->getId());
        }

        $this->dispatchRequestWithData([
            Data::REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG => false,
            'items' => [['sku'=>$sku, 'qty'=>1]]
        ], $isQuickOrder);

        $this->assertSessionMessages(
            $this->containsEqual((string)__($expectedMessage))
        );
        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertEquals($expectedItems, $this->getRequest()->getParam('items'));
    }

    /**
     * @dataProvider csvDataProvider
     */
    public function testQuickOrderAndOrderBySkuFromCsv(
        bool $isDefault,
        bool $isQuickOrder,
        string $companyName,
        string $csvFileName,
        string $expectedMessage,
        array $expectedItems
    ): void {
        $customer = FixtureManager::getStorage()->get('customer_ab');
        $company = FixtureManager::getStorage()->get($companyName);
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$company->getId(), null);

        $_FILES['sku_file'] = $this->prepareFile($csvFileName, 'text/csv');
        $this->session->loginById($customer->getId());

        if (!$isDefault) {
            $this->companySession->setCompanyId((int) $company->getId());
        }

        $this->dispatchRequestWithData([
            Data::REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG => true,
        ], $isQuickOrder);

        $this->assertSessionMessages(
            $this->containsEqual((string)__($expectedMessage))
        );

        $this->assertRedirect($this->stringContains('checkout/cart'));
        $this->assertEquals($expectedItems, $this->getRequest()->getParam('items'));
    }

    /**
     * @dataProvider listOfProductDataProvider
     */
    public function testQuickOrderAndOrderBySkuFromListOfProducts(
        bool $isDefault,
        bool $isQuickOrder,
        string $company,
        array $items,
        string $expectedMessage
    ): void {
        $customer = FixtureManager::getStorage()->get('customer_ab');
        $company = FixtureManager::getStorage()->get($company);
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, (int)$company->getId(), null);
        $this->session->loginById($customer->getId());

        if (!$isDefault) {
            $this->companySession->setCompanyId((int) $company->getId());
        }

        $this->dispatchRequestWithData([
            Data::REQUEST_PARAMETER_SKU_FILE_IMPORTED_FLAG => false,
            'items' => $items
        ], $isQuickOrder);

        $this->assertSessionMessages(
            $this->containsEqual((string)__($expectedMessage))
        );
    }

    /**
     * @return array
     */
    public function listOfProductDataProvider(): array
    {
        return [
            [
                'isDefault' => true,
                'isQuickOrder' => true,
                'company' => 'company_a',
                'items' => [['sku'=>'product_a', 'qty'=>1], ['sku'=>'product_aa', 'qty'=>1]],
                'expectedMessage' => 'You added 2 products to your shopping cart.',
            ],
            [
                'isDefault' => false,
                'isQuickOrder' => true,
                'company' => 'company_b',
                'items' => [['sku'=>'product_b', 'qty'=>1], ['sku'=>'product_bb', 'qty'=>1]],
                'expectedMessage' => 'You added 2 products to your shopping cart.',
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => true,
                'company' => 'company_a',
                'items' => [['sku'=>'product_b', 'qty'=>1], ['sku'=>'product_bb', 'qty'=>1]],
                'expectedMessage' => '2 products require your attention.',
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => false,
                'company' => 'company_a',
                'items' => [['sku'=>'product_a', 'qty'=>1], ['sku'=>'product_aa', 'qty'=>1]],
                'expectedMessage' => 'You added 2 products to your shopping cart.',
            ],
            [
                'isDefault' => false,
                'isQuickOrder' => false,
                'company' => 'company_b',
                'items' => [['sku'=>'product_b', 'qty'=>1], ['sku'=>'product_bb', 'qty'=>1]],
                'expectedMessage' => 'You added 2 products to your shopping cart.',
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => false,
                'company' => 'company_a',
                'items' => [['sku'=>'product_b', 'qty'=>1], ['sku'=>'product_bb', 'qty'=>1]],
                'expectedMessage' => '2 products require your attention.',
            ],
        ];
    }

    /**
     * @return array
     */
    public function oneProductDataProvider(): array
    {
        return [
            [
                'isDefault' => true,
                'isQuickOrder' => true,
                'company' => 'company_a',
                'product' => 'product_a',
                'expectedMessage' => 'You added 1 product to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_a']
                ]
            ],
            [
                'isDefault' => false,
                'isQuickOrder' => true,
                'company' => 'company_b',
                'product' => 'product_b',
                'expectedMessage' => 'You added 1 product to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => true,
                'company' => 'company_a',
                'product' => 'product_b',
                'expectedMessage' => '1 product requires your attention.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => false,
                'company' => 'company_a',
                'product' => 'product_a',
                'expectedMessage' => 'You added 1 product to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_a']
                ]
            ],
            [
                'isDefault' => false,
                'isQuickOrder' => false,
                'company' => 'company_b',
                'product' => 'product_b',
                'expectedMessage' => 'You added 1 product to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => false,
                'company' => 'company_a',
                'product' => 'product_b',
                'expectedMessage' => '1 product requires your attention.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b']
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function csvDataProvider(): array
    {
        return [
            [
                'isDefault' => false,
                'isQuickOrder' => true,
                'company' => 'company_b',
                'csvFileName' => 'company_b_sku.csv',
                'expectedMessage' => 'You added 2 products to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b'],
                    ['qty' => '1', 'sku' => 'product_bb']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => true,
                'company' => 'company_a',
                'csvFileName' => 'company_a_sku.csv',
                'expectedMessage' => 'You added 2 products to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_a'],
                    ['qty' => '1', 'sku' => 'product_aa']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => true,
                'company' => 'company_a',
                'csvFileName' => 'company_b_sku.csv',
                'expectedMessage' => '2 products require your attention.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b'],
                    ['qty' => '1', 'sku' => 'product_bb']
                ]
            ],
            [
                'isDefault' => false,
                'isQuickOrder' => false,
                'company' => 'company_b',
                'csvFileName' => 'company_b_sku.csv',
                'expectedMessage' => 'You added 2 products to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b'],
                    ['qty' => '1', 'sku' => 'product_bb']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => false,
                'company' => 'company_a',
                'csvFileName' => 'company_a_sku.csv',
                'expectedMessage' => 'You added 2 products to your shopping cart.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_a'],
                    ['qty' => '1', 'sku' => 'product_aa']
                ]
            ],
            [
                'isDefault' => true,
                'isQuickOrder' => false,
                'company' => 'company_a',
                'csvFileName' => 'company_b_sku.csv',
                'expectedMessage' => '2 products require your attention.',
                'expectedItems' => [
                    ['qty' => '1', 'sku' => 'product_b'],
                    ['qty' => '1', 'sku' => 'product_bb']
                ]
            ]
        ];
    }

    /**
     * Prepare file
     *
     * @param string $fileName
     * @param string $type
     * @return array
     */
    private function prepareFile(string $fileName, string $type): array
    {
        $tmpDirectory = $this->filesystem
            ->getDirectoryWrite(DirectoryList::SYS_TMP);
        $fixtureDir = realpath(__DIR__ . '/../_files');
        $filePath = $tmpDirectory->getAbsolutePath($fileName);
        copy($fixtureDir . DIRECTORY_SEPARATOR . $fileName, $filePath);

        return [
            'name' => $fileName,
            'type' => $type,
            'tmp_name' => $filePath,
            'error' => 0,
            'size' => filesize($filePath),
        ];
    }

    /**
     * Dispatch post request with post data
     *
     * @param array $post
     * @param bool $isQuickOrder
     * @return void
     */
    private function dispatchRequestWithData(array $post, bool $isQuickOrder): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($post);
        if ($isQuickOrder) {
            $this->dispatch('quickorder/sku/uploadFile/');
        } else {
            $this->dispatch('customer_order/sku/uploadFile/');
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->session->logout();
        $this->companySession->unsValue('company_id');
        parent::tearDown();
    }
}
