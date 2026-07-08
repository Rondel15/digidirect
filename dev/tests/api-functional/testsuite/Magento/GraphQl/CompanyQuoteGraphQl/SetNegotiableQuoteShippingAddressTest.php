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
use Magento\Customer\Api\Data\CustomerInterface;
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
class SetNegotiableQuoteShippingAddressTest extends GraphQlAbstract
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
        AppArea('graphql'),
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', true),
        DataFixture(Customer::class, as: 'admin_one'),
        DataFixture(Customer::class, as: 'admin_two'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(User::class, as: 'sales_rep'),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin_one.id$'
            ],
            'company_one'
        ),
        DataFixture(
            Company::class,
            [
                CompanyInterface::SALES_REPRESENTATIVE_ID => '$sales_rep.id$',
                CompanyInterface::SUPER_USER_ID => '$admin_two.id$'
            ],
            'company_two'
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_one.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                CompanyCustomerInterface::COMPANY_ID => '$company_two.id$',
                CompanyCustomerInterface::CUSTOMER_ID => '$customer.id$',
            ]
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_One_',
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
            'quote_one'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_one.id$', 'company_id' => '$company_one.id$']
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_Two_',
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
            'quote_two'
        ),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$quote_two.id$', 'company_id' => '$company_two.id$']
        ),
    ]
    /**
     * Test quote loading in different company scopes.
     *
     * @dataProvider getMutationData
     * @param string $quoteMask
     * @param string|null $company
     * @param string|null $exception
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testMutation(
        string $quoteMask,
        ?string $company = null,
        ?string $exception = null
    ): void {
        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');
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

        $address = $this->getAddressData();

        $response = $this->graphQlMutation(
            $this->getMutation(
                $quoteMask,
                $address
            ),
            [],
            '',
            $headers
        );

        if (!$exception) {
            unset($address['region']['id']);
            $this->assertNotEmpty($response['setNegotiableQuoteShippingAddress']);
            $this->assertArrayHasKey('quote', $response['setNegotiableQuoteShippingAddress']);
            $this->assertArrayHasKey('uid', $response['setNegotiableQuoteShippingAddress']['quote']);
            $this->assertEquals($quoteMask, $response['setNegotiableQuoteShippingAddress']['quote']['uid']);
            $this->assertNotEmpty($response['setNegotiableQuoteShippingAddress']['quote']['shipping_addresses']);
            $this->assertCount(1, $response['setNegotiableQuoteShippingAddress']['quote']['shipping_addresses']);
            $this->assertArrayHasKey(0, $response['setNegotiableQuoteShippingAddress']['quote']['shipping_addresses']);
            $this->assertEquals(
                $address,
                $response['setNegotiableQuoteShippingAddress']['quote']['shipping_addresses'][0]
            );
        }
    }

    /**
     * Data provider for testMutation.
     *
     * @return array
     */
    public function getMutationData(): array
    {
        return [
            ['NQ_One_mask', null, null],
            ['NQ_One_mask', 'company_one', null],
            ['NQ_One_mask', 'company_two', 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', null, 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', 'company_one', 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', 'company_two', null],
        ];
    }

    /**
     * Returns test address data
     *
     * @return array
     */
    private function getAddressData(): array
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => ['6161 West Centinela Ave.'],
            'city' => 'Culver City',
            'region' => ['id' => 12, 'code' => 'CA'],
            'postcode' => '90230',
            'country' => ['code' => 'US'],
            'telephone' => '5555555555',
            'company' => 'Magento'
        ];
    }

    /**
     * Generates GraphQl mutation
     *
     * @param string $quoteId
     * @param array $address
     * @return string
     */
    private function getMutation(string $quoteId, array $address): string
    {
        return <<<MUTATION
mutation {
  setNegotiableQuoteShippingAddress(
    input: {
      quote_uid: "{$quoteId}"
      shipping_addresses: [{
        address: {
          firstname: "{$address['firstname']}",
          lastname: "{$address['lastname']}",
          country_code: "{$address['country']['code']}",
          street: ["{$address['street'][0]}"],
          city: "{$address['city']}",
          region_id: {$address['region']['id']},
          region: "{$address['region']['code']}",
          postcode: "{$address['postcode']}",
          telephone: "{$address['telephone']}",
          company: "{$address['company']}"
          save_in_address_book: false
        }
      }]
    }
  ) {
    quote {
      uid
      shipping_addresses {
        firstname
        lastname
        street
        city
        region {
          code
        }
        postcode
        country {
          code
        }
        telephone
        company
      }
    }
  }
}
MUTATION;
    }
}
