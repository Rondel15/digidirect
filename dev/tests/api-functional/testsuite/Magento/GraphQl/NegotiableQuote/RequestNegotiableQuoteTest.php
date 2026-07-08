<?php
/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\NegotiableQuote;

use Exception;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyInterfaceFactory;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\SharedCatalog\Test\Fixture\Indexer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\NegotiableQuote\Helper\Company as CompanyHelper;

/**
 * Test coverage for create negotiable quote request
 */
class RequestNegotiableQuoteTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->companyRepository = $objectManager->get(CompanyRepositoryInterface::class);
        $this->companyHelper = $objectManager->get(CompanyHelper::class);
    }

    /**
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/cart_with_item_for_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testCreateNegotiableQuote()
    {
        // Execute the mutation
        $query = $this->getQuery('cart_item_customer_mask');
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        // Perform assertions
        $this->assertArrayHasKey('quote', $response['requestNegotiableQuote']);
        $this->assertNotEmpty($response['requestNegotiableQuote']['quote']);
        $this->assertNotEmpty($response['requestNegotiableQuote']['quote']['uid']);
        $this->assertNotEmpty($response['requestNegotiableQuote']['quote']['comments']);
        $this->assertEquals('cart_item_customer_mask', $response['requestNegotiableQuote']['quote']['uid']);
        $this->assertEquals('quote_customer_send', $response['requestNegotiableQuote']['quote']['name']);
        $this->assertNotEmpty($response['requestNegotiableQuote']['quote']['created_at']);
        $this->assertEquals('SUBMITTED', $response['requestNegotiableQuote']['quote']['status']);
        $this->assertCount(1, $response['requestNegotiableQuote']['quote']['items']);
        $negotiableQuoteItem = $response['requestNegotiableQuote']['quote']['items'][0];
        $this->assertEquals('Simple Product', $negotiableQuoteItem['product']['name']);
        $negotiableQuoteComment = $response['requestNegotiableQuote']['quote']['comments'][0];
        $this->assertTrue(array_key_exists('uid', $negotiableQuoteComment));
        unset($negotiableQuoteComment['uid']);
        $expectedComments = [

                'creator_type' => 'BUYER',
                'text' => 'Quote Comment',
                'author' => ['firstname' => 'Customer']
        ];
        $this->assertResponseFields($negotiableQuoteComment, $expectedComments);
        $this->assertEquals(50, $response['requestNegotiableQuote']['quote']['prices']['grand_total']['value']);
        $this->assertEquals('USD', $response['requestNegotiableQuote']['quote']['prices']['grand_total']['currency']);
        $expectedHistoryEntry = [
            [
                'changes' => [
                    'statuses' => ['changes' => [['old_status' => null, 'new_status' =>'SUBMITTED']]],
                    'comment_added'=>['comment'=>'Quote Comment']
                ]
            ]
        ];
        $this->assertResponseFields($response['requestNegotiableQuote']['quote']['history'], $expectedHistoryEntry);
    }

    /**
     * Testing for invalid customer token
     *
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @dataProvider dataProviderInvalidInfo
     * @param string $customerEmail
     * @param string $customerPassword
     * @param string $message
     * @throws AuthenticationException
     */
    public function testRequestNegotiableQuoteWithInvalidCustomerToken(
        string $customerEmail,
        string $customerPassword,
        string $message
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap($customerEmail, $customerPassword));
    }

    /**
     * Testing for guest customer token
     *
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteWithNoCustomerToken(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current user is not a registered customer and cannot perform operations '
            . 'on negotiable quotes.');

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query);
    }

    /**
     * Testing for module enabled
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 0
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteNoModuleEnabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The Negotiable Quote module is not enabled.');

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing for customer belongs to a company
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_no_company.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteCustomerNoCompany(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer does not belong to a company.');

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customernocompany@example.com'));
    }

    /**
     * Testing for feature enabled on company
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteNoCompanyFeatureEnabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Negotiable quotes are not enabled for the current customer\'s company.');

        /** @var CompanyInterfaceFactory $companyFactory */
        $companyFactory = Bootstrap::getObjectManager()->get(CompanyInterfaceFactory::class);
        /** @var CompanyInterface $company */
        $company = $companyFactory->create()->load('email@companyquote.com', 'company_email');
        $company->getExtensionAttributes()->getQuoteConfig()->setIsQuoteEnabled(false);
        /** @var CompanyRepositoryInterface $companyRepository */
        $companyRepository = Bootstrap::getObjectManager()->create(CompanyRepositoryInterface::class);
        $companyRepository->save($company);

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing for manage permissions
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_view_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteNoManagePermissions(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer does not have permission to edit negotiable quotes.');

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing for quote ownership
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/cart_with_item_for_admin.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteUnownedQuote(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find a quote with the specified UID.');

        $query = $this->getQuery('cart_item_admin_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing that no negotiable quote already exists
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/negotiable_quote_by_customer.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteAlreadyExists(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Negotiable quote already exists for the specified UID.");

        $query = $this->getQuery('nq_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(User::class, as: 'user'),
        DataFixture(Customer::class, as: 'company_admin'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            Company::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$company_admin.id$'
            ],
            'company'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
                CompanyCustomerInterface::COMPANY_ID => '$company.id$',
            ],
        ),
        DataFixture(
            Role::class,
            [
                'role_name' => 'Role %uniqid%',
                'company_id' => '$company.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::index',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::all',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::view_quotes',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::edit_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::delete_quotes_sub',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'customer_role'
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer.id$',
                'company_id' => '$company.id$',
                'role_ids' => ['$customer_role.id$'],
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'cartEmptyCustomerMask'),
    ]
    /**
     * Testing that the cart is not empty
     *
     * @throws Exception
     */
    public function testRequestNegotiableQuoteEmptyCart(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot create a negotiable quote for an empty cart.");

        $cartId = DataFixtureStorageManager::getStorage()->get('cartEmptyCustomerMask')->getMaskedId();
        $query = $this->getQuery($cartId);
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap($customer->getEmail()));
    }

    /**
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testRequestNegotiableQuoteForInvalidQuote(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find quotes with the following UIDs: 9999');

        $query = $this->getQuery('9999');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_address_for_customer.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/customer_cart_with_order_placed.php
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @throws Exception
     */
    public function testRequestNegotiableQuoteForInactiveQuote(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot create a negotiable quote for an inactive cart.');

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Testing that a quote for a different store on the same website is accessible
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/cart_with_item_for_customer.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/second_store.php
     * @magentoConfigFixture base_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture base_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testRequestNegotiableQuoteForSecondStore(): void
    {
        $this->storeManager->setCurrentStore('secondstore');
        $headers = $this->getHeaderMap();
        $headers['Store'] = 'secondstore';

        $query = $this->getQuery('cart_item_customer_mask');
        $response = $this->graphQlMutation($query, [], '', $headers);

        $this->assertArrayHasKey('quote', $response['requestNegotiableQuote']);
        $this->assertNotEmpty($response['requestNegotiableQuote']['quote']);
        $this->assertNotEmpty($response['requestNegotiableQuote']['quote']['uid']);
        $this->assertEquals("quote_customer_send", $response['requestNegotiableQuote']['quote']['name']);
    }

    /**
     * Testing that a quote for a different website is inaccessible
     *
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/company_customer_with_manage_permissions.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/product_simple.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/cart_with_item_for_customer.php
     * @magentoApiDataFixture Magento/NegotiableQuote/_files/second_website.php
     * @magentoConfigFixture secondwebsitestore_store customer/account_share/scope 0
     * @magentoConfigFixture secondwebsite_website btob/website_configuration/negotiablequote_active 1
     * @magentoConfigFixture secondwebsite_website btob/website_configuration/company_active 1
     * @throws Exception
     */
    public function testRequestNegotiableQuoteForInvalidWebsite(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not find a quote with the specified UID.');

        $this->storeManager->setCurrentStore('secondwebsitestore');
        $headers = $this->getHeaderMap();
        $headers['Store'] = 'secondwebsitestore';

        $query = $this->getQuery('cart_item_customer_mask');
        $this->graphQlMutation($query, [], '', $headers);
    }

    /**
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
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
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(Indexer::class, as: 'indexer'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testRequestNegotiableQuoteForBlockedComany(): void
    {
        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $headers = $this->getHeaderMap($customer->getEmail());
        $query = $this->getQuery($maskedQuoteId);
        $companyId = DataFixtureStorageManager::getStorage()->get('company')->getId();
        $company = $this->companyRepository->get($companyId);
        $company->setStatus(CompanyInterface::STATUS_BLOCKED);
        $quoteConfig = $this->companyHelper->getQuoteConfig($company);
        $quoteConfig->isObjectNew(false);
        $this->companyRepository->save($company);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This customer company account is blocked and customer cannot place orders.');
        $this->graphQlMutation($query, [], '', $headers);
    }

    /**
     * @return array
     */
    public static function dataProviderInvalidInfo(): array
    {
        return [
            'invalid_customer_email' => [
                'customer$example.com',
                'magento777',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'invalid_customer_password' => [
                'customercompany22@example.com',
                '__--++#$@',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'no_such_email' => [
                'customerNoSuch@example.com',
                'password',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ]
        ];
    }

    /**
     * Generates GraphQl mutation for request Negotiable Quote
     *
     * @param string $cartId
     * @return string
     */
    private function getQuery(string $cartId): string
    {
        return <<<MUTATION
mutation {
  requestNegotiableQuote(
    input: {
      cart_id: "{$cartId}"
      quote_name: "quote_customer_send"
      comment: {
        comment: "Quote Comment"
      }
    }
  )  {
    quote{
      uid
      name
      created_at
      comments{creator_type text author{firstname}}
      status
      comments { uid author { firstname } creator_type text }
      items { uid quantity product { sku name } }
      prices {grand_total {currency value}}
      history{ changes
        {
          statuses { changes { old_status new_status } }
          comment_added { comment }
        }
      }
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
}
