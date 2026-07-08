<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
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
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\SharedCatalog\Test\Fixture\Indexer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\NegotiableQuote\Helper\Company as CompanyHelper;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\Framework\ObjectManagerInterface;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * Test to check place order permission for company user
 */
class PlaceOrderPermissionTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerTokenServiceInterface|mixed|null
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
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->companyRepository = $this->objectManager->get(CompanyRepositoryInterface::class);
        $this->companyHelper = $this->objectManager->get(CompanyHelper::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
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
        DataFixture(Customer::class, as: 'customer1'),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::index',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Sales::all',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Sales::place_order',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Sales::payment_account',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Sales::view_orders',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Sales::view_orders_sub',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer1.id$'
            ]
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer1.id$',
                'company_id' => '$company.id$',
                'role_ids' => ['$role.id$']
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer1.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    /**
     * Test active company user with sales ACL access can place order
     *
     * @return void
     */
    public function testPlaceOrderOfActiveUserWithSalesAclAccess()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer1');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');
        $mutationResult = $this->placeOrder($maskedQuoteId, $customerToken);
        $this->assertArrayHasKey('placeOrder', $mutationResult);
        $this->assertArrayHasKey('order', $mutationResult['placeOrder']);
        $this->assertArrayHasKey('order_id', $mutationResult['placeOrder']['order']);
        $this->assertNotEmpty($mutationResult['placeOrder']['order']['order_id']);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
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
        DataFixture(Customer::class, as: 'customer2'),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::user_management',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::users_view',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_Company::users_edit',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role2'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer2.id$'
            ]
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer2.id$',
                'company_id' => '$company.id$',
                'role_ids' => ['$role2.id$']
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer2.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    /**
     * Test active company user without sales ACL access cannot place order
     *
     * @return void
     */
    public function testPlaceOrderOfActiveUserWithoutPlaceOrderAclAccess()
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer2');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');
        $this->expectException(\Exception::class);
        // This regexp is added to preserve for backward compatibility with 2.4.6-x versions.
        $this->expectExceptionMessageMatches(
            "/(This customer company account is blocked and customer cannot place orders)|"
            . "(Unable to place order\: A server error stopped your order from being placed\. "
            . "Please try to place your order again)/"
        );
        $this->placeOrder($maskedQuoteId, $customerToken);
    }

    /**
     * @param string $cartId
     * @param $customerToken
     * @return array
     * @throws \Exception
     */
    private function placeOrder(string $cartId, $customerToken): array
    {
        $query = <<<QUERY
mutation {
  placeOrder(
    input: {
      cart_id: "{$cartId}"
    }
  ) {
    order {
      order_id
    }
  }
}
QUERY;
          return $this->graphQlMutation(
              $query,
              [],
              '',
              ['Authorization' => sprintf('Bearer %s', $customerToken)]
          );
    }
}
