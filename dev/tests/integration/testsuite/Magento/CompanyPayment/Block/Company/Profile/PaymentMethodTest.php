<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CompanyPayment\Block\Company\Profile;

use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyPayment\Test\Fixture\ApplyCompanyPaymentMethods;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Http\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Test\Fixture\User;
use PHPUnit\Framework\TestCase;

/**
 * Test Class for Magento\CompanyPayment\Block\Company\Profile\PaymentMethod
 *
 * @magentoAppIsolation enabled
 */
#[
    AppArea('frontend'),
    DataFixture(Customer::class, as: 'company_user_ab'),
    DataFixture(Customer::class, as: 'company_user_b'),
    DataFixture(User::class, as: 'sales_rep_a'),
    DataFixture(User::class, as: 'sales_rep_b'),
    DataFixture(
        Company::class,
        [
            'sales_representative_id' => '$sales_rep_a.id$',
            'super_user_id' => '$company_user_ab.id$'
        ],
        'company_a'
    ),
    DataFixture(
        Company::class,
        [
            'sales_representative_id' => '$sales_rep_b.id$',
            'super_user_id' => '$company_user_b.id$'
        ],
        'company_b'
    ),
    DataFixture(
        ApplyCompanyPaymentMethods::class,
        [
            ApplyCompanyPaymentMethods::COMPANY_ID => '$company_b.id$',
            ApplyCompanyPaymentMethods::APPLICABLE_PAYMENT_METHOD => 2,
            ApplyCompanyPaymentMethods::AVAILABLE_PAYMENT_METHODS => 'checkmo',
            ApplyCompanyPaymentMethods::USE_CONFIG_SETTINGS => 0
        ]
    ),
    DataFixture(
        AssignCompany::class,
        [
            'company_id' => '$company_b.id$',
            'job_title' => 'job company b',
            'customer_id' => '$company_user_ab.id$',
        ]
    ),
]
class PaymentMethodTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\TestFramework\Fixture\DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = FixtureManager::getStorage();
        $this->layout = $this->objectManager->get(\Magento\Framework\View\LayoutInterface::class);
        $this->session = $this->objectManager->get(Session::class);
    }

    /**
     * Test payment methods for company with: B2B Payment Methods
     * B2B applicable payment methods: Selected Payment Methods
     * B2B payment methods are: Check / Money order, Purchase Order
     * Global sales payment methods check / money order is enabled
     * Global sales payment methods cashondelivery is enabled
     * Global sales payment methods purchase order is enabled
     *
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @magentoConfigFixture current_store payment/cashondelivery/active 1
     * @magentoConfigFixture current_store payment/purchaseorder/active 1
     * @magentoConfigFixture btob/default_b2b_payment_methods/applicable_payment_methods 1
     * @magentoConfigFixture btob/default_b2b_payment_methods/available_payment_methods checkmo,purchaseorder
     */
    public function testCompanyWithB2bPaymentMethodsEqualToConfigDefaultB2bSelectedPaymentMethods()
    {
        $userId = $this->fixtures->get('company_user_ab')->getId();
        $this->session->loginById($userId);
        $this->setCompanyContext('company_a');
        $block = $this->layout->createBlock(PaymentMethod::class);

        $expected = [
            'Check / Money order',
            'Purchase Order'
        ];

        $paymentMethods = $block->getPaymentMethods();
        $this->assertCount(2, $paymentMethods);
        $this->assertEquals($expected, $paymentMethods);

        // Switch to company b
        $this->setCompanyContext('company_b');
        $block = $this->layout->createBlock(PaymentMethod::class);
        $compBPaymentMethods = $block->getPaymentMethods();
        $this->assertCount(1, $compBPaymentMethods);
        $this->assertEquals(['Check / Money order'], $compBPaymentMethods);
    }

    /**
     * Test payment methods for company with: B2B Payment Methods
     * B2B applicable payment methods: All Payment Methods
     * B2B payment methods are: Check / Money order, Purchase Order
     * Global sales payment methods check / money order is enabled
     * Global sales payment methods cashondelivery is enabled
     * Global sales payment methods purchase order is enabled
     *
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @magentoConfigFixture current_store payment/cashondelivery/active 1
     * @magentoConfigFixture current_store payment/purchaseorder/active 1
     * @magentoConfigFixture current_store payment/banktransfer/active 1
     * @magentoConfigFixture current_store payment/free/active 0
     * @magentoConfigFixture current_store payment/paypal_billing_agreement/active 0
     * @magentoConfigFixture current_store payment/companycredit/active 0
     * @magentoConfigFixture current_store payment/fake/active 0
     * @magentoConfigFixture current_store payment/fake_vault/active 0
     * @magentoConfigFixture btob/default_b2b_payment_methods/applicable_payment_methods 0
     */
    public function testCompanyWithB2bPaymentMethodsEqualToConfigDefaultB2bAllPaymentMethods()
    {
        $userId = $this->fixtures->get('company_user_ab')->getId();
        $this->session->loginById($userId);
        $this->setCompanyContext('company_a');
        $block = $this->layout->createBlock(PaymentMethod::class);

        $expected = [
            'Bank Transfer Payment',
            'Cash On Delivery',
            'Check / Money order',
            'Purchase Order'
        ];

        $paymentMethods = $block->getPaymentMethods();
        $this->assertCount(4, $paymentMethods);
        $this->assertEquals($expected, $paymentMethods);

        // Switch to company b
        $this->setCompanyContext('company_b');
        $block = $this->layout->createBlock(PaymentMethod::class);
        $compBPaymentMethods = $block->getPaymentMethods();
        $this->assertCount(1, $compBPaymentMethods);
        $this->assertEquals(['Check / Money order'], $compBPaymentMethods);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->session->logout();
    }

    /**
     * Set company context.
     *
     * @param string|null $companyFixtureId
     * @return void
     */
    private function setCompanyContext(?string $companyFixtureId): void
    {
        $httpContext = $this->objectManager->get(Context::class);
        if ($companyFixtureId) {
            $companyId = (int) $this->fixtures->get($companyFixtureId)->getId();
            $httpContext->setValue('company_id', $companyId, null);
        } else {
            $httpContext->unsValue('company_id');
        }
    }
}
