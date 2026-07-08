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

use Magento\Catalog\Test\Fixture\Product;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResourceModel;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\User\Test\Fixture\User;

#[
    AppArea('frontend'),
    AppIsolation(true),
    DataFixture(Customer::class, as: 'admin1'),
    DataFixture(Customer::class, as: 'admin2'),
    DataFixture(User::class, as: 'sales_rep'),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$admin1.id$',
        ],
        'company1'
    ),
    DataFixture(
        Company::class,
        [
            CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
            CompanyInterface::SUPER_USER_ID => '$admin2.id$',
        ],
        'company2'
    ),
    DataFixture(CustomerCart::class, ['customer_id' => '$admin1.id$'], 'quote'),
    DataFixture(Product::class, [], 'product'),
    DataFixture(
        AddProductToCart::class,
        ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
        'item'
    ),
    DataFixture(
        NegotiableQuote::class,
        [
            NegotiableQuoteInterface::QUOTE_NAME => 'Quote #11',
            'quote' => [
                'customer_id' => '$admin1.id$',
                CartInterface::KEY_ITEMS => [
                    [
                        CartItemInterface::KEY_SKU => '$product.sku$',
                        CartItemInterface::KEY_QTY => 1
                    ],
                ],
            ],
        ],
        'quote'
    ),
]
class PermissionDuplicateTest extends AbstractController
{
    /** @var string  */
    private const URI = 'negotiable_quote_duplicate/quote/duplicate/';

    /** @var string  */
    private const XML_CONFIG_PATH_COMPANY = 'btob/website_configuration/company_active';

