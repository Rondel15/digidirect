<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
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

namespace Magento\PurchaseOrder\Service\V1;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Webapi\Rest\Request;
use Magento\PurchaseOrder\Test\Fixture\PurchaseOrderApprove;
use Magento\PurchaseOrder\Test\Fixture\PurchaseOrderFromQuote;
use Magento\PurchaseOrder\Test\Fixture\PurchaseOrderPlaceOrder;
use Magento\PurchaseOrder\Test\Fixture\PurchaseOrderCompanyConfig as PurchaseOrderCompanyConfigFixture;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Test\Fixture\User;

class CreateSalesOrderFromPurchaseOrderTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';
    private const SERVICE_READ_NAME = 'salesOrderRepositoryV1';
    private const SERVICE_VERSION = 'V1';

    /**
     * @var DataFixtureStorage
     */
    private $fixture;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixture = DataFixtureStorageManager::getStorage();
    }

    /**
     * Test to confirm that the applied taxes are available on the order that was converted from the purchase order.
     */
    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/purchaseorder_enabled', 1),

        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(
            TaxRateFixture::class,
            [
                'code' => '10%shipping',
                'tax_country_id' => 'US',
                'rate' => 10,
            ],
            'taxRate1'
        ),
        DataFixture(
            TaxRateFixture::class,
            [
                'code' => '15percent',
                'tax_country_id' => 'US',
                'rate' => 15,
            ],
            'taxRate2'
        ),
        Config('tax/classes/shipping_tax_class', '$taxRate1.id$'),
        Config('tax/classes/default_product_tax_class', '$taxRate2.id$'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$taxRate2.id$']
            ],
            'taxRule'
        ),

        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer.id$'
            ],
            'company'
        ),
        DataFixture(
            PurchaseOrderCompanyConfigFixture::class,
            [
                'company_id' => '$company.id$',
                'is_purchase_order_enabled' => true
            ]
        ),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$'
            ],
            'quote'
        ),
        DataFixture(ProductFixture::class, [
            'price' => 100,
            'custom_attributes' => [
                'tax_class_id' => '$taxRate2.id$'
            ]
        ], as:'product'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PurchaseOrderFromQuote::class, ['cart_id' => '$quote.id$'], 'purchase_order'),
        DataFixture(
            PurchaseOrderApprove::class,
            [
                'purchase_order_id' => '$purchase_order.entity_id$',
                'customer_id' => '$customer.id$',
            ]
        ),
        DataFixture(
            PurchaseOrderPlaceOrder::class,
            [
                'purchase_order_id' => '$purchase_order.entity_id$',
                'customer_id' => '$customer.id$',
            ],
            'order'
        ),
    ]
    public function testCreateSalesOrderFromPurchaseOrder()
    {
        $purchaseOrder = $this->fixture->get('purchase_order');
        $this->assertNotEmpty($purchaseOrder);
        $order = $this->fixture->get('order');
        $this->assertNotEmpty($order);

        $getServiceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $order['entity_id'],
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        $result = $this->_webApiCall($getServiceInfo, ['id' => $order['entity_id']]);

        $this->assertIsArray($result['extension_attributes']['applied_taxes']);
        $this->assertNotEmpty($result['extension_attributes']['applied_taxes']);
        $this->assertIsArray($result['extension_attributes']['item_applied_taxes']);
        $this->assertNotEmpty($result['extension_attributes']['item_applied_taxes']);
    }
}
