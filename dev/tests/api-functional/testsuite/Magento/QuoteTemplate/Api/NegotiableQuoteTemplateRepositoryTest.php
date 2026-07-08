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
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Framework\Reflection\DataObjectProcessor;
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

class NegotiableQuoteTemplateRepositoryTest extends WebapiAbstract
{
    /**
     * @var AdminTokenServiceInterface
     */
    private $adminTokens;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->dataObjectProcessor = $objectManager->get(DataObjectProcessor::class);
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
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_SUBMITTED_BY_SELLER,
                TemplateInterface::MIN_ORDERS => 10,
                TemplateInterface::MAX_ORDERS => 20
            ],
            'template_a'
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => false,
                TemplateInterface::STATUS => TemplateInterface::STATUS_SUBMITTED_BY_BUYER,
                TemplateInterface::MIN_ORDERS => 5,
                TemplateInterface::MAX_ORDERS => 10
            ],
            'template_b'
        ),
    ]
    public function testGetNegotiableQuoteTemplate(): void
    {
        /** @var TemplateInterface $template */
        $template = DataFixtureStorageManager::getStorage()->get('template_a');
        $adminUser = DataFixtureStorageManager::getStorage()->get('user');
        $token = $this->adminTokens->createAdminAccessToken(
            $adminUser->getUsername(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/' . $template->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];

        $result = $this->_webApiCall($serviceInfo);

        $expectedTemplateData = $template->getData();
        unset($expectedTemplateData['id']);
        unset($expectedTemplateData['extension_attributes']);
        unset($expectedTemplateData['items']);
        foreach ($expectedTemplateData as $key => $expectedValue) {
            $this->assertEquals($expectedValue, $result[$key]);
        }
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        DataFixture(Customer::class, as: 'customer_a'),
        DataFixture(Customer::class, as: 'customer_b'),
        DataFixture(RoleFixture::class, as: 'adminRole'),
        DataFixture(User::class, ['role_id' => '$adminRole.id$'], 'user'),
        DataFixture(
            Company::class,
            [
                'status' => 1,
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$'
            ],
            'company'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company.id$',
                'customer_id' => '$customer_b.id$',
            ]
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_a.id$'], 'quote_a'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer_b.id$'], 'quote_b'),
        DataFixture(Product::class, [], 'product'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_a.id$', 'product_id' => '$product.id$', 'qty' => 2],
            'item'
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$quote_b.id$', 'product_id' => '$product.id$', 'qty' => 2],
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
                    'customer_id' => '$customer_a.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'negotiable_quote_a'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote to be created quote template from',
                'quote' => [
                    'customer_id' => '$customer_a.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'negotiable_quote_b'
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer_a.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote_a.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => true,
                TemplateInterface::STATUS => TemplateInterface::STATUS_SUBMITTED_BY_SELLER,
                TemplateInterface::MIN_ORDERS => 10,
                TemplateInterface::MAX_ORDERS => 20
            ],
            'template_a'
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer_a.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote_a.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => false,
                TemplateInterface::STATUS => TemplateInterface::STATUS_SUBMITTED_BY_BUYER,
                TemplateInterface::MIN_ORDERS => 2500,
                TemplateInterface::MAX_ORDERS => 10000
            ],
            'template_b'
        ),
        DataFixture(
            Template::class,
            [
                TemplateInterface::NAME => 'Quote Template',
                TemplateInterface::CREATOR_ID => '$customer_b.id$',
                Template::TEMPLATE_CREATED_FROM_QUOTE => '$quote_b.id$',
                TemplateInterface::IS_MIN_MAX_QTY_USED => false,
                TemplateInterface::STATUS => TemplateInterface::STATUS_SUBMITTED_BY_BUYER,
                TemplateInterface::MIN_ORDERS => 5,
                TemplateInterface::MAX_ORDERS => 10
            ],
            'template_c'
        ),
    ]
    public function testListNegotiableQuoteTemplate(): void
    {
        /** @var TemplateInterface $template */
        $adminUser = DataFixtureStorageManager::getStorage()->get('user');
        $token = $this->adminTokens->createAdminAccessToken(
            $adminUser->getUsername(),
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $filter = Bootstrap::getObjectManager()->create(FilterBuilder::class)
            ->setField(TemplateInterface::MIN_ORDERS)
            ->setValue(2400)
            ->setConditionType('gt')
            ->create();
        $this->searchCriteriaBuilder->addFilters([$filter]);
        $searchData = $this->dataObjectProcessor->buildOutputDataArray(
            $this->searchCriteriaBuilder->create(),
            SearchCriteriaInterface::class
        );
        $requestData = ['searchCriteria' => $searchData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/negotiableQuoteTemplate/?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals(1, $result['total_count']);
    }
}
