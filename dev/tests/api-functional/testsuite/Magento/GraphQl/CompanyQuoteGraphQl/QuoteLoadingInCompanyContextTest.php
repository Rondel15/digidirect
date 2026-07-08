<?php
/**
 * ADOBE CONFIDENTIAL
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
 */
declare(strict_types=1);

namespace Magento\GraphQl\CompanyQuoteGraphQl;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;

/**
 * Test quote loading in different company scopes.
 */
class QuoteLoadingInCompanyContextTest extends GraphQlAbstract
{
    /**
     * @var ?CustomerTokenServiceInterface
     */
    private ?CustomerTokenServiceInterface $customerTokenService;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    #[
        AppArea('frontend'),
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', true),
        DataFixture(Customer::class, as: 'company_user_a'),
        DataFixture(Customer::class, as: 'company_user_b'),
        DataFixture(Customer::class, as: 'company_user_c'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$company_user_a.id$'
            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$company_user_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_a.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_user_c.id$',
                CompanyCustomerInterface::IS_DEFAULT => 1
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_b.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$company_user_c.id$',
                CompanyCustomerInterface::IS_DEFAULT => 0
            ]
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote_C_A_',
                'quote' => [
                    'customer_id' => '$company_user_c.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'quote_company_a_customer_c'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_company_a_customer_c.id$', 'company_id' => '$company_a.id$']
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'Quote_C_B_',
                'quote' => [
                    'customer_id' => '$company_user_c.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'quote_company_b_customer_c'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_company_b_customer_c.id$', 'company_id' => '$company_b.id$']
        ),
    ]
    /**
     * Test quote loading in different company scopes.
     *
     * @dataProvider getQuoteInCompanyContextDataProvider
     * @param string $quoteMask
     * @param string|null $company
     * @param string|null $exception
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testGetQuoteInCompanyContext(string $quoteMask, ?string $company = null, ?string $exception = null)
    {
        $customer = FixtureManager::getStorage()->get('company_user_c');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $headers = ['Authorization' => 'Bearer ' . $customerToken];
        if ($company) {
            $companyId = FixtureManager::getStorage()->get($company)->getId();
            $headers['X-Adobe-Company'] = base64_encode($companyId);
        }
        if ($exception) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($exception);
        }

        $response = $this->graphQlQuery($this->getQuery($quoteMask), [], '', $headers);
        if (!$exception) {
            $this->assertStringStartsWith($response['negotiableQuote']['name'], $quoteMask);
        }
    }

    /**
     * Data provider for testGetQuoteInCompanyContext.
     *
     * @return array
     */
    public function getQuoteInCompanyContextDataProvider(): array
    {
        return [
            ['Quote_C_A_mask', 'company_a', null],
            ['Quote_C_B_mask', 'company_a', 'Could not find a quote with the specified UID.'],
            ['Quote_C_A_mask', 'company_b', 'Could not find a quote with the specified UID.'],
            ['Quote_C_B_mask', 'company_b', null],
            ['Quote_C_A_mask', null, null],
            ['Quote_C_B_mask', null, 'Could not find a quote with the specified UID.'],
        ];
    }

    /**
     * Get query.
     *
     * @param string $quoteMask
     * @return string
     */
    private function getQuery(string $quoteMask): string
    {
        return <<< QUERY
    query {
      negotiableQuote(uid: "$quoteMask") {
        uid,
        name
      }
    }
    QUERY;
    }
}
