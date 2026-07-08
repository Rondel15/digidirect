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
use Magento\NegotiableQuoteTemplate\Api\Template\Actions\SellerSendInterface;
use Magento\NegotiableQuoteTemplate\Api\Template\RepositoryInterface;
use Magento\NegotiableQuoteTemplate\Model\Template\Actions\Send;
use Magento\NegotiableQuoteTemplate\Model\Template\Status\BuyerStatusProvider as StatusProvider;
use Magento\NegotiableQuoteTemplate\Model\Template\StatusManager;
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

class DeleteNegotiableQuoteTemplateTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface|mixed|null
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var RepositoryInterface|null
     */
    private ?RepositoryInterface $templateRepository;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->templateRepository = Bootstrap::getObjectManager()->get(RepositoryInterface::class);
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
                TemplateInterface::STATUS => TemplateInterface::STATUS_DRAFT_BY_BUYER,
            ],
            'template'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testDeleteNegotiableQuoteTemplate()
    {
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get('template');
        $templateId = $quoteTemplate->getTemplateId();
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $this->graphQlMutation(
            $this->getMutation($templateId),
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );
        //assert template not found
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a quote template with the specified ID.');
        $quoteTemplateQuery = $this->getQuery($quoteTemplate->getId());
        $this->graphQlQuery(
            $quoteTemplateQuery,
            [],
            '',
            ['Authorization' => sprintf('Bearer %s', $customerToken)]
        );
    }

    /**
     * Returns GraphQl Query string to create a quote template from quote
     *
     * @param int $templateId
     * @param string $cancellationComment
     * @return string
     */
    private function getMutation(int $templateId): string
    {
        return <<<MUTATION
mutation
  {
    deleteNegotiableQuoteTemplate(
    input: {template_id: "$templateId"}
)
}
MUTATION;
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
    }
}
QUERY;
    }
}
