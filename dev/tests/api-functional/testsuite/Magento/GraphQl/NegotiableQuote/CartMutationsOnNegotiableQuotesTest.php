<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\NegotiableQuote;

use Exception;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\SharedCatalog\Test\Fixture\Indexer;
use Magento\User\Test\Fixture\User;

/**
 * Test coverage verifying that negotiable quotes cannot be mutated through non-NQ endpoints
 */
class CartMutationsOnNegotiableQuotesTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test that attempting to use the addSimpleProductToCart mutation on a negotiable quote fails.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     */
    public function testAddSimpleProductToCartForNegotiableQuote(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The cart isn\'t active.');
        $mutation = <<<MUTATION
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "nq_customer_mask",
    cart_items: [
      {
        data: {
          quantity: 1
          sku: "simple"
        }
      }
    ]
  }) {
    cart {
      items {
        id
      }
    }
  }
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $this->getHeaderMap());
    }

    /**
     * Test that attempting to set the billing address on a negotiable quote through setBillingAddressOnCart fails.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_checkout.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testSetBillingAddressOnCartMutationForNegotiableQuote(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The cart isn\'t active.');

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "nq_customer_mask"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AZ"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
         }
      }
    }
  ) {
    cart {
      billing_address {
        __typename
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(Customer::class, ['email' => 'customercompany22@example.com'], as: 'customer'),
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
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            [
                'company_id' => '$company.entity_id$',
                'company_quote_enabled' => 1
            ]
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testPlaceOrderMutationForNegotiableQuote()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $requestMutation = <<<REQUEST
mutation {
  requestNegotiableQuote(
    input: {
      cart_id: "$maskedQuoteId"
      quote_name: "quote_customer_send"
      comment: {
        comment: "Quote Comment"
      }
    }
  ) {
    quote{
      uid
      status
    }
  }
}
REQUEST;

        $requestResponse = $this->graphQlMutation($requestMutation, [], '', $this->getHeaderMap());
        $this->assertEquals($maskedQuoteId, $requestResponse['requestNegotiableQuote']['quote']['uid']);
        $this->assertEquals('SUBMITTED', $requestResponse['requestNegotiableQuote']['quote']['status']);

        $placeOrderMutation = <<<PLACEORDER
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_number
    }
    errors {
      message
      code
    }
  }
}
PLACEORDER;

        $placeOrderMutationV1 = <<<PLACEORDERV
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    order {
      order_number
    }
  }
}
PLACEORDERV;

        // This try/catch block is added to preserve for backward compatibility with 2.4.6-x versions.
        try {
            $response = $this->graphQlMutation($placeOrderMutation, [], '', $this->getHeaderMap());
        } catch (\Exception $exception) {
            if ($exception->getMessage() === "GraphQL response contains errors: Cannot query field \"errors\" "
                . "on type \"PlaceOrderOutput\".\n") {
                $this->expectException(Exception::class);
                $this->expectExceptionMessage('The cart isn\'t active.');
                $response = $this->graphQlMutation($placeOrderMutationV1, [], '', $this->getHeaderMap());
            }
        }

        if (isset($response['placeOrder']['errors'])) {
            $this->assertEquals(1, count($response['placeOrder']['errors']));
            $this->assertEquals(
                'The cart isn\'t active.',
                $response['placeOrder']['errors'][0]['message']
            );
            $this->assertEquals('CART_NOT_ACTIVE', $response['placeOrder']['errors'][0]['code']);
        } elseif (isset($exception)) {
            $this->assertEquals('The cart isn\'t active.', $exception->getMessage());
        } else {
            $this->throwException(new \PHPUnit\Framework\Exception('This was not expected.'));
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(
        string $username = 'customercompany22@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
