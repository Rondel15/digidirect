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
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuoteTemplate\Api\Data\TemplateInterface;
use Magento\NegotiableQuoteTemplate\Api\Template\RepositoryInterface as TemplateRepositoryInterface;
use Magento\NegotiableQuoteTemplate\Test\Fixture\Template;
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

class SubmitToBuyerNegotiableQuoteTemplateTest extends WebapiAbstract
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $quoteTemplateRepository;

    /**
     * @var AdminTokenServiceInterface
     */
    private $adminTokenService;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteTemplateRepository = $objectManager->get(TemplateRepositoryInterface::class);
        $this->adminTokenService = $objectManager->get(AdminTokenServiceInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            [
                CustomerInterface::KEY_ADDRESSES =>[
                    [
                        'firstname' => 'Jane',
                        'lastname' => 'Doe',
                        'street' => ['321 Test Street'],
                        'city' => 'Los Angeles',
                        'postcode' => '90002',
                        'telephone' => '1234567899',
                        'is_default' => 1
                    ]
                ]
            ],
            'customer'
        ),
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
    public function testCreateAndSubmitToBuyerNegotiableQuoteTemplate(): void
    {
        $quote = DataFixtureStorageManager::getStorage()->get('quote');
        $adminUser = DataFixtureStorageManager::getStorage()->get('user');
        $token = $this->adminTokenService->createAdminAccessToken(
            $adminUser->getUsername(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/request',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $result = $this->_webApiCall($serviceInfo, ['quoteId' => (int)$quote->getId()]);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/submitToCustomer',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $this->_webApiCall($serviceInfo, ['templateId' => (int)$result['template_id']]);

        /** @var TemplateInterface $quoteTemplate */
        $quoteTemplate = $this->quoteTemplateRepository->getById($result['template_id']);
        $this->assertEquals(TemplateInterface::STATUS_CREATED_BY_SELLER, $quoteTemplate->getStatus());
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            [
                CustomerInterface::KEY_ADDRESSES =>[
                    [
                        'firstname' => 'Jane',
                        'lastname' => 'Doe',
                        'street' => ['321 Test Street'],
                        'city' => 'Los Angeles',
                        'postcode' => '90002',
                        'telephone' => '1234567899',
                        'is_default' => 1
                    ]
                ]
            ],
            'customer'
        ),
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
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_PROCESSING_BY_SELLER,
            ],
            'template'
        ),

    ]
    public function testSubmitToBuyerExistingNegotiableQuoteTemplate(): void
    {
        $template = DataFixtureStorageManager::getStorage()->get('template');
        $adminUser = DataFixtureStorageManager::getStorage()->get('user');
        $token = $this->adminTokenService->createAdminAccessToken(
            $adminUser->getUsername(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/submitToCustomer',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $this->_webApiCall($serviceInfo, ['templateId' => (int)$template->getId()]);

        /** @var TemplateInterface $quoteTemplate */

        $quoteTemplate = $this->quoteTemplateRepository->getById((int)$template->getId());
        $this->assertEquals(TemplateInterface::STATUS_SUBMITTED_BY_SELLER, $quoteTemplate->getStatus());
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(
            Customer::class,
            as: 'customer'
        ),
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
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_PROCESSING_BY_BUYER,
            ],
            'template'
        ),
    ]
    public function testSubmitQuoteTemplateToBuyerFailureWhenWrongStatus(): void
    {
        $template = DataFixtureStorageManager::getStorage()->get('template');
        $adminUser = DataFixtureStorageManager::getStorage()->get('user');
        $token = $this->adminTokenService->createAdminAccessToken(
            $adminUser->getUsername(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/submitToCustomer',
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token
            ]
        ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The template cannot be sent at the moment.');
        $this->_webApiCall($serviceInfo, ['templateId' => (int)$template->getId()]);
    }
}
