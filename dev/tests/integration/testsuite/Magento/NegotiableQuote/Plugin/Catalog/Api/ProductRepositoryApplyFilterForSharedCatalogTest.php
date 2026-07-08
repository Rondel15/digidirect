<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NegotiableQuote\Plugin\Catalog\Api;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\SharedCatalog\Test\Fixture\AssignProducts as AssignProductsSharedCatalog;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\SharedCatalog\Api\ProductManagementInterface;
use Magento\SharedCatalog\Api\SharedCatalogManagementInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductRepositoryApplyFilterForSharedCatalogTest.
 *
 * Test case to check when product has been removed from shared catalog, frontend cart updated correctly
 */
class ProductRepositoryApplyFilterForSharedCatalogTest extends TestCase
{
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
    public function testApplyFilterForSharedCatalog()
    {
        /** @var SharedCatalogManagementInterface $sharedCatalogManagement */
        $sharedCatalogManagement = Bootstrap::getObjectManager()->get(SharedCatalogManagementInterface::class);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $publicCatalog = $sharedCatalogManagement->getPublicCatalog();
        $items = $this->loadItems($publicCatalog->getCustomerGroupId());

        $this->assertCount(1, $items);
        $this->assertContains($product->getSku(), array_map(function ($product) {
            return $product->getSku();
        }, $items, []));
        /** @var Customer $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $quote = $quoteRepository->getActiveForCustomer($customer->getId());
        $this->assertCount(1, $quote->getItems());

        /** @var ProductManagementInterface $productManagement */
        $productManagement = Bootstrap::getObjectManager()->get(ProductManagementInterface::class);
        $productManagement->unassignProducts($publicCatalog->getId(), [$product]);
        $productsAfterUnassign = $productManagement->getProducts($publicCatalog->getId());

        $this->assertCount(0, $productsAfterUnassign);

        $quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        if (method_exists($quoteRepository, '_resetState')) {
            $quoteRepository->_resetState();
            $quote = $quoteRepository->getActiveForCustomer($customer->getId());
            $this->assertCount(0, $quote->getItems());
        }
    }

    /**
     * @param int $customerGroupId
     * @return Product[]
     */
    private function loadItems(int $customerGroupId): array
    {
        Bootstrap::getObjectManager()->get(HttpContext::class)
            ->setValue(CustomerContext::CONTEXT_GROUP, $customerGroupId, null);

        /** @var ProductCollection $productCollection */
        $productCollection = Bootstrap::getObjectManager()->create(ProductCollection::class);
        $productCollection->addPriceData($customerGroupId);
        $productCollection->load();

        return $productCollection->getItems();
    }
}
