<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2023 Adobe
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

namespace Magento\QuoteTemplate\Api;

use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuoteTemplate\Api\Data\TemplateInterface;
use Magento\NegotiableQuoteTemplate\Api\Template\RepositoryInterface as TemplateRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Test\Fixture\Customer;
use Magento\User\Test\Fixture\User;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\ApplyQuoteConfigForCompany;
use Magento\TestFramework\Helper\Bootstrap;

class CreateNegotiableQuoteTemplateTest extends WebapiAbstract
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $quoteTemplateRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteTemplateRepository = $objectManager->get(TemplateRepositoryInterface::class);
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(RoleFixture::class, as: 'adminRole'),
        DataFixture(User::class, ['role_id' => '$adminRole.id$'], 'user'),
        DataFixture(
            Company::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer.id$'
            ],
            'company'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(
            ApplyQuoteConfigForCompany::class,
            ['company_id' => '$company.entity_id$', 'company_quote_enabled' => 1]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote to be created quote template from',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'negotiable_quote'
        )
    ]
    public function testCreateNegotiableQuoteTemplate(): void
    {
        /** @var NegotiableQuoteInterface $quote */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $token = $this->customerTokenService->createCustomerAccessToken(
            $customer->getEmail(),
            'password'
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/request/me',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $result = $this->_webApiCall($serviceInfo, ['quoteId' => (int)$quote->getId()]);
        $this->assertEquals(TemplateInterface::STATUS_DRAFT_BY_BUYER, $result['status']);
        /** @var TemplateInterface $quoteTemplate */
        $quoteTemplate = $this->quoteTemplateRepository->getById($result['template_id']);
        $templateQuote = $this->quoteRepository->get($quoteTemplate->getParentQuoteId());
        $this->assertEquals($customer->getId(), $templateQuote->getCustomerId());
        foreach ($quote->getItems() as $key => $item) {
            $this->assertEquals($item->getSku(), $templateQuote->getItems()[$key]->getSku());
            $this->assertEquals($item->getQty(), $templateQuote->getItems()[$key]->getQty());
        }
    }
}
