<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\NegotiableQuoteTemplateGraphQl;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\NegotiableQuoteTemplate\Api\Data\TemplateInterface;
use Magento\NegotiableQuoteTemplate\Test\Fixture\Template;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\NegotiableQuoteTemplate\Api\Template\ManagementInterface as QuoteTemplateManagementInterface;
use Magento\NegotiableQuoteTemplate\Model\Template\Status\BuyerStatusProvider as StatusProvider;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for getting Negotiable Quote Template data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetNegotiableQuoteTemplateTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface|mixed|null
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var StatusProvider|null
     */
    private ?StatusProvider $statusProvider;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->statusProvider = $objectManager->get(StatusProvider::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(\Magento\User\Test\Fixture\User::class, as: 'user'),
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
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'quote'
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote.id$',
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testGetNegotiableQuoteTemplateById(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $quoteTemplateQuery = $this->getQuery($quoteTemplate->getId());
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $response = $this->graphQlQuery(
            $quoteTemplateQuery,
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );

        $this->assertArrayHasKey('negotiableQuoteTemplate', $response);
        $this->assertArrayHasKey('items', $response['negotiableQuoteTemplate']);

        $this->assertNotEmpty($response['negotiableQuoteTemplate']['template_id']);
        $this->assertNotEmpty($response['negotiableQuoteTemplate']['items']);
        $this->assertArrayHasKey('status', $response['negotiableQuoteTemplate']);
        $this->assertArrayHasKey('name', $response['negotiableQuoteTemplate']);
        $this->assertEquals($quoteTemplate->getId(), $response['negotiableQuoteTemplate']['template_id']);
        $this->assertEquals($quoteTemplate->getTemplateName(), $response['negotiableQuoteTemplate']['name']);
        $statusLabel = $this->statusProvider->getStatusLabel($quoteTemplate->getStatus());
        $this->assertEquals(
            $statusLabel,
            $response['negotiableQuoteTemplate']['status']
        );
        $this->assertNotEmpty($response['negotiableQuoteTemplate']['expiration_date']);
        // verify initial null expiration values in history
        $this->assertNull(
            $response['negotiableQuoteTemplate']['history'][0]['changes']['expiration']['old_expiration']
        );
        $this->assertNull(
            $response['negotiableQuoteTemplate']['history'][0]['changes']['expiration']['new_expiration']
        );
    }

    /**
     * Returns GraphQl Query string to get a negotiable quote template
     *
     * @param string $negotiableQuoteTemplateId
     * @return string
     */
    private function getQuery(string $negotiableQuoteTemplateId): string
    {
        return <<<QUERY
{
  negotiableQuoteTemplate(templateId: "{$negotiableQuoteTemplateId}") {
    template_id
    name
    status
    expiration_date
    items {
      id
      quantity
    }
    history {
      changes {
        expiration {
          old_expiration
          new_expiration
        }
      }
    }
    prices {
        grand_total {value}
    }
  }
}
QUERY;
    }
}
