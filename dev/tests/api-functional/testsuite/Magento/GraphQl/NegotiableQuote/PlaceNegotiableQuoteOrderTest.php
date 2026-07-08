<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\NegotiableQuote;

use Exception;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SharedCatalog\Test\Fixture\Indexer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\RequestReviewByCustomerNegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\SendByAdminNegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\Company\Test\Fixture\Company;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\NegotiableQuote\Helper\Company as CompanyHelper;

/**
 * Test coverage to place negotiable quote order.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceNegotiableQuoteOrderTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyHelper
     */
    private $companyHelper;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->companyRepository = $objectManager->get(
            CompanyRepositoryInterface::class
        );
        $this->companyHelper = $objectManager->get(CompanyHelper::class);
    }

    /**
     * Test that a negotiable quote with all required info and status can be placed and verify its status
     * and the one of the created order is correct.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testSuccessfulOrder(): void
    {
        $query = $this->getMutation('nq_customer_mask');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        // Assert the quote data is present and correct
        $this->assertNotEmpty($response['placeNegotiableQuoteOrder']);
        $this->assertArrayHasKey('order', $response['placeNegotiableQuoteOrder']);
        $this->assertArrayHasKey('order_number', $response['placeNegotiableQuoteOrder']['order']);
        $this->assertNotEmpty($response['placeNegotiableQuoteOrder']['order']['order_number']);

        $orderNumber = $response['placeNegotiableQuoteOrder']['order']['order_number'];

        $query = $this->getNegotiableQuoteStatusQuery('nq_customer_mask');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertNotEmpty($response['negotiableQuote']);
        $this->assertArrayHasKey('status', $response['negotiableQuote']);
        $this->assertEquals('ORDERED', $response['negotiableQuote']['status']);

        $query = $this->getCustomerOrdersStatusQuery($orderNumber);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertNotEmpty($response['customer']);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $order = $response['customer']['orders']['items'][0];
        $this->assertNotEmpty($order);
        $this->assertArrayHasKey('status', $order);
        $this->assertEquals('Pending', $order['status']);
    }

    /**
     * Test that a negotiable quote with 0 subtotal and free payment method is working.
     *
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_shipping_methods.php
     * @magentoConfigFixture default_store payment/free/active 1
     * @magentoConfigFixture default/payment/free/active 1
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order_free.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testSuccessfulFreeOrder(): void
    {
        $this->testSuccessfulOrder();
    }

    /**
     * Test that a negotiable quote order can be place even if the company has PO enabled
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/purchaseorder_enabled 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testSuccessfulCompanyPO(): void
    {
        $this->testSuccessfulOrder();
    }

    /**
     * Test that a negotiable quote order fails if the customer has checkout permission.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_no_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailNoCheckoutPermission(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer does not have permission to checkout negotiable quotes.');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order fails if its status is not allowing to place an order.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order_wrong_status.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailInvalidStatus(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The quote nq_customer_mask is currently locked, '
            . 'and you cannot place an order from it at the moment.');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order fails if its already placed.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailAlreadyPlaced(): void
    {
        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The quote nq_customer_mask is currently locked, '
            . 'and you cannot place an order from it at the moment.');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order fails if its already placed.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     */
    public function testFailAlreadyPlacedCartPlaceOrder(): void
    {
        $this->checkPlaceOrderFails();
    }

    /**
     * Test that the negotiable quote order can't be placed if an item is out of stock.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order_out_of_stock.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailOutOfStock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to place order: Some of the products are out of stock');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that negotiable quote order fails if uid is not present.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_cart_for_checkout.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailPlaceOrderEmtpyUid():void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Required parameter "quote_uid" is missing');

        $query = $this->getMutation('');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that regular quote can't be placed using Negotiable Quote place order mutation.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_cart_for_checkout.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailPlaceOrderRegularQuote():void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find quotes with the following UIDs: customer_quote_mask');

        $query = $this->getMutation('customer_quote_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order can't be placed using the cart place order mutation.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_for_place_order.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     */
    public function testFailRegularPlaceOrder(): void
    {
        $this->checkPlaceOrderFails();
    }

    /**
     * Test that a negotiable quote order fails if the quote has no shipping address.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_no_shipping.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailWhenNoShipping(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to place order: '
            . 'Some addresses can\'t be used due to the configurations for specific countries.');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order fails if the quote has no shipping method.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_no_shipping_method.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailWhenNoShippingMethod(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to place order: The shipping method is missing. '
            . 'Select the shipping method and try again.');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order fails if the quote has no billing address.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_no_billing.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailWhenNoBilling(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Please check the billing address information./');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote order fails if the quote has no payment method.
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_no_po_customer_with_checkout_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer_no_payment.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testFailWhenNoPaymentMethod(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to place order: Enter a valid payment method and try again.');

        $query = $this->getMutation('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Test that a negotiable quote place order fails if the company was blocked
     *
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws InputException
     */
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
        DataFixture(Product::class, [], 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
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
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'quote'
        ),
        DataFixture(RequestReviewByCustomerNegotiableQuote::class, ['cart_id' => '$quote.id$']),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testNegotiableQuotePlaceOrderOfActiveUserWithBlockedCompany()
    {
        $companyId = DataFixtureStorageManager::getStorage()->get('company')->getId();
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $headers = $this->getHeaderMap($customer->getEmail());
        $company = $this->companyRepository->get($companyId);
        $company->setStatus(CompanyInterface::STATUS_BLOCKED);
        $quoteConfig = $this->companyHelper->getQuoteConfig($company);
        $quoteConfig->isObjectNew(false);
        $this->companyRepository->save($company);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This customer company account is blocked and customer cannot place orders.');
        $query = $this->getMutation($maskedQuoteId);
        $this->graphQlMutation($query, [], '', $headers);
    }

    /**
     * Generates the GraphQl query a negotiable quote status.
     *
     * @param string $quoteId
     * @return string
     */
    private function getNegotiableQuoteStatusQuery(string $quoteId): string
    {
        return <<<QUERY
{
  negotiableQuote(
    uid: "{$quoteId}"
  ) {
    status
  }
}
QUERY;
    }

    /**
     * Generates the GraphQl query to fetch an order status.
     *
     * @param string $quoteId
     * @return string
     */
    private function getCustomerOrdersStatusQuery(string $orderNumber): string
    {
        return <<<QUERY
{
  customer{
    orders(filter: {number: {match: "{$orderNumber}"}}) {
      items {
        status
      }
    }
  }
}
QUERY;
    }

    /**
     * Generates the GraphQl mutation to place negotiable quote order.
     *
     * @param string $quoteId
     * @return string
     */
    private function getMutation(string $quoteId): string
    {
        return <<<MUTATION
mutation {
  placeNegotiableQuoteOrder(
    input: {
      quote_uid: "{$quoteId}"
    }
  ) {
    order {
      order_id
      order_number
    }
  }
}
MUTATION;
    }

    /**
     * Generates the GraphQl mutation to place cart order.
     *
     * @param string $quoteId
     * @return string
     */
    private function getCartMutation(string $quoteId): string
    {
        return <<<MUTATION
mutation {
  placeOrder(
    input: {
      cart_id: "{$quoteId}"
    }
  ) {
    order {
      order_id
      order_number
    }
    errors {
      message
      code
    }
  }
}
MUTATION;
    }

    /**
     * Generates the GraphQl mutation to place cart order.
     *
     * @param string $quoteId
     * @return string
     */
    private function getCartMutationV1(string $quoteId): string
    {
        return <<<MUTATION
mutation {
  placeOrder(
    input: {
      cart_id: "{$quoteId}"
    }
  ) {
    order {
      order_id
      order_number
    }
  }
}
MUTATION;
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

    /**
     * @return void
     * @throws AuthenticationException
     */
    private function checkPlaceOrderFails(): void
    {
        $query = $this->getCartMutation('nq_customer_mask');
        // This try/catch block is added to preserve for backward compatibility with 2.4.6-x versions.
        try {
            $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        } catch (\Exception $exception) {
            if ($exception->getMessage() === "GraphQL response contains errors: Cannot query field \"errors\" "
                . "on type \"PlaceOrderOutput\".\n") {
                $this->expectException(Exception::class);
                $this->expectExceptionMessage('The cart isn\'t active.');
                $query = $this->getCartMutationV1('nq_customer_mask');
                $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
            }
        }

        if (isset($response['placeOrder']['errors'])) {
            $this->assertEquals(1, count($response['placeOrder']['errors']));
            $this->assertEquals('CART_NOT_ACTIVE', $response['placeOrder']['errors'][0]['code']);
            $this->assertEquals(
                'The cart isn\'t active.',
                $response['placeOrder']['errors'][0]['message']
            );
        } elseif (isset($exception)) {
            $this->assertEquals('The cart isn\'t active.', $exception->getMessage());
        } else {
            $this->throwException(new \PHPUnit\Framework\Exception("This was not expected."));
        }
    }
}
