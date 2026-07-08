<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
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
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\NegotiableQuote\Api;

use Magento\Catalog\Test\Fixture\Product;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
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
use Magento\Quote\Model\QuoteRepositoryFactory;

class NegotiableQuoteItemTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/negotiableQuote/%d';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QuoteRepositoryFactory
     */
    private $quoteRepositoryFactory;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $this->objectManager = Bootstrap::getObjectManager();
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
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'negotiable_quote'
        )
    ]
    /**
     * @dataProvider negotiableQuoteDataProvider
     */
    public function testNegotiableQuoteItemDiscount(
        array $requestData,
        float $expectedRowTotal,
        float $expectedSubtotal
    ): void {
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $quoteItem = DataFixtureStorageManager::getStorage()->get('item');
        $quote = DataFixtureStorageManager::getStorage()->get('quote');

        // fixture data
        $requestData['quote']['id'] = $quote->getId();
        $requestData['quote']['items'][0]['item_id'] = $quoteItem->getId();
        $requestData['quote']['items'][0]['sku'] = $product->getSku();
        $requestData['quote']['items'][0]['extension_attributes']
        ['negotiable_quote_item']['item_id'] = $quoteItem->getId();

        $resourcePath = sprintf(self::RESOURCE_PATH, $quote->getId());
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ]
        ];
        $this->_webApiCall($serviceInfo, $requestData);
        $quoteRepository = $this->quoteRepositoryFactory->create();
        $actualQuote = $quoteRepository->get($quote->getId());
        $dbItem = $actualQuote->getItemById($quoteItem->getId());
        $this->assertEquals($expectedRowTotal, $dbItem->getRowTotal());
        $this->assertEquals($expectedSubtotal, $actualQuote->getSubtotal());
    }

    public function negotiableQuoteDataProvider(): array
    {
        $baseRequestData = [
            'quote' => [
                'items' => [
                    [
                        'qty' => 3,
                        'extension_attributes' => [
                            'negotiable_quote_item' => [
                                'extension_attributes' => [
                                    'negotiated_price_type' => 1,
                                    'negotiated_price_value' => 10,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return [
            // Test with extension attributes in whole quote
            [
                array_merge($baseRequestData, [
                    'quote' => array_merge($baseRequestData['quote'], [
                        'extension_attributes' => [
                            'negotiable_quote' => [
                                'negotiated_price_type' => 1,
                                'negotiated_price_value' => 50,
                            ],
                        ],
                    ]),
                ]),
                13.5000,
                13.5000,
            ],
            // Test without extension attributes in whole quote
            [
                $baseRequestData,
                40.0000,
                40.0000,
            ],
        ];
    }
}
