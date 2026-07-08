<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\NegotiableQuote\Controller\Adminhtml;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Request\Http;
use Magento\Logging\Model\Event;
use Magento\Logging\Model\ResourceModel\Event\Collection;
use Magento\Logging\Model\ResourceModel\Event\CollectionFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\User\Test\Fixture\User;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoConfigFixture default_store btob/website_configuration/company_active true
 * @magentoConfigFixture default_store btob/website_configuration/negotiablequote_active true
 */
class LoggingTest extends AbstractBackendController
{
    /**
     * @var array
     */
    private $loggingHelper = [
        'quote_save'        => [
            'uri'        => 'backend/quotes/quote/save',
            'action'     => 'save',
            'fullAction' => 'quotes_quote_save'
        ],
        'quote_send'        => [
            'uri'        => 'backend/quotes/quote/send',
            'action'     => 'save',
            'fullAction' => 'quotes_quote_send'
        ],
        'quote_decline'     => [
            'uri'        => 'backend/quotes/quote/decline/quote_id/%s',
            'action'     => 'save',
            'fullAction' => 'quotes_quote_decline'
        ],
        'quote_massDecline' => [
            'uri'        => 'backend/quotes/quote/massDeclineCheck',
            'action'     => 'massUpdate',
            'fullAction' => 'quotes_quote_massDeclineCheck'
        ],
    ];

    /**
     * @var Collection
     */
    private $eventCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableRepository;

