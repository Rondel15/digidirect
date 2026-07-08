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

namespace Magento\QuoteNegotiableDuplicate\Controller\Quote;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\Framework\App\Request\Http;
use Magento\User\Test\Fixture\User;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\AppArea;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResourceModel;
use Magento\Quote\Model\Quote;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Bundle\Model\Product\Price;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\GroupedProduct\Test\Fixture\AddProductToCart as AddGroupedToCartFixture;
use Magento\Framework\Message\MessageInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Test for duplicating negotiable quote with outofstock items
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockDuplicateTest extends AbstractController
{
    private const URI = 'negotiable_quote_duplicate/quote/duplicate';
    private const XML_CONFIG_PATH_COMPANY = 'btob/website_configuration/company_active';
    private const XML_CONFIG_PATH_QUOTE = 'btob/website_configuration/negotiablequote_active';
    private const ITEM_SKIPPED_MESSAGE = 'Some items were skipped during duplication due to errors.';

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var NegotiableQuoteResourceModel
     */
    private $negotiableQuoteResourceModel;

    /**
     * @var NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var CartRepositoryInterface;
     */
    private $quoteRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var array
     */
    private $skippedItems = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $scopeConfig = $this->_objectManager->get(MutableScopeConfigInterface::class);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_COMPANY, '1', ScopeInterface::SCOPE_WEBSITE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_COMPANY, '1', ScopeInterface::SCOPE_STORE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_QUOTE, '1', ScopeInterface::SCOPE_WEBSITE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_QUOTE, '1', ScopeInterface::SCOPE_STORE);
        $this->customerSession = $this->_objectManager->get(CustomerSession::class);
        $this->negotiableQuoteResourceModel = $this->_objectManager->get(NegotiableQuoteResourceModel::class);
        $this->negotiableQuoteRepository = $this->_objectManager->get(NegotiableQuoteRepositoryInterface::class);
        $this->negotiableQuoteManagement = $this->_objectManager->get(NegotiableQuoteManagementInterface::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->stockRegistry = $this->_objectManager->get(StockRegistryInterface::class);
        $this->skippedItems = [];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->logout();
        parent::tearDown();
    }

    #[
        AppArea('frontend'),
        AppIsolation(true),
        DataFixture(Customer::class, as: 'admin1'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin1.id$',
            ],
            'company1'
        ),
        DataFixture(ProductFixture::class, ['price' => 30], 'product'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product1'),
        DataFixture(CustomerCart::class, ['customer_id' => '$admin1.id$'], 'quote'),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #11',
                'quote' => [
                    'customer_id' => '$admin1.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 3
                        ],
                        [
                            CartItemInterface::KEY_SKU => '$product1.sku$',
                            CartItemInterface::KEY_QTY => 2
                        ],
                    ],
                ],
            ],
            'quote'
        ),
    ]
    /**
     * Test storefront negotiable quote owner can duplicate the quote with outofstock item
     *
     * Given a company user having a negotiatiable quote which contains outofstock item
     * Then the company user is able to duplicate the negotiatiable quote
     * the out of stock item is skipped
     *
     * @return void
     */
    public function testQuoteDuplicationByStorefrontUserWithOutOfStockItem():void
    {
        $customerId = (int)FixtureManager::getStorage()->get('admin1')->getId();
        $productId = (int)FixtureManager::getStorage()->get('product')->getId();
        $this->setProductStock($productId, 0);
        $this->duplicateQuoteAndVerify($customerId);
    }

    #[
        AppArea('frontend'),
        AppIsolation(true),
        DataFixture(Customer::class, as: 'admin1'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin1.id$',
            ],
            'company1'
        ),
        DataFixture(ProductFixture::class, ['price' => 30], 'product'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product1'),
        DataFixture(CustomerCart::class, ['customer_id' => '$admin1.id$'], 'quote'),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #11',
                'quote' => [
                    'customer_id' => '$admin1.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 3
                        ],
                        [
                            CartItemInterface::KEY_SKU => '$product1.sku$',
                            CartItemInterface::KEY_QTY => 2
                        ],
                    ],
                ],
            ],
            'quote'
        ),
    ]
    /**
     * Test storefront negotiable quote owner can duplicate the quote with items error
     *
     * Given a company user having a negotiatiable quote which contains items error about insufficient stock
     * Then the company user is able to duplicate the negotiatiable quote
     * the insufficient stock item is skipped
     *
     * @return void
     */
    public function testQuoteDuplicationByStorefrontUserWithInsufficientStockItem():void
    {
        $customerId = (int)FixtureManager::getStorage()->get('admin1')->getId();
        $productId = (int)FixtureManager::getStorage()->get('product')->getId();
        $this->setProductStock($productId, 1);
        $this->duplicateQuoteAndVerify($customerId);
    }

    #[
        AppArea('frontend'),
        AppIsolation(true),
        DataFixture(Customer::class, as: 'admin2'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin2.id$',
            ],
            'company2'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$admin2.id$'], 'quote'),
        DataFixture(ProductFixture::class, ['price' => 10], as: 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], as: 'p2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'cp1'
        ),
        DataFixture(ProductFixture::class, ['price' => 20], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product2'),
        DataFixture(ProductFixture::class, ['price' => 20], 'product3'),
        DataFixture(ProductFixture::class, ['price' => 10], 'product4'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$']
            ],
            'bundle_product_1'
        ),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => 'grouped-product-allowed',
                'product_links' => [
                    ['sku' => '$product3.sku$', 'qty' => 3],
                    ['sku' => '$product4.sku$', 'qty' => 2],
                ]
            ],
            'grouped_product'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote #11',
                'quote' => [
                    'customer_id' => '$admin2.id$',
                    CartInterface::KEY_ITEMS => [],
                ],
            ],
            'quote'
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$cp1.id$',
                'child_product_id' => '$p2.id$',
                'qty' => 1
            ]
        ),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']]
            ]
        ),
        DataFixture(
            AddGroupedToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$grouped_product.id$',
                'child_products' => ['$product3.id$', '$product4.id$']
            ]
        ),
    ]
    /**
     * Test storefront negotiable quote owner can duplicate the quote with composite products with items error
     *
     * Given a company user having a negotiatiable quote which contains composite products with outofstock item
     * & insufficient stock
     * Then the company user is able to duplicate the negotiatiable quote
     * the out of stock items and insufficient stock items are skipped
     *
     * @return void
     */
    public function testQuoteDuplicationByStorefrontUserWithCompositeProductWithItemsError():void
    {
        $customerId = (int)FixtureManager::getStorage()->get('admin2')->getId();
        $productId = (int) FixtureManager::getStorage()->get('p2')->getId();
        $parentId = (int) FixtureManager::getStorage()->get('cp1')->getId();
        $product3Id = (int) FixtureManager::getStorage()->get('product3')->getId();
        $this->setProductStock($productId, 0);
        $this->skippedItems[] = $parentId;
        $this->setProductStock($product3Id, 2);
        $this->duplicateQuoteAndVerify($customerId);
    }

   /**
    * Set product stock
    *
    * @param int $productId
    * @param int $qty
    * @return void
    */
    private function setProductStock(int $productId, $qty): void
    {
        try {
            $productStock = $this->stockRegistry->getStockItem($productId);
            $productStock->setQty($qty);
            if ($qty == 0) {
                $productStock->setIsInStock(false);
            }
            $productStock->save();
            $this->skippedItems[] = $productId;
        } catch (\Exception $e) {
            $this->markTestSkipped('Product stock is not available');
        }
    }

    /**
     * duplicate the negotiatiable quote and verify the data
     *
     * @param int $customerId
     * @return void
     */
    private function duplicateQuoteAndVerify($customerId): void
    {
        $this->customerSession->loginById($customerId);
        $quoteId = (int)FixtureManager::getStorage()->get('quote')->getId();
        $this->sendRequest($quoteId);
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $noticeMessage = self::ITEM_SKIPPED_MESSAGE;
        $this->assertSessionMessages(
            $this->equalTo([(string)__($noticeMessage)]),
            MessageInterface::TYPE_NOTICE
        );
        $this->assertSessionMessages(
            $this->equalTo([(string)__(htmlspecialchars(
                sprintf('The Quote "%s" has been successfully copied.', "Quote #11"),
                ENT_QUOTES
            ))]),
            MessageInterface::TYPE_SUCCESS
        );
        $locationUri = $this->getResponse()->getHeaders()->get('location')->getUri();
        $duplicateQuoteId = null;
        if (preg_match('/quote_id\/(\d+)/', $locationUri, $matches)) {
            $duplicateQuoteId = $matches[1] ?? null;
        }
        $this->assertNotNull($duplicateQuoteId);
        $this->assertDuplicateQuoteInDatabase($quoteId, (int) $duplicateQuoteId);
    }

    /**
     * Assert that the quote was duplicated in the database
     *
     * @param int $quoteId
     * @param int $duplicateQuoteId
     * @return void
     */
    private function assertDuplicateQuoteInDatabase(int $quoteId, int $duplicateQuoteId): void
    {
        $negotiableQuote = $this->negotiableQuoteRepository->getById($quoteId);
        $duplicateQuote = $this->negotiableQuoteRepository->getById($duplicateQuoteId);
        $expectedName = $negotiableQuote->getQuoteName()." (copy)";
        $this->assertEquals($expectedName, $duplicateQuote->getQuoteName());
        $this->assertEquals(NegotiableQuoteInterface::STATUS_DRAFT_BY_CUSTOMER, $duplicateQuote->getStatus());
        $this->assertAllItemsAreDuplicated(
            $this->getQuote($quoteId),
            $this->getQuote($duplicateQuoteId)
        );
    }

    /**
     * Assert that all items from the source quote are duplicated in the destination quote
     *
     * @param Quote $sourceQuote
     * @param Quote $destinationQuote
     * @return void
     */
    private function assertAllItemsAreDuplicated(Quote $sourceQuote, Quote $destinationQuote): void
    {
        $sourceQuoteItems = $sourceQuote->getAllItems();
       
        $destinationQuoteItems = $destinationQuote->getAllItems();
        $skippedItemsCount = count($this->skippedItems);
        $this->assertCount((count($sourceQuoteItems) - $skippedItemsCount), $destinationQuoteItems);
        foreach ($sourceQuoteItems as $sourceQuoteItem) {
            $this->assertErrorItemIsSkipped($sourceQuoteItem, $destinationQuoteItems);
        }
    }

    /**
     * Assert that the item is skipped
     *
     * @param Quote\Item $sourceQuoteItem
     * @param Quote\Item[] $destinationQuoteItems
     * @return void
     */
    private function assertErrorItemIsSkipped(Quote\Item $sourceQuoteItem, array $destinationQuoteItems): void
    {
        if (in_array($sourceQuoteItem->getProductId(), $this->skippedItems)) {
            return;
        }
        $sourceQuoteItemSku = $sourceQuoteItem->getSku();
        $destinationQuoteItem = $this->findItemBySku($sourceQuoteItemSku, $destinationQuoteItems);
        $this->assertNotNull($destinationQuoteItem);
        $this->assertEquals($sourceQuoteItem->getQty(), $destinationQuoteItem->getQty());
    }

    /**
     * Find item by sku
     *
     * @param string $sku
     * @param Quote\Item[] $items
     * @return Quote\Item|null
     */
    private function findItemBySku(string $sku, array $items): ?Quote\Item
    {
        foreach ($items as $item) {
            if ($item->getSku() === $sku) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Get the quote by id
     *
     * @param int $quoteId
     * @return Quote
     */
    private function getQuote($quoteId): Quote
    {
        return $this->quoteRepository->get($quoteId);
    }

    /**
     * Send request to controller
     *
     * @param int $quoteId
     * @return void
     */
    private function sendRequest($quoteId): void
    {
        $this->getRequest()
            ->setMethod(Http::METHOD_GET);
        $this->dispatch(self::URI.'/quote_id/'.$quoteId);
    }
}
