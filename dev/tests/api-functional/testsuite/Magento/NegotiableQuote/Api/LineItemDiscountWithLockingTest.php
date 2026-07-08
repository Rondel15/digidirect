<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NegotiableQuote\Api;

use Magento\Catalog\Test\Fixture\Product;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteRepositoryFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Framework\Webapi\Rest\Request;
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

use Magento\TestFramework\Helper\Bootstrap;

class LineItemDiscountWithLockingTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/carts/%d/items/%d';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteRepositoryFactory
     */
    private $quoteRepositoryFactory;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->quoteRepositoryFactory = $this->objectManager->get(QuoteRepositoryFactory::class);
    }

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
        DataFixture(Product::class, ['sku' => 'simpleprod1','url_key' => 'simpleprod1'], 'product1'),
        DataFixture(Product::class, ['sku' => 'simpleprod2', 'url_key' => 'simpleprod2'], 'product2'),
        DataFixture(Product::class, ['sku' => 'simpleprod3', 'url_key' => 'simpleprod3'], 'product3'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product1.id$',
                'qty' => 2
            ],
            'item1'
        ),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product2.id$'
            ],
            'item2'
        ),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product3.id$',
                'qty' => 3
            ],
            'item3'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            ['company_id' => '$company.entity_id$', 'company_quote_enabled' => 1]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #12',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item1.item_id$',
                            CartItemInterface::KEY_SKU => '$product1.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item2.item_id$',
                            CartItemInterface::KEY_SKU => '$product2.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ],
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item3.item_id$',
                            CartItemInterface::KEY_SKU => '$product3.sku$',
                            CartItemInterface::KEY_QTY => 3,
                        ]
                    ],
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 10
            ],
            'negotiable_quote'
        )
    ]
    public function testAddItemDiscountLockWithQuotePercentageDiscount(): void
    {
        $quoteItem = DataFixtureStorageManager::getStorage()->get('item3');

        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $resourcePath = sprintf(self::RESOURCE_PATH, $quote->getId(), $quoteItem->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];
        $requestData = [
            'cartItem' => [
                'item_id' => $quoteItem->getId(),
                'quote_id' => $quote->getId(),
                'qty' => 3,
                'extension_attributes' => [
                    'negotiable_quote_item' => [
                        'item_id' => $quoteItem->getId(),
                        'extension_attributes' => [
                            'negotiated_price_type' => 1,
                            'negotiated_price_value' => 5,
                            'is_discounting_locked' => 1
                        ]
                    ]
                ]
            ]
        ];

        $itemReturned = $this->_webApiCall($serviceInfo, $requestData);
        $quoteRepository = $this->quoteRepositoryFactory->create();
        $actualQuote = $quoteRepository->get($quote->getId());
        $dbItem = $actualQuote->getItemById($quoteItem->getId());
        $this->assertEquals(9.5, $itemReturned['price']);
        $this->assertEquals(28.5, $dbItem->getRowTotal());
        $this->assertEquals(55.5, $actualQuote->getSubtotal());
    }

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
        DataFixture(Product::class, ['sku' => 'simpleprod4','url_key' => 'simpleprod4'], 'product1'),
        DataFixture(Product::class, ['sku' => 'simpleprod5', 'url_key' => 'simpleprod5'], 'product2'),
        DataFixture(Product::class, ['sku' => 'simpleprod6', 'url_key' => 'simpleprod6'], 'product3'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product1.id$',
                'qty' => 2
            ],
            'item1'
        ),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product2.id$'
            ],
            'item2'
        ),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product3.id$',
                'qty' => 3
            ],
            'item3'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            ['company_id' => '$company.entity_id$', 'company_quote_enabled' => 1]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #12',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item1.item_id$',
                            CartItemInterface::KEY_SKU => '$product1.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item2.item_id$',
                            CartItemInterface::KEY_SKU => '$product2.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ],
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item3.item_id$',
                            CartItemInterface::KEY_SKU => '$product3.sku$',
                            CartItemInterface::KEY_QTY => 3,
                        ]
                    ],
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 12
            ],
            'negotiable_quote'
        )
    ]
    public function testAddItemDiscountLockWithQuoteFixedAmountDiscount(): void
    {
        $quoteItem1 = DataFixtureStorageManager::getStorage()->get('item1');
        $quoteItem2 = DataFixtureStorageManager::getStorage()->get('item2');
        $quoteItem3 = DataFixtureStorageManager::getStorage()->get('item3');

        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $resourcePath = sprintf(self::RESOURCE_PATH, $quote->getId(), $quoteItem3->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];
        $requestData = [
            'cartItem' => [
                'item_id' => $quoteItem3->getId(),
                'quote_id' => $quote->getId(),
                'qty' => 3,
                'extension_attributes' => [
                    'negotiable_quote_item' => [
                        'item_id' => $quoteItem3->getId(),
                        'extension_attributes' => [
                            'negotiated_price_type' => 1,
                            'negotiated_price_value' => 10,
                            'is_discounting_locked' => 1
                        ]
                    ]
                ]
            ]
        ];

        $itemReturned = $this->_webApiCall($serviceInfo, $requestData);
        $quoteRepository = $this->quoteRepositoryFactory->create();
        $actualQuote = $quoteRepository->get($quote->getId());
        $dbItem1 = $actualQuote->getItemById($quoteItem1->getId());
        $dbItem2 = $actualQuote->getItemById($quoteItem2->getId());
        $dbItem3 = $actualQuote->getItemById($quoteItem3->getId());

        $this->assertEquals(9, $itemReturned['price']);
        $this->assertEquals(12, $dbItem1->getRowTotal());
        $this->assertEquals(6, $dbItem2->getRowTotal());
        $this->assertEquals(27, $dbItem3->getRowTotal());
        $this->assertEquals(45, $actualQuote->getSubtotal());
    }

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
        DataFixture(Product::class, ['sku' => 'simpleprod7','url_key' => 'simpleprod7'], 'product1'),
        DataFixture(Product::class, ['sku' => 'simpleprod8', 'url_key' => 'simpleprod8'], 'product2'),
        DataFixture(Product::class, ['sku' => 'simpleprod9', 'url_key' => 'simpleprod9'], 'product3'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product1.id$',
                'qty' => 2
            ],
            'item1'
        ),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product2.id$'
            ],
            'item2'
        ),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product3.id$',
                'qty' => 3
            ],
            'item3'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            ['company_id' => '$company.entity_id$', 'company_quote_enabled' => 1]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #12',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item1.item_id$',
                            CartItemInterface::KEY_SKU => '$product1.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item2.item_id$',
                            CartItemInterface::KEY_SKU => '$product2.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ],
                        [
                            CartItemInterface::KEY_ITEM_ID => '$item3.item_id$',
                            CartItemInterface::KEY_SKU => '$product3.sku$',
                            CartItemInterface::KEY_QTY => 3,
                        ]
                    ],
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 45
            ],
            'negotiable_quote'
        )
    ]
    public function testAddItemDiscountLockWithQuoteProposedAmount(): void
    {
        $quoteItem1 = DataFixtureStorageManager::getStorage()->get('item1');
        $quoteItem2 = DataFixtureStorageManager::getStorage()->get('item2');
        $quoteItem3 = DataFixtureStorageManager::getStorage()->get('item3');

        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $resourcePath = sprintf(self::RESOURCE_PATH, $quote->getId(), $quoteItem3->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];
        $requestData = [
            'cartItem' => [
                'item_id' => $quoteItem3->getId(),
                'quote_id' => $quote->getId(),
                'qty' => 3,
                'extension_attributes' => [
                    'negotiable_quote_item' => [
                        'item_id' => $quoteItem3->getId(),
                        'extension_attributes' => [
                            'negotiated_price_type' => 1,
                            'negotiated_price_value' => 10,
                            'is_discounting_locked' => 1
                        ]
                    ]
                ]
            ]
        ];

        $itemReturned = $this->_webApiCall($serviceInfo, $requestData);
        $quoteRepository = $this->quoteRepositoryFactory->create();
        $actualQuote = $quoteRepository->get($quote->getId());
        $dbItem1 = $actualQuote->getItemById($quoteItem1->getId());
        $dbItem2 = $actualQuote->getItemById($quoteItem2->getId());
        $dbItem3 = $actualQuote->getItemById($quoteItem3->getId());

        $this->assertEquals(9, $itemReturned['price']);
        $this->assertEquals(12, $dbItem1->getRowTotal());
        $this->assertEquals(6, $dbItem2->getRowTotal());
        $this->assertEquals(27, $dbItem3->getRowTotal());
        $this->assertEquals(45, $actualQuote->getSubtotal());
    }
}
