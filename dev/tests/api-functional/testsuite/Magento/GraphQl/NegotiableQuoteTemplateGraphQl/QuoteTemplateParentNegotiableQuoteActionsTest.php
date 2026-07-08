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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\NegotiableQuoteGraphQl\Model\NegotiableQuote\IdEncoder;
use Magento\NegotiableQuoteGraphQl\Model\NegotiableQuote\ResourceModel\QuoteIdMask as QuoteIdMaskResourceModel;
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

class QuoteTemplateParentNegotiableQuoteActionsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface|mixed|null
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var CartRepositoryInterface|null
     */
    private ?CartRepositoryInterface $quoteRepository;

    /**
     * @var IdEncoder|null
     */
    private ?IdEncoder $idEncoder;

    /**
     * @var QuoteIdMaskResourceModel|null
     */
    private ?QuoteIdMaskResourceModel $idMaskProvider;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->idEncoder = Bootstrap::getObjectManager()->get(IdEncoder::class);
        $this->idMaskProvider = Bootstrap::getObjectManager()->get(QuoteIdMaskResourceModel::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            as: 'customer'
        ),
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
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_PROCESSING_BY_SELLER,
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testItemRemovalNotAllowed()
    {
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $parentQuote = $this->quoteRepository->get($quoteTemplate->getParentQuoteId());
        $parentQuoteUId = $this->idMaskProvider->getMaskedQuoteId((int)$parentQuote->getId());
        $items = $parentQuote->getItems();
        $itemId = $this->idEncoder->encode((string)(array_pop($items)->getId()));

        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $eMsg = 'The quotes with the following UIDs have a status that does not allow them to be edited or submitted';
     //   $this->expectExceptionMessage($eMsg);
     //   $this->expectException(\Exception::class);

        try {
            $this->graphQlMutation(
                $this->getRemoveNegotiableQuoteItemQuery($parentQuoteUId, $itemId),
                [],
                '',
                ['Authorization' => sprintf('Bearer %s', $customerToken)]
            );
            $this->fail('Expected exception was not thrown. Item removal should not be allowed');
        } catch (\Exception $e) {
            //close negotiable quote to allow cleanup of fixture
            $negotiableQuote = $parentQuote->getExtensionAttributes()->getNegotiableQuote();
            $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_CLOSED);
            $this->quoteRepository->save($parentQuote);
            $negotiableQuote = DataFixtureStorageManager::getStorage()->get('negotiable_quote');
            $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_CLOSED);
            $negotiableQuote->save();
            $this->assertStringContainsString($eMsg, $e->getMessage());
        }
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            as: 'customer'
        ),
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
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_ACTIVE,
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testItemRemovalNotAllowedActiveQuoteTemplate()
    {
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $parentQuote = $this->quoteRepository->get($quoteTemplate->getParentQuoteId());
        $parentQuoteUId = $this->idMaskProvider->getMaskedQuoteId((int)$parentQuote->getId());
        $items = $parentQuote->getItems();
        $itemId = $this->idEncoder->encode((string)(array_pop($items)->getId()));

        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $eMsg = 'The quotes with the following UIDs have a status that does not allow them to be edited or submitted';
        $this->expectExceptionMessage($eMsg);
        $this->expectException(\Exception::class);
        $this->graphQlMutation(
            $this->getRemoveNegotiableQuoteItemQuery($parentQuoteUId, $itemId),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );
        //close negotiable quote to allow cleanup of fixture.
        $negotiableQuote = $parentQuote->getExtensionAttributes()->getNegotiableQuote();
        $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_CLOSED);
        $this->quoteRepository->save($parentQuote);
        $negotiableQuote = DataFixtureStorageManager::getStorage()->get('negotiable_quote');
        $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_CLOSED);
        $negotiableQuote->save();
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            as: 'customer'
        ),
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
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_ACTIVE
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testItemQtyUpdateNotAllowedActiveQuoteTemplate()
    {
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $parentQuote = $this->quoteRepository->get($quoteTemplate->getParentQuoteId());
        $parentQuoteUId = $this->idMaskProvider->getMaskedQuoteId((int)$parentQuote->getId());
        $items = $parentQuote->getItems();
        $itemId = $this->idEncoder->encode((string)(array_pop($items)->getId()));

        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $eMsg = 'The quotes with the following UIDs have a status that does not allow them to be edited or submitted';
        $this->expectExceptionMessage($eMsg);
        $this->expectException(\Exception::class);
        $this->graphQlMutation(
            $this->getItemQtyUpdateMutation($parentQuoteUId, $itemId, 5),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );
    }

    /**
     * Generates GraphQl mutation to remove quote item from negotiable quote
     *
     * @param string $quoteId
     * @param string $itemId
     * @return string
     */
    private function getRemoveNegotiableQuoteItemQuery(string $quoteId, string $itemId): string
    {
        return <<<MUTATION
mutation {
  removeNegotiableQuoteItems(
    input: {
      quote_uid: "{$quoteId}"
      quote_item_uids: ["{$itemId}"]
    }
  ) {
    quote {
      uid
      name
      status
      created_at
      updated_at
      items {
        id
      }
    }
  }
}
MUTATION;
    }

    /**
     * Generates GraphQl mutation to update negotiable quote item quantity
     *
     * @param string $quoteId
     * @param string $quoteItemId
     * @param float $quantity
     *
     * @return string
     */
    private function getItemQtyUpdateMutation(string $quoteId, string $quoteItemId, float $quantity): string
    {
        return <<<MUTATION
mutation {
  updateNegotiableQuoteQuantities(
    input: {
      quote_uid: "{$quoteId}"
      items: [
        {
          quote_item_uid: "{$quoteItemId}",
          quantity: {$quantity},
        }
      ]
    }
  ) {
    quote {
      uid
      items {
        id
        quantity
      }
    }
  }
}
MUTATION;
    }
}