    /** @var string  */
    private const XML_CONFIG_PATH_QUOTE = 'btob/website_configuration/negotiablequote_active';

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var NegotiableQuoteResourceModel
     */
    private $negotiableQuoteResourceModel;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $scopeConfig = $this->_objectManager->get(MutableScopeConfigInterface::class);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_QUOTE, '1', ScopeInterface::SCOPE_WEBSITE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_QUOTE, '1', ScopeInterface::SCOPE_STORE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_COMPANY, '1', ScopeInterface::SCOPE_WEBSITE);
        $scopeConfig->setValue(self::XML_CONFIG_PATH_COMPANY, '1', ScopeInterface::SCOPE_STORE);

        $this->customerSession = $this->_objectManager->get(CustomerSession::class);
        $this->negotiableQuoteResourceModel = $this->_objectManager->get(NegotiableQuoteResourceModel::class);
        $this->negotiableQuoteRepository = $this->_objectManager->get(NegotiableQuoteRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->logout();
        parent::tearDown();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testNotLoggedInUser(): void
    {
        /** @var NegotiableQuoteInterface $quote */
        $quote = FixtureManager::getStorage()->get('quote');
        $quoteId = (int) $quote->getId();

        $this->sendRequest($quoteId);
        $this->assertRedirect($this->stringContains('customer/account/login'));
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testDuplicate(): void
    {
        $customerId = (int)FixtureManager::getStorage()->get('admin1')->getId();
        $this->customerSession->loginById($customerId);
        /** @var NegotiableQuoteInterface $quote */
        $quote = FixtureManager::getStorage()->get('quote');
        $quoteId = (int) $quote->getId();
        $quoteName = $quote->getQuoteName();

        $this->sendRequest($quoteId);
        $this->assertRedirect($this->stringContains('negotiable_quote/quote/view'));
        $this->assertSessionMessages(
            $this->equalTo(["The Quote &quot;" . $quoteName . "&quot; has been successfully copied."]),
            MessageInterface::TYPE_SUCCESS
        );
        $redirectQuoteId = $this->getQuoteIdFromResponse();
        $this->assertNotEquals($quoteId, $redirectQuoteId);
        $lastCreatedQuote = $this->getLastCreatedQuoteCreated($customerId);
        $this->assertEquals($lastCreatedQuote->getId(), $redirectQuoteId);
        $this->assertQuoteStatusInDatabase(
            $lastCreatedQuote,
            NegotiableQuoteInterface::STATUS_DRAFT_BY_CUSTOMER
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testCannotDuplicateForPendingStatus(): void
    {
        $customerId = (int)FixtureManager::getStorage()->get('admin1')->getId();
        $this->customerSession->loginById($customerId);
        /** @var NegotiableQuoteInterface $quote */
        $quote = FixtureManager::getStorage()->get('quote');
        $quoteId = (int) $quote->getId();

        $this->updateQuoteStatus($quoteId, NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);

        $this->sendRequest($quoteId);
        $this->assertRedirect($this->stringContains('negotiable_quote/quote/view'));
        $this->assertSessionMessages(
            $this->equalTo(["&quot;Create Copy&quot; can not be performed when a quote is Pending."]),
            MessageInterface::TYPE_ERROR
        );
        $redirectQuoteId = $this->getQuoteIdFromResponse();
        $this->assertEquals($quoteId, $redirectQuoteId);
        $lastCreatedQuote = $this->getLastCreatedQuoteCreated($customerId);
        $this->assertEquals($lastCreatedQuote->getId(), $redirectQuoteId);
        $this->assertQuoteStatusInDatabase(
            $lastCreatedQuote,
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN
        );
    }

    #[
        AppArea('frontend'),
        AppIsolation(true),
        DataFixture(Customer::class, as: 'admin1'),
        DataFixture(Customer::class, as: 'admin2'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin1.id$',
            ],
            'company1'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin2.id$',
            ],
            'company2'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
                CompanyCustomerInterface::COMPANY_ID => '$company2.id$',
            ],
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
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
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'quote'
        ),
        DataFixture(
            Role::class,
            [
                'role_name' => 'Role %uniqid%',
                'company_id' => '$company2.id$',
                'permissions' => [
                    [
                        'resource_id' => 'Magento_Company::index',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::all',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::view_quotes',
                        'permission' => 'allow'
                    ],
                ],
            ],
            'customer_role'
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer.id$',
                'company_id' => '$company2.id$',
                'role_ids' => ['$customer_role.id$'],
            ]
        ),
    ]
    /**
     * @return void
     * @throws LocalizedException
     */
    public function testUserWithoutPermission(): void
    {
        $customerId = (int)FixtureManager::getStorage()->get('customer')->getId();
        $this->customerSession->loginById($customerId);
        /** @var NegotiableQuoteInterface $quote */
        $quote = FixtureManager::getStorage()->get('quote');
        $quoteId = (int) $quote->getId();

        $this->sendRequest($quoteId);
        $this->assertRedirect($this->stringContains('company/accessdenied'));
    }

    /**
     * Assert Quote Status in database is that of $expectedStatus
     *
     * @param Quote $quote
     * @param string $expectedStatus
     */
    private function assertQuoteStatusInDatabase(Quote $quote, string $expectedStatus): void
    {
        $actualStatus = $quote->getExtensionAttributes()->getNegotiableQuote()->getStatus();
        $this->assertEquals(
            $expectedStatus,
            $actualStatus,
            'Quote status is not ' . $expectedStatus . ' in database'
        );
    }

    /**
     * Get last created Quote for customer
     *
     * @param int $customerId
     * @return Quote|false
     */
    private function getLastCreatedQuoteCreated(int $customerId): Quote|false
    {
        /** @var Quote[] $quotes */
        $quotes = $this->negotiableQuoteRepository->getListByCustomerId($customerId);

        return end($quotes);
    }

    /**
     * Send request to controller
     *
     * @param int $quoteId
     * @return void
     */
    private function sendRequest(int $quoteId): void
    {
        $this->getRequest()
            ->setMethod(Http::METHOD_GET)
            ->setParams(['quote_id' => $quoteId]);

        $this->dispatch(self::URI);
    }

    /**
     * Update Quote status
     *
     * @param int $quoteId
     * @param string $status
     * @return void
     * @throws LocalizedException
     */
    private function updateQuoteStatus(int $quoteId, string $status): void
    {
        $this->negotiableQuoteResourceModel->getConnection()->update(
            $this->negotiableQuoteResourceModel->getMainTable(),
            ['status' => $status],
            ['quote_id' => $quoteId],
        );
    }

    /**
     * Extract quote ID from the response redirect location
     *
     * @return int|false
     */
    private function getQuoteIdFromResponse(): int|false
    {
        $redirectUri = $this->getResponse()->getHeader('Location')->getFieldValue();
        if (preg_match('/quote_id\/(?<quote_id>\d+)/', (string) $redirectUri, $matches)) {
            return (int) $matches['quote_id'] ?? false;
        }
        return false;
    }
}
