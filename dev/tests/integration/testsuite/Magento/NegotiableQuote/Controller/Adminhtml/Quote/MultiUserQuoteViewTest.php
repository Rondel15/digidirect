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

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Catalog\Test\Fixture\Product;
use Magento\Company\Model\CompanyContextInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Company\Test\Fixture\AssignCustomer;
use Magento\SharedCatalog\Test\Fixture\AssignProducts;
use Magento\User\Test\Fixture\User;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\NegotiableQuote\Controller\Adminhtml\AbstractTest;

#[
    AppArea('adminhtml'),
    DataFixture(Customer::class, as: 'customerA'),
    DataFixture(Customer::class, as: 'customerB'),
    DataFixture(Customer::class, as: 'company_user'),
    DataFixture(User::class, as: 'user'),

    DataFixture(
        Product::class,
        [
            'sku' => 'productA',
            'price' => 21
        ],
        'productA'
    ),
    DataFixture(
        Product::class,
        [
            'sku' => 'productB',
            'price' => 25
        ],
        'productB'
    ),
    DataFixture(
        Product::class,
        [
            'sku' => 'productC',
            'price' => 10
        ],
        'productC'
    ),

    DataFixture(SharedCatalog::class, as: 'catalogA'),
    DataFixture(SharedCatalog::class, as: 'catalogB'),

    DataFixture(
        AssignProducts::class,
        [
            'product_ids' => ['$productA.id$', '$productB.id$'],
            'catalog_id' => '$catalogA.id$'
        ]
    ),
    DataFixture(
        AssignProducts::class,
        [
            'product_ids' => ['$productB.id$', '$productC.id$'],
            'catalog_id' => '$catalogB.id$'
        ]
    ),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            CompanyInterface::SUPER_USER_ID => '$customerA.id$',
            CompanyInterface::CUSTOMER_GROUP_ID => '$catalogA.customer_group_id$',
        ],
        'companyA'
    ),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            CompanyInterface::SUPER_USER_ID => '$customerB.id$',
            CompanyInterface::CUSTOMER_GROUP_ID => '$catalogB.customer_group_id$',
        ],
        'companyB'
    ),
    DataFixture(
        AssignCustomer::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$companyA.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user.id$'
        ]
    ),
    DataFixture(
        AssignCustomer::class,
        [
            CompanyCustomerInterface::COMPANY_ID => '$companyB.id$',
            CompanyCustomerInterface::CUSTOMER_ID => '$company_user.id$',
        ]
    )
]
class MultiUserQuoteViewTest extends AbstractTest
{
    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_GET;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var NegotiableQuote
     */
    private $negotiableQuote;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var UrlInterface
     */
    private $backendUrlModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->negotiableQuoteRepository = Bootstrap::getObjectManager()
            ->get(NegotiableQuoteRepositoryInterface::class);
        $this->groupRepository = Bootstrap::getObjectManager()->get(GroupRepositoryInterface::class);
        $this->session = Bootstrap::getObjectManager()->get(Session::class);
        $this->negotiableQuote = Bootstrap::getObjectManager()->create(NegotiableQuote::class);
        $this->httpContext = Bootstrap::getObjectManager()->get(Context::class);
        $this->backendUrlModel = Bootstrap::getObjectManager()->get(
            UrlInterface::class
        );
    }

    /**
     * Verify Quote SharedCatalog For Multi CompanyUser
     *
     * @return void
     */
    public function testVerifyQuoteSharedCatalogForMultiCompanyUser(): void
    {
        $negotiableQuoteId = $this->createNegotiableQuote();
        $urlTemplate = 'backend/quotes/quote/view';
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

        $catalogBId = (int) DataFixtureStorageManager::getStorage()->get('catalogB')->getId();
        $expectedUrl = $this->backendUrlModel->getUrl(
            'shared_catalog/sharedCatalog/edit',
            ['shared_catalog_id' => $catalogBId]
        );
        $expectedCustomerGroup = $this->getExpectedCustomerGroup();
        $expectedString = '<a href="'.$expectedUrl.'">'.$expectedCustomerGroup.'</a>';
        $html = $this->getResponse()->getBody();
        $this->assertStringContainsString($expectedString, $html);
    }

    /**
     * Get Expected Customer Group
     *
     * @return string
     */
    private function getExpectedCustomerGroup(): string
    {
        $customerGroupId = (int) DataFixtureStorageManager::getStorage()->get('catalogB')->getCustomerGroupId();
        $customerGroupCode = $this->groupRepository->getById($customerGroupId)->getCode();
        return $customerGroupCode;
    }

    /**
     * Create negotiable quote
     *
     * @return int
     */
    private function createNegotiableQuote(): int
    {
        $this->setCustomerContext();
        $customerId = (int) DataFixtureStorageManager::getStorage()->get('company_user')->getId();
        $createdQuote = $this->negotiableQuote->apply([
            NegotiableQuoteInterface::QUOTE_NAME => 'Quote #1',
            'quote' => [
                'customer_id' => $customerId,
                CartInterface::KEY_ITEMS => [
                    [
                        CartItemInterface::KEY_SKU => 'productB',
                        CartItemInterface::KEY_QTY => 1
                    ],
                    [
                        CartItemInterface::KEY_SKU => 'productC',
                        CartItemInterface::KEY_QTY => 1
                    ],
                ],
            ],
        ]);
        $this->unsetCustomerContext();
        return (int) $createdQuote->getId();
    }

    /**
     * Set customer context
     *
     * @return void
     */
    private function setCustomerContext(): void
    {
        $companyId = (int) DataFixtureStorageManager::getStorage()->get('companyB')->getId();
        $customerId = (int) DataFixtureStorageManager::getStorage()->get('company_user')->getId();
        $this->session->loginById($customerId);
        $this->httpContext->setValue(CompanyContextInterface::CONTEXT_COMPANY_ID, $companyId, null);
    }

    /**
     * @return void
     */
    private function unsetCustomerContext(): void
    {
        $this->httpContext->unsValue(CompanyContextInterface::CONTEXT_COMPANY_ID);
        $this->session->logout();
    }
}
