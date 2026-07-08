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
use Magento\NegotiableQuoteTemplate\Api\Data\TemplateInterface;
use Magento\NegotiableQuoteTemplate\Test\Fixture\Template;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Api\Data\CartInterface;

class UpdateNegotiableQuoteTemplateQuantitiesTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface|mixed|null
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var CartRepositoryInterface|null
     */
    private ?CartRepositoryInterface $quoteRepository;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
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
            'negotiable_quote'
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => true
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testUpdateNegotiableQuoteTemplateQuantities()
    {
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $parentQuote = $this->quoteRepository->get($quoteTemplate->getParentQuoteId());
        $templateId = $quoteTemplate->getTemplateId();

        $quoteItem = $parentQuote->getAllItems()[0];
        $itemData = [
            'item_id' => $quoteItem->getId(),
            'qty' => $quoteItem->getData(CartItemInterface::KEY_QTY) + 1,
            'min_qty' => $quoteItem->getData(CartItemInterface::KEY_QTY) + 1,
            'max_qty' => 10
        ];

        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $response = $this->graphQlMutation(
            $this->getMutation($templateId, $itemData),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );

        $this->assertArrayHasKey('template_id', $response['updateNegotiableQuoteTemplateQuantities']['quote_template']);
        $expectedItems[] = [
            'product' => ['sku' => $quoteItem->getData(CartItemInterface::KEY_SKU)],
            'quantity' => $quoteItem->getData(CartItemInterface::KEY_QTY) + 1,
            'min_qty' => $quoteItem->getData(CartItemInterface::KEY_QTY) + 1,
            'max_qty' => 10
        ];
        $this->assertEquals(
            $expectedItems,
            $response['updateNegotiableQuoteTemplateQuantities']['quote_template']['items']
        );
    }

    /**
     * Returns GraphQl Query string to create a quote template from quote
     *
     * @param string $templateId
     * @param array $itemData
     * @return string
     */
    private function getMutation(int $templateId, array $itemData): string
    {
        $itemId = $itemData['item_id'];
        $itemQty = $itemData['qty'];
        $itemMinQty = $itemData['min_qty'];
        $itemMaxQty = $itemData['max_qty'];
        return <<<MUTATION
mutation
  {
    updateNegotiableQuoteTemplateQuantities(
    input: {
    template_id: "$templateId"
      items: [
    {
      item_id: "$itemId",
      quantity: $itemQty
      min_qty: $itemMinQty
      max_qty: $itemMaxQty
    }
  ]
  }

)
   {
        quote_template {
         template_id
         items {
              product {
                sku
              }
              quantity
              min_qty
              max_qty
          }

      }}
  }
MUTATION;
    }
}
