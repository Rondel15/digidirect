<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\HistoryManagementInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote as NegotiableQuoteFixture;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\User\Test\Fixture\User as UserFixture;

class ViewTest extends \Magento\NegotiableQuote\Controller\Adminhtml\AbstractTest
{
    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_GET;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->negotiableQuoteRepository = Bootstrap::getObjectManager()
            ->get(NegotiableQuoteRepositoryInterface::class);
    }

    #[
        AppArea('adminhtml'),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
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
                'super_user_id' => '$customer_ab.id$',

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
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            NegotiableQuoteFixture::class,
            [
                'quote' => [
                    'customer_id' => '$customer_ab.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
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
    /**
     * Verify quote updates if opened by admin:
     *
     * - Status changed
     * - "Decline" button visible
     * - History log updated
     */
    public function testExecute(): void
    {
        $urlTemplate = 'backend/quotes/quote/view';
        $negotiableQuoteId = DataFixtureStorageManager::getStorage()->get('negotiable_quote')->getId();
        $this->assertEquals(
            NegotiableQuoteInterface::STATUS_CREATED,
            $this->negotiableQuoteRepository->getById($negotiableQuoteId)->getStatus()
        );
        $this->getRequest()->setParams(['quote_id' => $negotiableQuoteId]);
        $this->dispatch($urlTemplate);

        $this->assertEquals(
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN,
            $this->negotiableQuoteRepository->getById($negotiableQuoteId)->getStatus()
        );

        /** @var \Magento\Company\Model\Company $company */
        $company = FixtureManager::getStorage()->get('company_b');
        $this->assertQuoteCompany((int)$negotiableQuoteId, $company->getCompanyEmail());

        $historyManagement = Bootstrap::getObjectManager()->get(HistoryManagementInterface::class);
        $quoteHistoryItems = $historyManagement->getQuoteHistory($negotiableQuoteId);
        $this->assertCount(2, $quoteHistoryItems);
        /** @var HistoryInterface $quoteHistory */
        $quoteHistory = array_last($quoteHistoryItems);
        $this->assertEquals(HistoryInterface::STATUS_UPDATED, $quoteHistory->getStatus());

        $html = $this->getResponse()->getBody();
        $this->assertStringContainsString('<button id="quote-view-decline-button"', $html);
    }
}
