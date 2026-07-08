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

namespace Magento\NegotiableQuoteDuplicate\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Test\Fixture\CreateNegotiableQuoteFromQuote;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuoteItemDiscount;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\User\Test\Fixture\User;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class DuplicateNegotiableQuoteTest extends WebapiAbstract
{
    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->negotiableQuoteRepository = $objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $this->negotiableQuoteManagement = $objectManager->get(NegotiableQuoteManagementInterface::class);
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        Config('btob/website_configuration/sharedcatalog_active', 0),
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
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote to be duplicated',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'quote'
        )
    ]
    public function testDuplicateNegotiableQuote(): void
    {
        /** @var NegotiableQuoteInterface $quote */
        $quote = DataFixtureStorageManager::getStorage()->get('quote');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf('/V1/negotiableQuote/%d/duplicate', $quote->getId()),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];
        $result = $this->_webApiCall($serviceInfo);
        $duplicatedQuoteName = $this->negotiableQuoteRepository->getById((int)$result)->getQuoteName();

        $this->assertStringContainsString($quote->getQuoteName(), $duplicatedQuoteName);
        $this->assertStringEndsWith(" (copy)", $duplicatedQuoteName);
    }

    #[
        AppIsolation(true),
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'admin'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin.id$',
            ],
            'company'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$admin.id$'], 'quote'),
        DataFixture(ProductFixture::class, ['price' => 10], as: 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], as: 'p2'),
        DataFixture(ProductFixture::class, ['price' => 30], as: 'p3'),
        DataFixture(
            AttributeFixture::class,
            [
                'options' => [
                    [
                        'label' => 'option_1',
                        'sort_order' => 0,
                    ],
                    [
                        'label' => 'option_2',
                        'sort_order' => 1,
                    ],
                    [
                        'label' => 'option_3',
                        'sort_order' => 2,
                    ],
                ]
            ],
            'attr'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$', '$p3$']],
            'cp1'
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$cp1.id$',
                'child_product_id' => '$p1.id$',
                'qty' => 1
            ],
            'item1'
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$cp1.id$',
                'child_product_id' => '$p2.id$',
                'qty' => 1
            ],
            'item2'
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$cp1.id$',
                'child_product_id' => '$p3.id$',
                'qty' => 1
            ],
            'item3'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #1',
                NegotiableQuoteInterface::CREATOR_TYPE => UserContextInterface::USER_TYPE_ADMIN,
                NegotiableQuoteInterface::CREATOR_ID => '$sales_rep.id$',

                'quote' => [
                    'customer_id' => '$admin.id$',
                    CartInterface::KEY_ITEMS => [],
                ],
                NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_DRAFT_BY_ADMIN,

                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 20
            ],
            'negotiable_quote'
        ),
        DataFixture(
            NegotiableQuoteItemDiscount::class,
            [
                'quote_id' => '$quote.id$',
                'item_id' => '$item1.id$',
                'item_sku' => '$p1.sku$',
                'negotiated_price_type' => 1,
                'negotiated_price_value' => 50.0,
                'is_discounting_locked' => 0
            ]
        ),
        DataFixture(
            NegotiableQuoteItemDiscount::class,
            [
                'quote_id' => '$quote.id$',
                'item_id' => '$item2.id$',
                'item_sku' => '$p2.sku$',
                'negotiated_price_type' => 2,
                'negotiated_price_value' => 5.0,
                'is_discounting_locked' => 1
            ]
        ),
        DataFixture(
            NegotiableQuoteItemDiscount::class,
            [
                'quote_id' => '$quote.id$',
                'item_id' => '$item3.id$',
                'item_sku' => '$p3.sku$',
                'negotiated_price_type' => 3,
                'negotiated_price_value' => 5.0,
                'is_discounting_locked' => 0
            ]
        ),

    ]
    public function testDuplicateNegotiableQuoteTotalsWithCompositeProductsAndLineItemDiscounts(): void
    {
        /** @var CartInterface $quote */
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $this->negotiableQuoteManagement->recalculateQuote($quote->getId());
        $quote = $this->quoteRepository->get($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf('/V1/negotiableQuote/%d/duplicate', $quote->getId()),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];
        $result = $this->_webApiCall($serviceInfo);
        $duplicateQuote = $this->quoteRepository->get((int)$result);
        $this->assertEquals($quote->getSubtotal(), $duplicateQuote->getSubtotal());
        $this->assertEquals($quote->getGrandTotal(), $duplicateQuote->getGrandTotal());
        foreach ($duplicateQuote->getAllVisibleItems() as $duplicateItem) {
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getSku() === $duplicateItem->getSku()) {
                    $this->assertEquals($item->getQty(), $duplicateItem->getQty());
                    $this->assertEquals($item->getRowTotal(), $duplicateItem->getRowTotal());
                }
            }
        }
    }
}
