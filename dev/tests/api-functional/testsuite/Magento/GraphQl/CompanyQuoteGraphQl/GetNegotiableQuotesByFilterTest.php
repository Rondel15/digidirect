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

namespace Magento\GraphQl\CompanyQuoteGraphQl;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test getting quote list in different company scopes.
 */
class GetNegotiableQuotesByFilterTest extends GraphQlAbstract
{
    /**
     * @var ?CustomerTokenServiceInterface
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        AppArea('graphql'),
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', true),
        DataFixture(Customer::class, as: 'admin_one'),
        DataFixture(Customer::class, as: 'admin_two'),
        DataFixture(Customer::class, as: 'admin_three'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(Customer::class, as: 'customer_two'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin_one.id$'
            ],
            'company_one'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin_two.id$'
            ],
            'company_two'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin_three.id$'
            ],
            'company_three'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_one.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_two.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_three.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer_two.id$',
            ]
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote_one'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_one.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'item_one'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_One_',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'negotiable_quote_one'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_one.id$', 'company_id' => '$company_one.id$']
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote_two'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_two.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'item_two'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_Two_',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'negotiable_quote_two'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_two.id$', 'company_id' => '$company_two.id$']
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_two.id$'], 'quote_three'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_three.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'item_three'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_Three_',
                'quote' => [
                    'customer_id' => '$customer_two.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'negotiable_quote_three'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_three.id$', 'company_id' => '$company_three.id$']
        ),
    ]
    /**
     * Test quote list loading in different company scopes.
     *
     * @dataProvider getQueryData
     * @param array $expectedQuoteMasks
     * @param string|null $company
     * @param string|null $exceptionTemplate
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testMutation(
        array $expectedQuoteMasks = [],
        ?string $company = null,
        ?string $exceptionTemplate = null
    ): void {
        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $headers = ['Authorization' => 'Bearer ' . $customerToken];
        if ($company) {
            $companyId = FixtureManager::getStorage()->get($company)->getId();
            $headers['X-Adobe-Company'] = base64_encode($companyId);
        }
        if ($exceptionTemplate) {
            $this->expectException(\Exception::class);
            $exceptionTemplate = str_replace('{company_uid}', $headers['X-Adobe-Company'], $exceptionTemplate);
            $this->expectExceptionMessage($exceptionTemplate);
        }

        $response = $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            $headers
        );

        if (!$exceptionTemplate) {
            $this->assertNotEmpty($response['negotiableQuotes']);
            $this->assertArrayHasKey('items', $response['negotiableQuotes']);
            foreach ($expectedQuoteMasks as $key => $expectedQuoteMask) {
                $this->assertEquals($expectedQuoteMask, $response['negotiableQuotes']['items'][$key]['uid']);
            }
            $this->assertEquals(count($expectedQuoteMasks), $response['negotiableQuotes']['total_count']);
        }
    }

    /**
     * Data provider for test.
     *
     * @return array
     */
    public function getQueryData(): array
    {
        return [
            [['NQ_One_mask'], null, null],
            [['NQ_One_mask'], 'company_one', null],
            [['NQ_Two_mask'], 'company_two', null],
            [[], 'company_three', 'Company with ID "{company_uid}" is not available.'],
        ];
    }

    /**
     * GraphQl Query under test.
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
{
  negotiableQuotes
	{
    items {
      uid
    }
    total_count
  }
}
QUERY;
    }
}
