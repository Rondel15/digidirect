<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Controller\Adminhtml\AbstractTest;
use Magento\NegotiableQuote\Model\QuoteItemHashHandler;
use Magento\NegotiableQuote\Model\QuoteUpdatesInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation\Rate as TaxRate;
use Magento\Tax\Model\Config;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\User\Test\Fixture\User;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote as NegotiableQuoteFixture;

/**
 * Tests quote update.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends AbstractTest
{
    /**
     * @var TaxRate
     */
    private $taxRate;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var MutableScopeConfigInterface
     */
    private $mutableScopeConfig;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->taxRate = $this->_objectManager->create(TaxRate::class);
        $this->customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $this->negotiableQuoteRepository = $this->_objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $this->negotiableQuoteManagement = $this->_objectManager->get(NegotiableQuoteManagementInterface::class);
        $this->mutableScopeConfig = $this->_objectManager->create(MutableScopeConfigInterface::class);
        $this->cartRepository = $this->_objectManager->create(CartRepositoryInterface::class);
    }

    #[
        ConfigFixture(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'store',
            scopeValue: 'default_store'
        ),
        ConfigFixture(
            'btob/website_configuration/company_active',
            1,
            scopeType: 'store',
            scopeValue: 'default_store'
        ),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_ab'),
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
                'super_user_id' => '$customer_ab.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 10,
            ],
            as: 'product'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 20,
            ],
            as: 'simple_for_quote'
        ),
        DataFixture(
            NegotiableQuoteFixture::class,
            [
                'quote' => [
                    'customer_id' => '$customer_ab.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ]
                    ]
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 5,
            ],
            'negotiable_quote'
        ),
    ]
    public function testUpdateQuote(): void
    {
        $customer = FixtureManager::getStorage()->get('customer_ab');
        $quotes = $this->negotiableQuoteRepository->getListByCustomerId($customer->getId());

        $quoteId = end($quotes)->getId();

        /** @var Product $product1 */
        $product1 = FixtureManager::getStorage()->get('product');
        /** @var Product $product2 */
        $product2 = FixtureManager::getStorage()->get('simple_for_quote');

        $postData = [
            'quote_id' => $quoteId,
            'quote' => [
                'items' => [
                    0 => [
                        'id' => $product1->getId(),
                        'qty' => '1',
                        'sku' => $product1->getSku(),
                        'productSku' => $product1->getSku(),
                        'config' => '',
                    ],
                ],
                'addItems' => [
                    0 => [
                        'qty' => '1',
                        'sku' => $product2->getSku(),
                    ],
                ],
                'update' => 1,
                'recalcPrice' => 1,
            ],
        ];

        $this->getRequest()->setPostValue($postData)->setMethod('POST');
        $this->dispatch('backend/quotes/quote/update/?isAjax=true');

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $quote = $quoteRepository->get($quoteId);
        /** @var \Magento\Company\Model\Company $company */
        $company = FixtureManager::getStorage()->get('company_b');
        $this->assertQuoteCompany((int)$quote->getId(), $company->getCompanyEmail());
        /** @var  QuoteUpdatesInfo $quoteInfo */
        $quoteInfo = $this->_objectManager->create(QuoteUpdatesInfo::class);
        $updatedData = $quoteInfo->getQuoteUpdatedData($quote, $postData);
        $this->assertQuoteItemPriceData(
            $updatedData['items'],
            $product1->getSku(),
            '$9.50',
            '$10.00',
            '$10.00',
            '$9.50',
        );
        $this->assertQuoteItemPriceData(
            $updatedData['items'],
            $product2->getSku(),
            '$19.00',
            '$20.00',
            '$20.00',
            '$19.00',
        );
    }

    /**
     * Perform assertions on item's price data.
     *
     * @param array $items
     * @param string $sku
     *
     * @return void
     */
    private function assertQuoteItemPriceData(
        array $itemsArray,
        string $itemSku,
        string $subtotal,
        string $cartPrice,
        string $originalPrice,
        string $proposedPrice
    ) {
        $foundItem = false;
        foreach ($itemsArray as $item) {
            if ($item['sku'] == $itemSku) {
                $this->assertEquals($subtotal, $item['subtotal']);
                $this->assertEquals($cartPrice, $item['cartPrice']);
                $this->assertEquals($originalPrice, $item['originalPrice']);
                $this->assertEquals($proposedPrice, $item['proposedPrice']);
                $foundItem = true;
            }
        }
        $this->assertTrue($foundItem, 'Item is not found in items array');
    }

    #[
        AppArea('adminhtml'),
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', 1),
        ConfigFixture('general/country/default', 'DE', ScopeInterface::SCOPE_STORE),
        ConfigFixture('shipping/origin/country_id', 'DE', ScopeInterface::SCOPE_STORE),
        ConfigFixture('shipping/origin/region_id', 82, ScopeInterface::SCOPE_STORE),
        ConfigFixture('shipping/origin/postcode', 10115, ScopeInterface::SCOPE_STORE),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_ab'),
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
                'super_user_id' => '$customer_ab.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 10,
            ],
            as: 'product'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_ab.id$'], 'quote'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 5],
            'item'
        ),
        DataFixture(
            NegotiableQuoteFixture::class,
            [
                'quote' => [
                    'customer_id' => '$customer_ab.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 5,
                        ]
                    ]
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 20,
            ],
            'negotiable_quote'
        ),
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'US',
                'code' => 'Test Rate US',
                'rate' => '10',
            ],
            'tax_rate_us',
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'code' => 'Test Rule US',
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$tax_rate_us.id$'],
            ],
            'tax_rule_us',
        ),
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'DE',
                'code' => 'Test Rate DE',
                'rate' => '21',
            ],
            'tax_rate_de',
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'code' => 'Test Rule DE',
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$tax_rate_de.id$'],
            ],
            'tax_rule_de',
        ),
    ]
    /**
     * @dataProvider taxRateDataProvider
     *
     * @param string $taxCalculationType
     * @param bool $isCatalogPriceIncludeTax
     * @param float $taxRateStore
     * @param float $taxRateCustomer
     * @param float $proposedPrice
     * @param bool $isQuoteSentToAdminFirstBeforeProposingPrice
     * @return void
     */
    public function testPriceCalculationWithDifferentTaxRateOnUpdateQuote(
        string $taxCalculationType,
        bool $isCatalogPriceIncludeTax,
        float $taxRateStore,
        float $taxRateCustomer,
        float $proposedPrice,
        bool $isQuoteSentToAdminFirstBeforeProposingPrice,
        bool $isCrossBorderTradeEnabled
    ): void {
        // Set Tax calculation basis (either shipping or billing)
        $this->mutableScopeConfig->setValue(
            'tax/calculation/based_on',
            $taxCalculationType,
            ScopeInterface::SCOPE_STORE
        );
        $this->mutableScopeConfig->setValue(
            Config::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED,
            $isCrossBorderTradeEnabled,
            ScopeInterface::SCOPE_STORE
        );

        // Set whether catalog prices include tax (either true or false)
        $this->mutableScopeConfig->setValue(
            'tax/calculation/price_includes_tax',
            $isCatalogPriceIncludeTax,
            ScopeInterface::SCOPE_STORE
        );

        // Get DE (Where the store is based) Tax Rate and assign $taxRateStore
        /** @var TaxRate $fixtureTaxRateDE */
        $fixtureTaxRateDE = FixtureManager::getStorage()->get('tax_rate_de');
        $fixtureTaxRateDE
            ->setRate($taxRateStore)
            ->save();

        // Get US (Where the customer is based) Tax Rate and assign $taxRateCustomer
        /** @var TaxRate $fixtureTaxRateUS */
        $fixtureTaxRateUS = FixtureManager::getStorage()->get('tax_rate_us');
        $fixtureTaxRateUS
            ->setRate($taxRateCustomer)
            ->save();

        /** @var CartInterface $quote */
        $quote = FixtureManager::getStorage()->get('quote');
        $quoteId = $quote->getId();

        if ($isQuoteSentToAdminFirstBeforeProposingPrice) {
            $this->negotiableQuoteManagement->send($quoteId);
        }
        $quoteItems = $quote->getAllItems();

        $postData = [
            'quote_id' => $quoteId,
            'quote' => [
                'items' => [
                    0 => [
                        'id' => end($quoteItems)->getId(),
                        'qty' => '1',
                        'sku' => 'simple',
                        'productSku' => 'simple',
                        'config' => '',
                    ],
                ],
                'proposed' => [
                    'type' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
                    'value' => $proposedPrice,
                ],
                'update' => 0
            ],
            'negotiable_quote_update_flag' => true,
        ];

        $this->getRequest()->setPostValue($postData)->setMethod('POST');
        $this->dispatch('backend/quotes/quote/update/?isAjax=true');

        $response = $this->getResponse();
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals($proposedPrice, (float) trim($responseContent['quoteSubtotal']['base'], '$'));
        $this->assertEquals($proposedPrice, (float) trim($responseContent['items'][0]['proposedPrice'], '$'));

        $this->assertEquals(
            round($proposedPrice * ((100 + $taxRateCustomer) / 100), PriceCurrencyInterface::DEFAULT_PRECISION),
            (float) trim($responseContent['grandTotal']['base'], '$')
        );
    }

    /**
     * Data provider for testPriceCalculationWithDifferentTaxRateOnUpdateQuote
     *
     * @return array
     */
    public static function taxRateDataProvider(): array
    {
        return [
            ['shipping', true, 20.0, 0.0, 2.00, true, false],
            ['shipping', false, 21.0, 10.0, 2.00, true, false],
            ['shipping', true, 21.0, 10.0, 2.00, false, false],
            ['shipping', false, 21.0, 10.0, 2.00, false, false],
            ['shipping', false, 10.0, 25.5, 2.11, false, false],
            ['shipping', false, 0, 10.0, 5.00, false, false],
            ['shipping', false, 10.0, 0, 5.00, false, false],
            ['shipping', false, 0, 0, 10.00, false, false],
            ['shipping', false, 10.0, 10.0, 20.00, false, false],
            ['billing', true, 21.0, 10.0, 2.00, true, false],
            ['billing', false, 21.0, 10.0, 2.00, true, false],
            ['billing', true, 21.0, 10.0, 2.00, false, false],
            ['billing', false, 21.0, 10.0, 2.00, false, false],
            ['shipping', true, 20.0, 10.0, 2.00, true, true],
            ['billing', true, 10.0, 20.0, 2.00, true, true],
        ];
    }

    /**
     * Data provider for testLineItemDiscountUpdateQuote
     *
     * @return array
     */
    public static function lineItemDiscountsDataPovider(): array
    {
        $data = [];
        $itemLevelPercentageDiscountCase = [];
        $itemLevelPercentageDiscountCase['item_data'] = [
            'negotiated_price_type' => NegotiableQuoteItemInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
            'negotiated_price_value' => 20.0,
            'qty' => 2
        ];
        $itemLevelPercentageDiscountCase['expected_item_data']['totals'] = [
            'subtotal' => '$12.80',
            'cartPrice' => '$10.00',
            'originalPrice' => '$10.00',
            'proposedPrice' => '$6.40',
            'itemDiscount' => '20% ($2.00)'
        ];
        $data[] = [$itemLevelPercentageDiscountCase];

        $itemLevelFixedDiscountCase = [];
        $itemLevelFixedDiscountCase['item_data'] = [
            'negotiated_price_type' => NegotiableQuoteItemInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT,
            'negotiated_price_value' => 5.0,
            'qty' => 1
            ];
        $itemLevelFixedDiscountCase['expected_item_data']['totals'] = [
            'subtotal' => '$4.00',
            'cartPrice' => '$10.00',
            'originalPrice' => '$10.00',
            'proposedPrice' => '$4.00',
            'itemDiscount' => '$5.00'
            ];
        $data[] = [$itemLevelFixedDiscountCase];

        $itemLevelProposedPriceCase = [];
        $itemLevelProposedPriceCase['item_data'] = [
            'negotiated_price_type' => NegotiableQuoteItemInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
            'negotiated_price_value' => 4.0,
            'qty' => 3
        ];
        $itemLevelProposedPriceCase['expected_item_data']['totals'] = [
            'subtotal' => '$9.60',
            'cartPrice' => '$10.00',
            'originalPrice' => '$10.00',
            'proposedPrice' => '$3.20',
            'itemDiscount' => '$6.00'

        ];
        $data[] = [$itemLevelProposedPriceCase];
        return $data;
    }

    /**
     * @dataProvider lineItemDiscountsDataPovider
     *
     * @return void
     */
    #[
        ConfigFixture(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'store',
            scopeValue: 'default_store'
        ),
        ConfigFixture(
            'btob/website_configuration/company_active',
            1,
            scopeType: 'store',
            scopeValue: 'default_store'
        ),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_ab'),
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
                'super_user_id' => '$customer_ab.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 10,
            ],
            as: 'simple'
        ),
        DataFixture(
            NegotiableQuoteFixture::class,
            [
                'quote' => [
                    'customer_id' => '$customer_ab.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$simple.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ]
                    ]
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 20,
            ],
            'negotiable_quote'
        ),
    ]
    public function testLineItemDiscountUpdateQuote($testData): void
    {
        /** @var NegotiableQuoteRepositoryInterface $negotiableRepository */
        $negotiableRepository = $this->_objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $quoteItemHashHandler = $this->_objectManager->get(QuoteItemHashHandler::class);

        $customer = FixtureManager::getStorage()->get('customer_ab');
        $simpleProduct = FixtureManager::getStorage()->get('simple');
        $quotes = $negotiableRepository->getListByCustomerId($customer->getId());

        $quote = end($quotes);
        $quoteId = $quote->getId();
        $items = $quote->getAllItems();
        $itemSimple = $items[0];
        foreach ($quote->getAllItems() as $item) {
            if ($item->getSku() == 'simple') {
                $itemSimple = $item;
                break;
            }
        }

        $postData = [
            'quote_id' => $quoteId,
            'quote' => [
                'items' => [
                    0 => [
                        'id' => $simpleProduct->getId(),
                        'qty' => $testData['item_data']['qty'],
                        'sku' => $simpleProduct->getSku(),
                        'productSku' => $simpleProduct->getSku(),
                        'config' => '',
                        'negotiated_price_type' => $testData['item_data']['negotiated_price_type'],
                        'negotiated_price_value' => $testData['item_data']['negotiated_price_value'],
                        'item_hash' => $quoteItemHashHandler->getItemHashToIdItem($itemSimple),
                    ],
                ],
                'addItems' => [],
                'update' => 1,
                'recalcPrice' => 1,
            ],
        ];

        $this->getRequest()->setPostValue($postData)->setMethod('POST');
        $this->dispatch('backend/quotes/quote/update/?isAjax=true');

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $quote = $quoteRepository->get($quoteId);
        /** @var \Magento\Company\Model\Company $company */
        $company = FixtureManager::getStorage()->get('company_b');
        $this->assertQuoteCompany((int)$quote->getId(), $company->getCompanyEmail());
        /** @var  QuoteUpdatesInfo $quoteInfo */
        $quoteInfo = $this->_objectManager->create(QuoteUpdatesInfo::class);
        $updatedData = $quoteInfo->getQuoteUpdatedData($quote, $postData);

        foreach ($updatedData['items'] as $item) {
            $this->assertLineItemDiscountData($item, $testData);
        }
    }

    /**
     * @param array $item
     * @param array $testData
     */
    private function assertLineItemDiscountData(array $item, array $testData): void
    {
        $expectedTotalsData = $testData['expected_item_data']['totals'];
        $this->assertEquals($expectedTotalsData['subtotal'], $item['subtotal']);
        $this->assertEquals($expectedTotalsData['cartPrice'], $item['cartPrice']);
        $this->assertEquals($expectedTotalsData['originalPrice'], $item['originalPrice']);
        $this->assertEquals($expectedTotalsData['proposedPrice'], $item['proposedPrice']);
        $this->assertEquals($expectedTotalsData['itemDiscount'], $item['itemDiscount']);
    }

    /**
     * @dataProvider lineItemDiscountsWithQuoteProposedPriceDataProvider
     *
     * @return void
     */
    #[
        ConfigFixture(
            'btob/website_configuration/negotiablequote_active',
            1,
            scopeType: 'store',
            scopeValue: 'default_store'
        ),
        ConfigFixture(
            'btob/website_configuration/company_active',
            1,
            scopeType: 'store',
            scopeValue: 'default_store'
        ),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_ab'),
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
                'super_user_id' => '$customer_ab.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 10,
            ],
            as: 'simple'
        ),
        DataFixture(
            NegotiableQuoteFixture::class,
            [
                'quote' => [
                    'customer_id' => '$customer_ab.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$simple.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ]
                    ]
                ],
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
                NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 20,
            ],
            'negotiable_quote'
        ),
    ]
    public function testLineItemDiscountWithQuoteProposedPrice($testData): void
    {
        /** @var NegotiableQuoteRepositoryInterface $negotiableRepository */
        $negotiableRepository = $this->_objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $quoteItemHashHandler = $this->_objectManager->get(QuoteItemHashHandler::class);

        $customer = FixtureManager::getStorage()->get('customer_ab');
        $simpleProduct = FixtureManager::getStorage()->get('simple');

        $quotes = $negotiableRepository->getListByCustomerId($customer->getId());
        $quote = end($quotes);
        $quoteId = $quote->getId();
        $itemSimple = $quote->getAllItems()[0];

        $postData = [
            'quote_id' => $quoteId,
            'quote' => [
                'items' => [
                    0 => [
                        'id' => $simpleProduct->getId(),
                        'qty' => $testData['item_data']['qty'],
                        'sku' => $simpleProduct->getSku(),
                        'productSku' => $simpleProduct->getSku(),
                        'config' => '',
                        'negotiated_price_type' => $testData['item_data']['negotiated_price_type'],
                        'negotiated_price_value' => $testData['item_data']['negotiated_price_value'],
                        'item_hash' =>  $quoteItemHashHandler->getItemHashToIdItem($itemSimple),
                    ],
                ],
                'addItems' => [],
                'update' => 1,
                'recalcPrice' => 1,
                'proposed' => [
                    'type' => NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
                    'value' => 5.0
                ]
            ],
        ];

        $this->getRequest()->setPostValue($postData)->setMethod('POST');
        $this->dispatch('backend/quotes/quote/update/?isAjax=true');

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $quote = $quoteRepository->get($quoteId);
        /** @var \Magento\Company\Model\Company $company */
        $company = FixtureManager::getStorage()->get('company_b');
        $this->assertQuoteCompany((int)$quote->getId(), $company->getCompanyEmail());
        /** @var  QuoteUpdatesInfo $quoteInfo */
        $quoteInfo = $this->_objectManager->create(QuoteUpdatesInfo::class);
        $updatedData = $quoteInfo->getQuoteUpdatedData($quote, $postData);

        foreach ($updatedData['items'] as $item) {
            $this->assertLineItemDiscountData($item, $testData);
        }
    }

    /**
     * Data provider for testLineItemDiscountWithQuoteProposedPrice
     *
     * @return array
     */
    public static function lineItemDiscountsWithQuoteProposedPriceDataProvider(): array
    {
        $data = [];
        $itemLevelPercentageDiscountCase = [];
        $itemLevelPercentageDiscountCase['item_data'] = [
            'negotiated_price_type' => NegotiableQuoteItemInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
            'negotiated_price_value' => 20.0,
            'qty' => 2
        ];
        $itemLevelPercentageDiscountCase['expected_item_data']['totals'] = [
            'subtotal' => '$5.00',
            'cartPrice' => '$10.00',
            'originalPrice' => '$10.00',
            'proposedPrice' => '$2.50',
            'itemDiscount' => ''
        ];
        $data[] = [$itemLevelPercentageDiscountCase];

        $itemLevelFixedDiscountCase = [];
        $itemLevelFixedDiscountCase['item_data'] = [
            'negotiated_price_type' => NegotiableQuoteItemInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT,
            'negotiated_price_value' => 5.0,
            'qty' => 1
        ];
        $itemLevelFixedDiscountCase['expected_item_data']['totals'] = [
            'subtotal' => '$5.00',
            'cartPrice' => '$10.00',
            'originalPrice' => '$10.00',
            'proposedPrice' => '$5.00',
            'itemDiscount' => ''
        ];
        $data[] = [$itemLevelFixedDiscountCase];

        $itemLevelProposedPriceCase = [];
        $itemLevelProposedPriceCase['item_data'] = [
            'negotiated_price_type' => NegotiableQuoteItemInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL,
            'negotiated_price_value' => 4.0,
            'qty' => 4
        ];
        $itemLevelProposedPriceCase['expected_item_data']['totals'] = [
            'subtotal' => '$5.00',
            'cartPrice' => '$10.00',
            'originalPrice' => '$10.00',
            'proposedPrice' => '$1.25',
            'itemDiscount' => ''

        ];
        $data[] = [$itemLevelProposedPriceCase];
        return $data;
    }
}
