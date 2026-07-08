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

namespace Magento\NegotiableQuoteRequisitionList\Controller\Quote;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\Company as CompanyFixture;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\RequisitionList\Api\Data\RequisitionListInterfaceFactory;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\User\Test\Fixture\User;

#[
    AppArea('frontend'),
]
class ItemMoveToRequisitionListTest extends AbstractController
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var RequisitionListInterfaceFactory
     */
    private $requisitionListFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Setup method
     */
    protected function setUp(): void
    {
        $this->customerSession = ObjectManager::getInstance()->get(Session::class);
        $this->quoteRepository = ObjectManager::getInstance()->get(CartRepositoryInterface::class);
        $this->requisitionListRepository = ObjectManager::getInstance()->get(RequisitionListRepositoryInterface::class);
        $this->requisitionListFactory = ObjectManager::getInstance()->get(RequisitionListInterfaceFactory::class);
        $this->resource = ObjectManager::getInstance()->get(ResourceConnection::class);

        parent::setUp();
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1, ScopeInterface::SCOPE_WEBSITE),
        ConfigFixture('btob/website_configuration/negotiablequote_active', 1, ScopeInterface::SCOPE_WEBSITE),
        ConfigFixture('btob/website_configuration/requisition_list_active', 1, ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Customer::class, as: 'admin_customer_A'),
        DataFixture(Customer::class, as: 'admin_customer_B'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer_A.id$'
            ],
            'company_A'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer_B.id$'
            ],
            'company_B'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            [
                'company_id' => '$company_A.entity_id$',
                'company_quote_enabled' => 1,
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$admin_customer_A.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'item'
        ),
        DataFixture(
            SetShippingAddress::class,
            [
                'cart_id' => '$quote.id$'
            ]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote to be placed as purchase order',
                NegotiableQuoteInterface::CREATOR_TYPE => UserContextInterface::USER_TYPE_CUSTOMER,
                NegotiableQuoteInterface::CREATOR_ID => '$user.id$',
                NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
                'quote' => [
                    'customer_id' => '$admin_customer_A.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'negotiable_quote'
        ),
    ]
    public function testMoveItemToRequisitionListSuccess()
    {
        $customerA = DataFixtureStorageManager::getStorage()->get('admin_customer_A');

        $connection = $this->resource->getConnection();
        $connection->delete(
            'company_advanced_customer_entity',
            ['company_id = ?' => 0, 'customer_id = ?' => $customerA->getId()]
        );
        $existingRequistionList = $this->createRequisitionList((int)$customerA->getId());
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $quoteId = (int)$quote->getId();
        /** @var CartInterface $quote */
        $quote = $this->quoteRepository->get($quoteId);
        $quoteItem = $quote->getAllItems()[0];
        $this->customerSession->loginById($quote->getCustomerId());
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'quote-id' => $quote->getId(),
            'item-id' => $quoteItem->getItemId(),
            'requisition-id' => $existingRequistionList->getId(),
        ]);

        $this->dispatch('negotiable_quote_requisition_list/quote/itemMoveToRequisitionList');

        // Assert response and requisition list
        $this->assertSessionMessages(
            $this->equalTo(['1 line-item successfully moved to ' . $existingRequistionList->getName()]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    #[
        ConfigFixture('btob/website_configuration/company_active', 1, ScopeInterface::SCOPE_WEBSITE),
        ConfigFixture('btob/website_configuration/negotiablequote_active', 1, ScopeInterface::SCOPE_WEBSITE),
        ConfigFixture('btob/website_configuration/requisition_list_active', 1, ScopeInterface::SCOPE_WEBSITE),
        DataFixture(Customer::class, as: 'admin_customer_A'),
        DataFixture(Customer::class, as: 'admin_customer_B'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer_A.id$'
            ],
            'company_A'
        ),
        DataFixture(
            CompanyFixture::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$admin_customer_B.id$'
            ],
            'company_B'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            [
                'company_id' => '$company_A.entity_id$',
                'company_quote_enabled' => 1,
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$admin_customer_A.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'item'
        ),
        DataFixture(
            SetShippingAddress::class,
            [
                'cart_id' => '$quote.id$'
            ]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote to be placed as purchase order',
                NegotiableQuoteInterface::CREATOR_TYPE => UserContextInterface::USER_TYPE_CUSTOMER,
                NegotiableQuoteInterface::CREATOR_ID => '$user.id$',
                NegotiableQuoteInterface::QUOTE_STATUS => NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
                'quote' => [
                    'customer_id' => '$admin_customer_A.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'negotiable_quote'
        ),
    ]
    public function testMoveItemToRequisitionListOfAnotherCustomerFailure()
    {
        $customerA = DataFixtureStorageManager::getStorage()->get('admin_customer_A');
        $customerB = DataFixtureStorageManager::getStorage()->get('admin_customer_B');

        $connection = $this->resource->getConnection();
        $connection->delete(
            'company_advanced_customer_entity',
            ['company_id = ?' => 0, 'customer_id = ?' => $customerA->getId()]
        );
        $existingRequistionList = $this->createRequisitionList((int)$customerB->getId());
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $quoteId = (int)$quote->getId();
        /** @var CartInterface $quote */
        $quote = $this->quoteRepository->get($quoteId);
        $quoteItem = $quote->getAllItems()[0];
        $this->customerSession->loginById($quote->getCustomerId());
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'quote-id' => $quote->getId(),
            'item-id' => $quoteItem->getItemId(),
            'requisition-id' => $existingRequistionList->getId(),
        ]);

        $this->dispatch('negotiable_quote_requisition_list/quote/itemMoveToRequisitionList');
        $this->assertRedirect($this->stringContains('noroute'));
    }

    /**
     * Create requisition list
     *
     * @param int $customerId
     * @return RequisitionListInterface
     * @throws CouldNotSaveException
     */
    private function createRequisitionList(int $customerId): RequisitionListInterface
    {
        $requisitionList = $this->requisitionListFactory->create();
        $requisitionList->setCustomerId($customerId);
        $requisitionList->setName('Test Requisition List');
        $requisitionList->setDescription('Test Description');
        $this->requisitionListRepository->save($requisitionList);
        return $requisitionList;
    }
}
