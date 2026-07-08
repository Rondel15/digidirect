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

namespace Magento\GraphQl\Company\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\AssignCustomer;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\PageCache\Model\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\User\Test\Fixture\User;

/**
 * Test to verify the company id is added to the cache key / cache id
 */
class CompanyProductCacheTest extends GraphQLPageCacheAbstract
{
    private const GET_PRODUCT_SKU = <<<QUERY
{
    products(filter: {sku: {eq: "%s"}}) {
        items {
            sku
        }
    }
}
QUERY;

    #[
        DataFixture(Customer::class, as: 'customer1'),
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(Customer::class, as: 'customer3'),
        DataFixture(User::class, as: 'admin'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer1.id$',
                CompanyInterface::NAME => 'Company 1'
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$admin.id$',
                CompanyInterface::SUPER_USER_ID => '$customer2.id$',
                CompanyInterface::NAME => 'Company 2'
            ],
            'company2'
        ),
        DataFixture(
            AssignCustomer::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company1.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer3.id$',
                CompanyCustomerInterface::IS_DEFAULT => 0
            ]
        ),
        DataFixture(Product::class, as: 'product'),
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        ConfigFixture('btob/website_configuration/company_active', 1)
    ]
    public function testQuery(): void
    {
        /** @var CompanyInterface $company1 */
        $company1 = DataFixtureStorageManager::getStorage()->get('company1');
        /** @var CompanyInterface $company2 */
        $company2 = DataFixtureStorageManager::getStorage()->get('company2');
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer3');
        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $token = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($customer->getEmail(), 'password');

        $company1CacheId = $this->executeMissHitQueriesAndReturnCacheId($product, $company1, $token);
        $company2CacheId = $this->executeMissHitQueriesAndReturnCacheId($product, $company2, $token);

        $this->assertNotEquals($company1CacheId, $company2CacheId);
    }

    /**
     * Assert first product query is hit, second is miss. Return cache id.
     *
     * @param ProductInterface $product
     * @param CompanyInterface $company
     * @param string $token
     * @return string
     */
    private function executeMissHitQueriesAndReturnCacheId(
        ProductInterface $product,
        CompanyInterface $company,
        string $token
    ): string {
        $query = sprintf(static::GET_PRODUCT_SKU, $product->getSku());
        $expectedResponse = ['products' => ['items' => [['sku' => $product->getSku()]]]];
        $companyUid = Bootstrap::getObjectManager()->get(Uid::class)->encode((string) $company->getId());

        $response = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Adobe-Company' => $companyUid
            ]
        );
        $this->assertEquals($expectedResponse, $response['body']);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        $response = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Adobe-Company' => $companyUid,
                CacheIdCalculator::CACHE_ID_HEADER => $cacheId
            ]
        );
        $this->assertEquals($expectedResponse, $response['body']);

        $response = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Adobe-Company' => $companyUid,
                CacheIdCalculator::CACHE_ID_HEADER => $cacheId
            ]
        );
        $this->assertEquals($expectedResponse, $response['body']);

        return $cacheId;
    }
}
