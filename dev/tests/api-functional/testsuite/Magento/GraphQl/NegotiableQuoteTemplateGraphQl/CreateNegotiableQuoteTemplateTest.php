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

namespace Magento\GraphQl\NegotiableQuoteTemplateGraphQl;

use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Api\Data\CartInterface;

class CreateNegotiableQuoteTemplateTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface|mixed|null
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer.id$'
            ],
            'company'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            ['company_id' => '$company.entity_id$', 'company_quote_enabled' => 1]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #11',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 5
                        ],
                    ],
                ],
            ],
            'negotiable_quote'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testCreateNegotiableQuoteTemplate()
    {
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $quoteItems = $quote->getAllItems();

        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');
        $response = $this->graphQlMutation(
            $this->getMutation($maskedQuoteId),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );

        $this->assertArrayHasKey('template_id', $response['requestNegotiableQuoteTemplateFromQuote']);
        $this->assertArrayHasKey('name', $response['requestNegotiableQuoteTemplateFromQuote']);
        $this->assertArrayHasKey('items', $response['requestNegotiableQuoteTemplateFromQuote']);
        $expectedItems[] = [
            'product' => ['sku' => $quoteItems[0]->getData(CartItemInterface::KEY_SKU)],
            'quantity' => $quoteItems[0]->getData(CartItemInterface::KEY_QTY) + 5
        ];
        $this->assertEquals($expectedItems, $response['requestNegotiableQuoteTemplateFromQuote']['items']);
    }

    /**
     * Returns GraphQl Query string to create a quote template from quote
     *
     * @param string $quoteId
     * @return string
     */
    private function getMutation(string $quoteId): string
    {
        return <<<MUTATION
mutation
  {
    requestNegotiableQuoteTemplateFromQuote(
    input: {
    cart_id: "$quoteId",
  }

)
   {
        template_id
          name
          expiration_date
          is_min_max_qty_used
          min_order_commitment
          max_order_commitment
          status
          items {
              product {
                sku
              }
              quantity
          }
            comments {
              text
              creator_type
              author {
                firstname
                lastname
              }
            }
            history {
              uid
              author {
                firstname
                lastname
              }
              created_at

         }

      }
  }
MUTATION;
    }
}
