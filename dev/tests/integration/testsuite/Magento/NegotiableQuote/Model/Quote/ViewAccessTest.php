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

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Company\Model\CompanyContextInterface;
use Magento\Company\Test\Fixture\AssignCompany as AssignCompanyFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\Http\Context;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote as NegotiableQuoteFixture;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Test\Fixture\User as UserFixture;
use PHPUnit\Framework\TestCase;

#[
    AppArea('frontend'),
    AppIsolation(true),
    DbIsolation(false),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(CustomerFixture::class, as: 'customer_a'),
    DataFixture(CustomerFixture::class, as: 'customer_b'),
    DataFixture(CustomerFixture::class, as: 'customer_ab'),
    DataFixture(UserFixture::class, as: 'user'),
    DataFixture(
        CompanyFixture::class,
        [
            'sales_representative_id' => '$user.id$',
            'super_user_id' => '$customer_a.id$',

        ],
        'company_a'
    ),
    DataFixture(
        CompanyFixture::class,
        [
            'sales_representative_id' => '$user.id$',
            'super_user_id' => '$customer_b.id$'
        ],
        'company_b'
    ),
    DataFixture(
        AssignCompanyFixture::class,
        [
            'company_id' => '$company_a.id$',
            'customer_id' => '$customer_ab.id$'
        ]
    ),
    DataFixture(
        AssignCompanyFixture::class,
        [
            'company_id' => '$company_b.id$',
            'customer_id' => '$customer_ab.id$',
        ]
    ),
    DataFixture(
        NegotiableQuoteFixture::class,
        [
            'quote' => [
                'customer_id' => '$customer_ab.id$',
                CartInterface::KEY_ITEMS => [
                    [
                        CartItemInterface::KEY_SKU => '$product.sku$',
                        CartItemInterface::KEY_QTY => 100,
                    ]
                ]
            ],
            NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_CREATED,
            NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE =>
                NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT,
            NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE => 5,
        ],
        'negotiable_quote'
    )
]
class ViewAccessTest extends TestCase
{
    /**
     * @var FixtureManager
     */
    private $fixture;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ViewAccessInterface
     */
    private $viewAccess;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixture = FixtureManager::getStorage();
        $this->httpContext = Bootstrap::getObjectManager()->get(Context::class);
        $this->session = Bootstrap::getObjectManager()->get(Session::class);
        $this->quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $this->viewAccess = Bootstrap::getObjectManager()->get(ViewAccessInterface::class);
    }

    public function testMultiCompanyCustomer()
    {
        $customerId = (int)$this->fixture->get('customer_ab')->getId();
        $quoteId = (int)$this->fixture->get('negotiable_quote')->getQuoteId();
        $companyB_Id = (int)$this->fixture->get('company_b')->getId();

        //customer_ab login
        $this->session->loginById($customerId);

        //Access quote in default company login
        $quote = $this->quoteRepository->get($quoteId);
        $this->assertTrue($this->viewAccess->canViewQuote($quote));

        //switch to company_b
        $this->setHttpContext($companyB_Id);

        //Access denied after switching to company_b
        $quote = $this->quoteRepository->get($quoteId);
        $this->assertFalse($this->viewAccess->canViewQuote($quote));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->session->logout();
    }

    /**
     * Switch company context
     *
     * @param int $value
     * @return void
     */
    private function setHttpContext(int $value): void
    {
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, $value, 0);
    }
}
