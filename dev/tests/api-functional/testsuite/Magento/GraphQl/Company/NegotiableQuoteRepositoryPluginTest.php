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

namespace Magento\GraphQl\Company;

use Magento\Catalog\Test\Fixture\Product;
use Magento\CompanyQuote\Model\CompanyQuoteLinkFactory;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyQuote\Model\ResourceModel\CompanyQuoteLink;
use Magento\CompanyQuote\Plugin\Quote\Api\CartRepositoryInterfacePlugin;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test coverage for quote extension company id after negotiable quote request
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NegotiableQuoteRepositoryPluginTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CompanyQuoteLinkFactory
     */
    private $companyQuoteLinkFactory;

    /**
     * @var CompanyQuoteLink
     */
    private $companyQuoteLinkResourceModel;

    /**
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @var CartRepositoryInterfacePlugin
     */
    private $cartRepositoryPlugin;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->companyQuoteLinkFactory = $objectManager->get(CompanyQuoteLinkFactory::class);
        $this->companyQuoteLinkResourceModel = $objectManager->get(CompanyQuoteLink::class);
        $this->uidEncoder = $objectManager->get(Uid::class);
        $this->cartRepositoryPlugin = $objectManager->get(CartRepositoryInterfacePlugin::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(Customer::class, as: 'customer_b'),
        DataFixture(Customer::class, as: 'customer_ab'),
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
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_ab.id$'], 'quote'),
        DataFixture(Product::class, as: 'product'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 5]),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testCustomerCompanyLinkForMultiCompanyCustomerDefaultCompany(): void
    {
        $companyId = (int)DataFixtureStorageManager::getStorage()->get('company_a')->getId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer_ab');

        $headers = $this->getHeaderMap($customer->getEmail());

        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $this->graphQlMutation($query, [], '', $headers);

        $quoteId = (int)DataFixtureStorageManager::getStorage()->get('quote')->getId();

        //assert Company Customer Link database
        $companyQuoteLink = $this->companyQuoteLinkFactory->create();
        $this->companyQuoteLinkResourceModel->load($companyQuoteLink, $quoteId, 'quote_id');
        $this->assertEquals($companyId, $companyQuoteLink->getData('company_id'));

        // Resetting quote plugin state, as test and actual instance where service is running are different objects.
        $this->cartRepositoryPlugin->_resetState();

        //assert Company Id in quote extension attribute
        $quote = $this->quoteRepository->get($quoteId);
        $this->assertEquals(
            $companyId,
            $quote->getExtensionAttributes()->getCompanyId()
        );
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(Customer::class, as: 'customer_b'),
        DataFixture(Customer::class, as: 'customer_ab'),
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
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_ab.id$'], 'quote'),
        DataFixture(
            AssignCompanyToQuote::class,
            [
                'company_id' => '$company_b.id$',
                CartInterface::KEY_ENTITY_ID => '$quote.id$',
            ]
        ),
        DataFixture(Product::class, as: 'product'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 5]),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testCustomerCompanyLinkForMultiCompanyCustomerNonDefaultCompany(): void
    {
        $companyId = (int)DataFixtureStorageManager::getStorage()->get('company_b')->getId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer_ab');

        $headers = $this->getHeaderMap($customer->getEmail());
        $headers['X-Adobe-Company'] = $this->uidEncoder->encode(
            (string)$companyId
        );

        $maskedQuoteId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $query = $this->getQuery($maskedQuoteId);
        $this->graphQlMutation($query, [], '', $headers);

        $quoteId = (int)DataFixtureStorageManager::getStorage()->get('quote')->getId();

        //assert Company Customer Link database
        $companyQuoteLink = $this->companyQuoteLinkFactory->create();
        $this->companyQuoteLinkResourceModel->load($companyQuoteLink, $quoteId, 'quote_id');
        $this->assertEquals($companyId, $companyQuoteLink->getData('company_id'));

        // Resetting quote plugin state, as test and actual instance where service is running are different objects.
        $this->cartRepositoryPlugin->_resetState();

        //assert Company Id in quote extension attribute
        $quote = $this->quoteRepository->get($quoteId);
        $this->assertEquals(
            $companyId,
            $quote->getExtensionAttributes()->getCompanyId()
        );
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
