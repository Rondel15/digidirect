<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyShipping\Block\Company\Profile;

use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyShipping\Test\Fixture\ApplyCompanyShippingMethods;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Http\Context;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\User\Test\Fixture\User;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Session;

/**
 * Test Class for Magento\CompanyShipping\Block\Company\Profile\ShippingMethod
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
        ApplyCompanyShippingMethods::class,
        [
            ApplyCompanyShippingMethods::COMPANY_ID => '$company_b.id$',
            ApplyCompanyShippingMethods::APPLICABLE_SHIPPING_METHOD => 2,
            ApplyCompanyShippingMethods::AVAILABLE_SHIPPING_METHODS => 'flatrate',
            ApplyCompanyShippingMethods::USE_CONFIG_SETTINGS => 0
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
class ShippingMethodTest extends TestCase
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
     * Test shipping methods for company with:
     * B2B applicable shipping methods: Selected Shipping Methods
     * B2B applicable shipping methods are: free shipping
     * Global sales shipping methods free shipping is enabled
     * Global sales shipping methods flatrate shipping is enabled
     *
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @magentoConfigFixture btob/default_b2b_shipping_methods/applicable_shipping_methods 1
     * @magentoConfigFixture btob/default_b2b_shipping_methods/available_shipping_methods freeshipping
     */
    public function testGetShippingMethodsWithSelectedShippingMethodsEnabled()
    {
        $userId = $this->fixtures->get('company_user_ab')->getId();
        $this->session->loginById($userId);
        $this->setCompanyContext('company_a');
        $block = $this->layout->createBlock(ShippingMethod::class);
        $shippingMethods = $block->getShippingMethods();
        $this->assertCount(1, $shippingMethods);
        $this->assertEquals('Free Shipping', $shippingMethods[0]);

        // Switch to company b
        $this->setCompanyContext('company_b');
        $block = $this->layout->createBlock(ShippingMethod::class);
        $compBShippingMethods = $block->getShippingMethods();
        $this->assertCount(1, $compBShippingMethods);
        $this->assertEquals('Flat Rate', $compBShippingMethods[0]);
    }

    /**
     * Test shipping methods for company with:
     * B2B applicable shipping methods: All Shipping Methods
     * Global sales shipping methods free shipping is enabled and sort order 10
     * Global sales shipping methods flatrate shipping is enabled and sort order 20
     *
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store carriers/flatrate/sort_order 20
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @magentoConfigFixture current_store carriers/freeshipping/sort_order 10
     * @magentoConfigFixture btob/default_b2b_shipping_methods/applicable_shipping_methods 0
     */
    public function testGetShippingMethodsWithAllShippingMethodsAndCustomSortOrder()
    {
        $userId = $this->fixtures->get('company_user_ab')->getId();
        $this->session->loginById($userId);
        $this->setCompanyContext('company_a');
        $block = $this->layout->createBlock(ShippingMethod::class);
        $shippingMethods = $block->getShippingMethods();
        $this->assertCount(2, $shippingMethods);
        $this->assertEquals('Free Shipping', $shippingMethods[0]);
        $this->assertEquals('Flat Rate', $shippingMethods[1]);

        // Switch to company b
        $this->setCompanyContext('company_b');
        $block = $this->layout->createBlock(ShippingMethod::class);
        $compBShippingMethods = $block->getShippingMethods();
        $this->assertCount(1, $compBShippingMethods);
        $this->assertEquals('Flat Rate', $compBShippingMethods[0]);
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
