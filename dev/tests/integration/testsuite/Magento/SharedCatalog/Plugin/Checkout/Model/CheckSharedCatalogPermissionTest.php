<?php
/**
 * ADOBE CONFIDENTIAL
 *
 *  Copyright 2024 Adobe
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
 */
declare(strict_types=1);

namespace Magento\SharedCatalog\Plugin\Checkout\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Model\Cart;
use Magento\Company\Model\CompanyContextInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\SharedCatalog\Plugin\Checkout\Model\CheckSharedCatalogPermission;
use Magento\SharedCatalog\Test\Fixture\AssignProducts as AssignProductsSharedCatalog;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

class CheckSharedCatalogPermissionTest extends AbstractController
{
    /**
     * @var CheckSharedCatalogPermission
     */
    private $subject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = $this->_objectManager;
        $this->subject = $objectManager->get(CheckSharedCatalogPermission::class);
    }

    #[
        DbIsolation(false),
        AppArea('frontend'),
        Config('btob/website_configuration/company_active', 1, 'website'),
        Config('btob/website_configuration/sharedcatalog_active', 1, 'website'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            AssignProductsSharedCatalog::class,
            [
                'product_ids' => ['$product.id$'],
                'catalog_id' => '1',
            ]
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote1'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote1.id$', 'product_id' => '$product.id$', 'qty' => 1]),
    ]
    /**
     * @dataProvider customerIsNotCompanyProvider
     */
    public function testBeforeAddProductNotCompany($companyId)
    {
        $context = Bootstrap::getObjectManager()->get(HttpContext::class);
        $context->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, $companyId, $companyId);
        $requestInfo = ['product' => 1];
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $cart = Bootstrap::getObjectManager()->get(Cart::class);
        $result = $this->subject->beforeAddProduct($cart, $product, $requestInfo);
        $this->assertEquals($result[1], $requestInfo);
        $this->assertEquals($result[0], $product);
    }

    public static function customerIsNotCompanyProvider()
    {
        return [
            'company id absent'=> [null],
            'customer is not a company' => [0],
        ];
    }
}