    #[
        AppArea('adminhtml'),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_STORE, 'default_store'),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_a.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            ],
            'company_a'
        ),
        DataFixture(Customer::class, as: 'customer_ab'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_ab.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
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
            NegotiableQuote::class,
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
    public function testSaveAction(): void
    {
        $helperData = $this->loggingHelper['quote_save'];
        $customer = FixtureManager::getStorage()->get('customer_ab');

        $quotes  = $this->negotiableRepository->getListByCustomerId($customer->getId());
        $quoteId = end($quotes)->getId();

        // Prepare request data
        $postData = [
            'quote_id' => $quoteId,
            'quote' => [
                'expiration_period' => date('Y-m-d')
            ],
            'dataSend' => json_encode(
                [
                    'quote_id' => $quoteId,
                    'quote'  => [
                        'items' => [
                            0 => [
                                'id' => 1,
                                'qty' => 1,
                                'sku' => 'simple',
                                'productSku' => 'simple',
                                'config' => ''
                            ]
                        ],
                        'addItems' => [],
                        'proposed' => [
                            "type" => 1,
                            "value" => ""
                        ],
                        'recalcPrice' => 1
                    ],
                    'negotiable_quote_update_flag' => true
                ]
            ),
            'negotiable_quote_update_flag' => true,
            'comment' => 'Negotiable Quote Save Draft'
        ];

        // dispatch save action request
        $this->getRequest()->setPostValue($postData)->setMethod(Http::METHOD_POST);
        $this->dispatch($helperData['uri'] . '/?isAjax=true');

        // assert the save action success log
        $this->assert($helperData, Event::RESULT_SUCCESS);
    }

    /**
     * Assert the given event log, or last event log
     *
     * @param array $assertData
     * @param string $status 'success' | 'failure'
     * @param $event
     *
     */
    private function assert($assertData, $status, $event = null)
    {
        // Get the recent event object to assert
        $event = $event ?? $this->getRecentEvent();

        $this->assertEquals($assertData['action'], $event->getAction());
        $this->assertEquals($assertData['fullAction'], $event->getFullaction());
        $this->assertEquals($status, $event->getStatus());
    }

    /**
     * Returns latest logging entry
     *
     * @return Event
     */
    private function getRecentEvent(): Event
    {
        $eventCollection = $this->eventCollectionFactory->create();
        $eventCollection->setOrder('log_id', Collection::SORT_ORDER_DESC);

        return $eventCollection->getFirstItem();
    }

    #[
        AppArea('adminhtml'),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_STORE, 'default_store'),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_a.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            ],
            'company_a'
        ),
        DataFixture(Customer::class, as: 'customer_ab'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_ab.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
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
            NegotiableQuote::class,
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
    public function testDeclineAction(): void
    {
        $helperData = $this->loggingHelper['quote_decline'];

        /** @var NegotiableQuoteInterface $negotiableQuote */
        $negotiableQuote = FixtureManager::getStorage()->get('negotiable_quote');
        $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
        $this->negotiableRepository->save($negotiableQuote);
        $quoteId = $negotiableQuote->getId();

        $uri = sprintf($helperData['uri'], $quoteId);

        // Prepare request data
        $postData = [
            'quote_message' => 'Negotiable Quote Decline'
        ];

        // dispatch decline action request
        $this->getRequest()->setPostValue($postData)->setMethod(Http::METHOD_POST);
        $this->dispatch($uri);

        // assert the decline action success log
        $this->assert($helperData, Event::RESULT_SUCCESS);
    }

    #[
        AppArea('adminhtml'),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_STORE, 'default_store'),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_a.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            ],
            'company_a'
        ),
        DataFixture(Customer::class, as: 'customer_ab'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_ab.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
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
            NegotiableQuote::class,
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
    public function testSendAction(): void
    {
        $helperData = $this->loggingHelper['quote_send'];
        $customer = FixtureManager::getStorage()->get('customer_ab');

        $quotes = $this->negotiableRepository->getListByCustomerId($customer->getId());
        $quoteId = end($quotes)->getId();

        // Prepare request data
        $postData = [
            'quote_id' => $quoteId,
            'quote' => [
                'expiration_period' => date('Y-m-d')
            ],
            'dataSend' => json_encode(
                [
                    'quote_id' => $quoteId,
                    'quote' => [
                        'items' => [
                            0 => [
                                'id' => 1,
                                'qty' => 1,
                                'sku' => 'simple',
                                'productSku' => 'simple',
                                'config' => ''
                            ]
                        ],
                        'addItems' => [],
                        'proposed' => [
                            "type"  => 1,
                            "value" => 10
                        ],
                        'recalcPrice' => 1
                    ],
                    'negotiable_quote_update_flag' => true
                ]
            ),
            'negotiable_quote_update_flag' => true,
            'comment' => 'Negotiable Quote Send'
        ];

        // dispatch send action request
        $this->getRequest()->setPostValue($postData)->setMethod(Http::METHOD_POST);
        $this->dispatch($helperData['uri'] . '/?isAjax=true');

        // assert the send action success log
        $this->assert($helperData, Event::RESULT_SUCCESS);
    }

    #[
        AppArea('adminhtml'),
        Config('btob/website_configuration/negotiablequote_active', '1', ScopeInterface::SCOPE_STORE, 'default_store'),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_a.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
            ],
            'company_a'
        ),
        DataFixture(Customer::class, as: 'customer_ab'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SUPER_USER_ID => '$customer_ab.id$',
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$user.id$',
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
            NegotiableQuote::class,
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
                NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN
            ],
            'negotiable_quote'
        ),
    ]
    public function testMassDeclineAction(): void
    {
        $helperData = $this->loggingHelper['quote_massDecline'];
        $customer = FixtureManager::getStorage()->get('customer_ab');

        $quotes = $this->negotiableRepository->getListByCustomerId($customer->getId());
        $quoteId = end($quotes)->getId();

        // Prepare request data
        $postData = [
            'selected'  => [$quoteId],
            'namespace' => 'negotiable_quote_grid'
        ];

        // dispatch Mass Decline action request
        $this->getRequest()->setPostValue($postData)->setMethod(Http::METHOD_POST);
        $this->dispatch($helperData['uri']);

        // assert the mass decline action success log
        $this->assert($helperData, Event::RESULT_SUCCESS);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->eventCollectionFactory = $this->_objectManager->get(CollectionFactory::class);
        $this->customerRepository     = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->negotiableRepository   = $this->_objectManager->get(NegotiableQuoteRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->eventCollectionFactory = null;
        $this->customerRepository     = null;
        $this->negotiableRepository   = null;
    }
}
