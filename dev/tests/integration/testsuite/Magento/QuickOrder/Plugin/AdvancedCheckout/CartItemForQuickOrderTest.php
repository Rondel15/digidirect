<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuickOrder\Plugin\AdvancedCheckout;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for items in QuickOrder
 */
class CartItemForQuickOrderTest extends AbstractController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test to display prices including tax for quick order
     *
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws StateException
     * @throws LocalizedException
     */
    #[
        DbIsolation(false),
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/quickorder_active', 1, ScopeInterface::SCOPE_WEBSITE),
        Config('tax/defaults/country', 'GB', ScopeInterface::SCOPE_STORE),
        Config('tax/display/type', 2, ScopeInterface::SCOPE_STORE),
        Config('tax/cart_display/price', 2, ScopeInterface::SCOPE_STORE),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, [ 'rate' => 20,'tax_country_id' => 'GB'], as: 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(ProductFixture::class, ['price' => 100], 'product'),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100,
                'custom_attributes' => [
                    'tax_class_id' => '$product_tax_class.classId$'
                ],
            ],
            'product'
        ),
    ]
    public function testDisplayPricesIncludingTaxForQuickOrder()
    {
        $product = $this->fixtures->get('product');

        $requestData = [
            'items' => json_encode([['sku' => $product->getSku(), 'qty' => '1']])
        ];

        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('isAjax', 'true');
        $this->dispatch("quickorder/ajax/search/");
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('120.00', $body);
    }
}
