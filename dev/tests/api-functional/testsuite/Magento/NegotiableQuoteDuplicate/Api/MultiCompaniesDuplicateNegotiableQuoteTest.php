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

namespace Magento\NegotiableQuoteDuplicate\Api;

use Magento\Authorization\Test\Fixture\Role as RoleFixture;
use Magento\Catalog\Test\Fixture\Product;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\ConfigurableProduct\Test\Fixture\Attribute;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProduct;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCart;
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
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Test\Fixture\AssignCompany;

class MultiCompaniesDuplicateNegotiableQuoteTest extends WebapiAbstract
{
    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->negotiableQuoteRepository = $objectManager->get(NegotiableQuoteRepositoryInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/negotiablequote_active', 1),
        Config('btob/website_configuration/sharedcatalog_active', 0),
        DataFixture(Customer::class, as: 'company_admin_a'),
        DataFixture(Customer::class, as: 'company_admin_b'),
        DataFixture(Customer::class, as: 'company_user'),
        DataFixture(RoleFixture::class, as: 'adminRole'),
        DataFixture(User::class, ['role_id' => '$adminRole.id$'], 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$company_admin_a.id$',
                CompanyInterface::STATUS => 1,
                CompanyInterface::NAME => 'Company A',
            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$company_admin_b.id$',
                CompanyInterface::STATUS => 1,
                CompanyInterface::NAME => 'Company B',
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_admin_a.id$',
                CompanyCustomerInterface::JOB_TITLE => 'Job A company_admin_a',
                CompanyCustomerInterface::TELEPHONE => '11111111',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_admin_a.id$',
                CompanyCustomerInterface::JOB_TITLE => 'Job B company_admin_a',
                CompanyCustomerInterface::TELEPHONE => '12121212',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_user.id$',
                CompanyCustomerInterface::JOB_TITLE => 'Job A company_user',
                CompanyCustomerInterface::TELEPHONE => '21212121',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_user.id$',
                CompanyCustomerInterface::JOB_TITLE => 'Job B company_user',
                CompanyCustomerInterface::TELEPHONE => '22222222',
            ]
        ),
        DataFixture(Product::class, as: 'simple1'),
        DataFixture(Product::class, as: 'simple2'),
        DataFixture(Attribute::class, as: 'attr'),
        DataFixture(
            ConfigurableProduct::class,
            ['_options' => ['$attr$'], '_links' => ['$simple1$', '$simple2$']],
            'config'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$company_admin_a.id$'], 'admin_quote_a'),
        DataFixture(CustomerCart::class, ['customer_id' => '$company_admin_a.id$'], 'admin_quote_b'),
        DataFixture(CustomerCart::class, ['customer_id' => '$company_user.id$'], 'user_quote_a'),
        DataFixture(CustomerCart::class, ['customer_id' => '$company_user.id$'], 'user_quote_b'),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$admin_quote_a.id$', 'company_id' => '$company_a.id$']
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$admin_quote_b.id$', 'company_id' => '$company_b.id$']
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$user_quote_a.id$', 'company_id' => '$company_a.id$']
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$user_quote_b.id$', 'company_id' => '$company_b.id$']
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$admin_quote_a.id$', 'product_id' => '$simple1.id$', 'qty' => 1],
            'admin_a_item'
        ),
        DataFixture(
            AddConfigurableProductToCart::class,
            [
                'cart_id' => '$admin_quote_b.id$',
                'product_id' => '$config.id$',
                'child_product_id' => '$simple2.id$',
                'qty' => 2
            ],
            'admin_b_item'
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$user_quote_a.id$', 'product_id' => '$simple2.id$', 'qty' => 2],
            'user_a_item'
        ),
        DataFixture(
            AddConfigurableProductToCart::class,
            [
                'cart_id' => '$user_quote_b.id$',
                'product_id' => '$config.id$',
                'child_product_id' => '$simple1.id$',
                'qty' => 1
            ],
            'user_b_item'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Admin Company A Quote to be Duplicated',
                'quote' => [
                    'customer_id' => '$company_admin_a.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$simple1.sku$',
                            CartItemInterface::KEY_QTY => 1,
                        ],
                    ],
                ],
            ],
            'admin_a_ng_quote'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Admin Company B Quote to be Duplicated',
                'quote' => [
                    'customer_id' => '$company_admin_a.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$simple2.sku$',
                            CartItemInterface::KEY_QTY => 2,
                            CartItemInterface::KEY_PRODUCT_TYPE => 'configurable',
                        ],
                    ],
                ],
            ],
            'admin_b_ng_quote'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'User Company A Quote to be Duplicated',
                'quote' => [
                    'customer_id' => '$company_user.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$simple2.sku$',
                            CartItemInterface::KEY_QTY => 2,
                        ],
                    ],
                ],
            ],
            'user_a_ng_quote'
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'User Company B Quote to be Duplicated',
                'quote' => [
                    'customer_id' => '$company_user.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$simple1.sku$',
                            CartItemInterface::KEY_QTY => 1,
                            CartItemInterface::KEY_PRODUCT_TYPE => 'configurable',
                        ],
                    ],
                ],
            ],
            'user_b_ng_quote'
        )
    ]
    /**
     * @dataProvider duplicateNegotiableQuoteDataProvider
     * @param string $expectedQuoteName
     * @param string $ngQuote
     * @param string|null $companyInHeader
     * @returns void
     */
    public function testDuplicateNegotiableQuote(
        string $expectedQuoteName,
        string $ngQuote,
        ?string $companyInHeader,
        bool $isExceptionExpected
    ): void {
        /** @var NegotiableQuoteInterface $quote */
        $quote = DataFixtureStorageManager::getStorage()->get($ngQuote);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf('/V1/negotiableQuote/%d/duplicate', $quote->getId()),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        if ($companyInHeader) {
            /** @var CompanyInterface $company */
            $company = DataFixtureStorageManager::getStorage()->get($companyInHeader);
            $serviceInfo['rest']['headers'] = ['X-Adobe-Company: ' . $company->getId()];
        }

        if ($isExceptionExpected) {
            $this->expectException(\Exception::class);
        }

        $duplicateNgQuoteId = $this->_webApiCall($serviceInfo);

        if (!$isExceptionExpected) {
            $duplicateQuote = $this->negotiableQuoteRepository->getById((int)$duplicateNgQuoteId);
            $duplicatedNgQuoteName = $duplicateQuote->getQuoteName();
            $this->assertStringContainsString($quote->getQuoteName(), $duplicatedNgQuoteName);
            $this->assertStringEndsWith(" (copy)", $duplicatedNgQuoteName);
            $this->assertStringContainsString($expectedQuoteName, $duplicatedNgQuoteName);
            $this->negotiableQuoteRepository->delete($duplicateQuote);
        }
    }

    /**
     * @return array
     */
    public function duplicateNegotiableQuoteDataProvider(): array
    {
        return [
            ['Admin Company A Quote to be Duplicated', 'admin_a_ng_quote', 'company_b', false],
            ['Admin Company B Quote to be Duplicated', 'admin_b_ng_quote', 'company_a', false],
            ['Admin Company A Quote to be Duplicated', 'admin_a_ng_quote', null, false],
            ['Admin Company B Quote to be Duplicated', 'admin_b_ng_quote', null, false],
            ['User Company A Quote to be Duplicated', 'user_a_ng_quote', 'company_b', false],
            ['User Company B Quote to be Duplicated', 'user_b_ng_quote', 'company_a', false],
            ['User Company A Quote to be Duplicated', 'user_a_ng_quote', null, false],
            ['User Company B Quote to be Duplicated', 'user_b_ng_quote', null, false],
            ['Admin Company A Quote to be Duplicated', 'admin_a_ng_quote', 'company_a', true],
        ];
    }
}
