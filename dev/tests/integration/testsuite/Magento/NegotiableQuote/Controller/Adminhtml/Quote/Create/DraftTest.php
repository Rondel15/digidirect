<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote\Create;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\NegotiableQuote\Controller\Adminhtml\AbstractTest;
use Magento\NegotiableQuote\Model\Company\DetailsProviderFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Test\Fixture\User;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;

/**
 * @magentoAppArea adminhtml
 */
class DraftTest extends AbstractTest
{
    /**
     * @var string
     */
    protected $resource = Draft::ADMIN_RESOURCE;

    /**
     * @var string
     */
    protected $uri = 'backend/quotes/quote_create/draft';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    #[
        Config(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'website',
            scopeValue: 'base_website'
        ),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(Customer::class, as: 'customer_b'),
        DataFixture(Customer::class, as: 'customer_abc'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_abc.id$'
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        )
    ]
    public function testExecuteSuccess()
    {
        $objectManager = Bootstrap::getObjectManager();

        $customer = FixtureManager::getStorage()->get('customer_abc');
        $storeManager = $objectManager->get(StoreManagerInterface::class);

        $this->getRequest()->setMethod($this->httpMethod);
        $this->getRequest()->setPostValue([
            'customer_id' => $customer->getId(),
            'store_id' => $storeManager->getDefaultStoreView()->getId()
        ]);
        $this->dispatch($this->uri);
        $responseContent = \json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $responseContent);
        $this->assertArrayHasKey('quote_id', $responseContent);
        /** @var \Magento\Company\Model\Company $company */
        $company = FixtureManager::getStorage()->get('company_c');
        $this->assertQuoteCompany((int)$responseContent['quote_id'], $company->getCompanyEmail());
        $this->assertIsInt($responseContent['quote_id']);
    }

    #[
        Config(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'website',
            scopeValue: 'base_website'
        ),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(Customer::class, as: 'customer_b'),
        DataFixture(Customer::class, as: 'customer_abc'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_abc.id$'
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_abc.id$'], 'cart_abc'),
        DataFixture(Product::class, ['sku' => '100000001', 'price' => 10], 'product_simple'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart_abc.id$', 'product_id' => '$product_simple.id$']),
    ]
    public function testExecuteSuccessNonEmptyCustomerCart()
    {
        $objectManager = Bootstrap::getObjectManager();

        $customer = FixtureManager::getStorage()->get('customer_abc');
        $storeManager = $objectManager->get(StoreManagerInterface::class);

        $cartManagement = $objectManager->get(CartManagementInterface::class);
        $existingQuote = $cartManagement->getCartForCustomer($customer->getId());
        $this->assertEquals(1, $existingQuote->getItemsCount());

        $this->getRequest()->setMethod($this->httpMethod);
        $this->getRequest()->setPostValue([
            'customer_id' => $customer->getId(),
            'store_id' => $storeManager->getDefaultStoreView()->getId()
        ]);
        $this->dispatch($this->uri);
        $responseContent = \json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $responseContent);
        $this->assertArrayHasKey('quote_id', $responseContent);
        /** @var \Magento\Company\Model\Company $company */
        $company = FixtureManager::getStorage()->get('company_c');
        $this->assertQuoteCompany((int)$responseContent['quote_id'], $company->getCompanyEmail());
        $this->assertGreaterThan($existingQuote->getId(), $responseContent['quote_id']);

        $cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $quote = $cartRepository->get($responseContent['quote_id']);
        $this->assertEquals($customer->getId(), $quote->getCustomer()->getId());
        $this->assertEquals(0, $quote->getItemsCount());
        $customerCurrentQuote = $cartManagement->getCartForCustomer($customer->getId());
        $this->assertEquals(1, $customerCurrentQuote->getItemsCount());
        $this->assertEquals($existingQuote->getId(), $customerCurrentQuote->getId());
    }

    /**
     * @dataProvider executeInvalidCustomerDataProvider
     * @param int $customerId
     * @param string $message
     */
    #[
        Config(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'website',
            scopeValue: 'base_website'
        )
    ]
    public function testExecuteInvalidCustomer($customerId, $message)
    {
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->getRequest()->setMethod($this->httpMethod);
        $this->getRequest()->setPostValue([
            'customer_id' => $customerId,
            'store_id' => $storeManager->getDefaultStoreView()->getId()
        ]);
        $this->dispatch($this->uri);
        $responseContent = \json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertStringContainsString($message, $responseContent['message']);
    }

    public static function executeInvalidCustomerDataProvider()
    {
        return [
            [
                'customerId' => 100500,
                'message' => 'No such entity with customerId = 100500'
            ],
            [
                'customerId' => null,
                'message' => 'Invalid Customer or Store ID.'
            ],
            [
                'customerId' => '',
                'message' => 'Invalid Customer or Store ID.'
            ],
        ];
    }

    /**
     * @dataProvider executeInvalidStoreDataProvider
     * @param int $storeId
     * @param string $message
     */
    #[
        Config(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'website',
            scopeValue: 'base_website'
        ),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(Customer::class, as: 'customer_b'),
        DataFixture(Customer::class, as: 'customer_abc'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_abc.id$'
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_abc.id$',
            ]
        )
    ]
    public function testExecuteInvalidStore($storeId, $message)
    {
        $customer = FixtureManager::getStorage()->get('customer_abc');
        $this->getRequest()->setMethod($this->httpMethod);
        $this->getRequest()->setPostValue([
            'customer_id' => $customer->getId(),
            'store_id' => $storeId
        ]);
        $this->dispatch($this->uri);
        $responseContent = \json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertStringContainsString(
            $message,
            $responseContent['message']
        );
    }

    public static function executeInvalidStoreDataProvider()
    {
        return [
            [
                'storeId' => 100500,
                'message' => 'The store that was requested wasn\'t found. Verify the store and try again.'
            ],
            [
                'storeId' => null,
                'message' => 'Invalid Customer or Store ID.'
            ],
            [
                'storeId' => '',
                'message' => 'Invalid Customer or Store ID.'
            ],
        ];
    }
}
