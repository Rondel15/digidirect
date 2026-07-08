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
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuoteBillingAddress;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuoteShippingAddress;
use Magento\NegotiableQuote\Test\Fixture\SendByAdminNegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test quote loading in different company scopes.
 */
class SetNegotiableQuotePaymentMethodTest extends GraphQlAbstract
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
        DataFixture(Customer::class, as: 'customer'),
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
        DataFixture(ProductFixture::class, as: 'product'),
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
            'quote_one'
        ),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$quote_one.id$']),
        DataFixture(
            NegotiableQuoteBillingAddress::class,
            [
                NegotiableQuoteBillingAddress::QUOTE_ID => '$quote_one.id$',
            ]
        ),
        DataFixture(
            NegotiableQuoteShippingAddress::class,
            [
                NegotiableQuoteShippingAddress::QUOTE_ID => '$quote_one.id$',
            ]
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_one.id$', 'company_id' => '$company_one.id$']
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
            'quote_two'
        ),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$quote_two.id$']),
        DataFixture(
            NegotiableQuoteBillingAddress::class,
            [
                NegotiableQuoteBillingAddress::QUOTE_ID => '$quote_two.id$',
            ]
        ),
        DataFixture(
            NegotiableQuoteShippingAddress::class,
            [
                NegotiableQuoteShippingAddress::QUOTE_ID => '$quote_two.id$',
            ]
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_two.id$', 'company_id' => '$company_two.id$']
        ),
    ]
    /**
     * Test quote loading in different company scopes.
     *
     * @dataProvider getMutationData
     * @param string $quoteMask
     * @param string|null $company
     * @param string|null $exception
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testMutation(
        string $quoteMask,
        ?string $company = null,
        ?string $exception = null
    ): void {
        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $headers = ['Authorization' => 'Bearer ' . $customerToken];
        if ($company) {
            $companyId = FixtureManager::getStorage()->get($company)->getId();
            $headers['X-Adobe-Company'] = base64_encode($companyId);
        }
        if ($exception) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception);
        }

        $paymentMethod = 'checkmo';

        $response = $this->graphQlMutation(
            $this->getMutation(
                $quoteMask,
                $paymentMethod
            ),
            [],
            '',
            $headers
        );

        if (!$exception) {
            $this->assertNotEmpty($response['setNegotiableQuotePaymentMethod']);
            $this->assertArrayHasKey('quote', $response['setNegotiableQuotePaymentMethod']);
            $this->assertArrayHasKey('uid', $response['setNegotiableQuotePaymentMethod']['quote']);
            $this->assertEquals($quoteMask, $response['setNegotiableQuotePaymentMethod']['quote']['uid']);
            $this->assertArrayHasKey('status', $response['setNegotiableQuotePaymentMethod']['quote']);
            $this->assertEquals('UPDATED', $response['setNegotiableQuotePaymentMethod']['quote']['status']);
            $this->assertArrayHasKey(
                'available_payment_methods',
                $response['setNegotiableQuotePaymentMethod']['quote']
            );
            $this->assertNotEmpty($response['setNegotiableQuotePaymentMethod']['quote']['available_payment_methods']);
            $this->assertContains(
                ['code' => $paymentMethod],
                $response['setNegotiableQuotePaymentMethod']['quote']['available_payment_methods']
            );
            $this->assertArrayHasKey(
                'selected_payment_method',
                $response['setNegotiableQuotePaymentMethod']['quote']
            );
            $this->assertNotEmpty($response['setNegotiableQuotePaymentMethod']['quote']['selected_payment_method']);
            $this->assertArrayHasKey(
                'code',
                $response['setNegotiableQuotePaymentMethod']['quote']['selected_payment_method']
            );
            $this->assertEquals(
                $paymentMethod,
                $response['setNegotiableQuotePaymentMethod']['quote']['selected_payment_method']['code']
            );
        }
    }

    /**
     * Data provider for testMutation.
     *
     * @return array
     */
    public function getMutationData(): array
    {
        return [
            ['NQ_One_mask', null, null],
            ['NQ_One_mask', 'company_one', null],
            ['NQ_One_mask', 'company_two', 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', null, 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', 'company_one', 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', 'company_two', null],
        ];
    }

    /**
     * Generates GraphQl mutation
     *
     * @param string $quoteId
     * @param string $paymentMethod
     * @return string
     */
    private function getMutation(string $quoteId, string $paymentMethod): string
    {
        return <<<MUTATION
mutation {
  setNegotiableQuotePaymentMethod(
    input: {
      quote_uid: "{$quoteId}"
      payment_method: {code: "{$paymentMethod}"}
    }
  ) {
    quote {
      uid
      status
      available_payment_methods {
        code
      }
      selected_payment_method {
        code
      }
    }
  }
}
MUTATION;
    }
}
