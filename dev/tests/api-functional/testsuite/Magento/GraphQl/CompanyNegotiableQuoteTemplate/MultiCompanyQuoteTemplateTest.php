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

namespace Magento\GraphQl\CompanyNegotiableQuoteTemplate;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\CustomerGroup;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuoteTemplate\Api\Data\TemplateInterface;
use Magento\NegotiableQuoteTemplate\Test\Fixture\Template;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;

class MultiCompanyQuoteTemplateTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface|mixed|null
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * @var Uid|null
     */
    private ?Uid $uidEncoder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->uidEncoder = $objectManager->get(Uid::class);
    }

    /**
     * @param string $companyName
     * @param string $templateName
     * @param string $httpCode
     * @return void
     * @throws LocalizedException
     * @dataProvider dataTestTemplateView
     */
    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'admin_a'),
        DataFixture(Customer::class, as: 'admin_b'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(CustomerGroup::class, as: 'company_a_customer_group'),
        DataFixture(CustomerGroup::class, as: 'company_b_customer_group'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_a.id$',
                'customer_group_id' => '$company_a_customer_group.id$'
            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_b.id$',
                'customer_group_id' => '$company_b_customer_group.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer.id$',
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote_a'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_a.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item_a'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote A',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2
                        ],
                    ],
                ],
            ],
            'quote_a'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [
                'company_id' => '$company_a.id$',
                CartInterface::KEY_ENTITY_ID => '$quote_a.id$',
            ]
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Template A',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote_a.id$',
            ],
            'template_a'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [
                'company_id' => '$company_a.id$',
                CartInterface::KEY_ENTITY_ID => '$template_a.parent_quote_id$',
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote_b'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_b.id$', 'product_id' => '$product.id$', 'qty' => 4],
            'item_b'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote B',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 4
                        ],
                    ],
                ],
            ],
            'quote_b'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [
                'company_id' => '$company_b.id$',
                CartInterface::KEY_ENTITY_ID => '$quote_b.id$',
            ]
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Template B',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote_b.id$',
            ],
            'template_b'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [
                'company_id' => '$company_b.id$',
                CartInterface::KEY_ENTITY_ID => '$template_b.parent_quote_id$',
            ]
        ),
    ]
    public function testTemplateView(string $companyName, string $templateName, bool $error): void
    {
        /** @var CompanyInterface $company */
        $company = FixtureManager::getStorage()->get($companyName);

        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');

        /** @var TemplateInterface $template */
        $quoteTemplate = DataFixtureStorageManager::getStorage()->get($templateName);
        $quoteTemplateQuery = $this->getQuery($quoteTemplate->getId());
        $customerToken = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );
        $headers = [];
        $headers['Authorization'] = sprintf('Bearer %s', $customerToken);
        $headers['X-Adobe-Company'] = $this->uidEncoder->encode((string)$company->getId());
        if ($error) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Could not find a quote template with the specified ID.');
        }
        $this->graphQlQuery(
            $quoteTemplateQuery,
            [],
            '',
            $headers
        );
    }

    /**
     * @return array[]
     */
    public static function dataTestTemplateView(): array
    {
        return [
            ['company_a', 'template_a', false],
            ['company_a', 'template_b', true],
            ['company_b', 'template_a', true],
            ['company_b', 'template_b', false],
        ];
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
