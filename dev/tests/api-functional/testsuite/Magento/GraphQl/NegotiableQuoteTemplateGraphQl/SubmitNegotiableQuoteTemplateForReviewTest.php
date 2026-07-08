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
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\QuoteIdMask;
use Magento\NegotiableQuoteTemplate\Api\Data\TemplateInterface;
use Magento\NegotiableQuoteTemplate\Model\Template\Status\BuyerStatusProvider as StatusProvider;
use Magento\NegotiableQuoteTemplate\Test\Fixture\Template;
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

class SubmitNegotiableQuoteTemplateForReviewTest extends GraphQlAbstract
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
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->statusProvider = Bootstrap::getObjectManager()->get(StatusProvider::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            [
                CustomerInterface::KEY_ADDRESSES =>[
                    [
                        'firstname' => 'John',
                        'lastname' => 'Doe',
                        'street' => ['123 Test Street'],
                        'city' => 'Los Angeles',
                        'postcode' => '90001',
                        'telephone' => '1234567890'
                    ]
                ]
            ],
            'customer'
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
                TemplateInterface::IS_MIN_MAX_QTY_USED => true
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testSubmitNegotiableQuoteTemplateForReview()
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $shippingAddress = $customer->getDefaultShippingAddress();
        $shippingAddressUid = base64_encode($shippingAddress->getId());

        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $templateId = $quoteTemplate->getTemplateId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $this->graphQlMutation(
            $this->getMutationToAddShippingAddress($templateId, $shippingAddressUid),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );

        $templateData = [
            TemplateInterface::NAME => 'My Quote template',
            TemplateInterface::MIN_ORDERS => 10,
            TemplateInterface::MAX_ORDERS => 100
        ];
        $response = $this->graphQlMutation(
            $this->getMutationToSendForReview($templateId, $templateData),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );

        $expectedStatusLabel = $this->statusProvider->getStatusLabel(TemplateInterface::STATUS_CREATED_BY_BUYER);
        $this->assertEquals(
            $expectedStatusLabel,
            $response['submitNegotiableQuoteTemplateForReview']['status']
        );
        $responseTemplateData = [
            TemplateInterface::NAME => $response['submitNegotiableQuoteTemplateForReview']['name'],
            TemplateInterface::MIN_ORDERS =>
                $response['submitNegotiableQuoteTemplateForReview']['min_order_commitment'],
            TemplateInterface::MAX_ORDERS => $response['submitNegotiableQuoteTemplateForReview']['max_order_commitment']
        ];
        $this->assertEquals(
            $templateData,
            $responseTemplateData
        );
    }

    /**
     * Returns GraphQl Query string to create a quote template from quote
     *
     * @param int $templateId
     * @param string $customerAddressUid
     * @return string
     */
    private function getMutationToAddShippingAddress(int $templateId, string $customerAddressUid): string
    {
        return <<<MUTATION
mutation
  {
    setNegotiableQuoteTemplateShippingAddress(
    input: {
    template_id: "$templateId",
      shipping_address: {
         customer_address_uid: "$customerAddressUid"
      }
    }
)
   {
       template_id
       shipping_addresses {
           firstname
           lastname
           street
           city
           postcode
           telephone
       }
   }
}
MUTATION;
    }

    /**
     * Returns GraphQl Query string to create a quote template from quote
     *
     * @param int $templateId
     * @param array $templateData
     * @return string
     */
    private function getMutationToSendForReview(int $templateId, array $templateData): string
    {
        $name = $templateData[TemplateInterface::NAME];
        $minOrderCommitment = $templateData[TemplateInterface::MIN_ORDERS];
        $maxOrderCommitment = $templateData[TemplateInterface::MAX_ORDERS];

        return <<<MUTATION
mutation
  {
    submitNegotiableQuoteTemplateForReview(
    input: {
    template_id: "$templateId",
      name: "$name",
      min_order_commitment: "$minOrderCommitment",
      max_order_commitment: "$maxOrderCommitment"
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
              id
              quantity
              min_qty
              max_qty
          }
      }
  }
MUTATION;
    }
}
