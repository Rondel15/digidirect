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
use Magento\Company\Test\Fixture\Role;
use Magento\Company\Test\Fixture\SetRolesForCompanyUser;
use Magento\CompanyQuote\Test\Fixture\AssignCompanyToQuote;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Model\Mutation\BatchResult;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\SendByAdminNegotiableQuote;
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
class DeleteNegotiableQuotesTest extends GraphQlAbstract
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
        DataFixture(
            Role::class,
            [
                'company_id' => '$company_one.id$',
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
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::edit_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::delete_quotes_sub',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role_one'
        ),
        DataFixture(
            Role::class,
            [
                'company_id' => '$company_two.id$',
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
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::manage_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::edit_quotes_sub',
                        'permission' => 'allow'
                    ],
                    [
                        'resource_id' => 'Magento_NegotiableQuote::delete_quotes_sub',
                        'permission' => 'allow'
                    ],
                ]
            ],
            'role_two'
        ),
        DataFixture(ProductFixture::class, as: 'product_one'),
        DataFixture(ProductFixture::class, as: 'product_two'),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_One_',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product_one.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'NQ_One_mask'
        ),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$NQ_One_mask.id$']),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$NQ_One_mask.id$', 'company_id' => '$company_one.id$']
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_Two_',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product_two.sku$',
                            CartItemInterface::KEY_QTY => 2
                        ],
                    ],
                ],
            ],
            'NQ_Two_mask'
        ),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$NQ_Two_mask.id$']),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$NQ_Two_mask.id$', 'company_id' => '$company_one.id$']
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer.id$',
                'company_id' => '$company_one.id$',
                'role_ids' => ['$role_one.id$']
            ]
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_Three_',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product_one.sku$',
                            CartItemInterface::KEY_QTY => 1
                        ],
                    ],
                ],
            ],
            'NQ_Three_mask'
        ),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$NQ_Three_mask.id$']),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$NQ_Three_mask.id$', 'company_id' => '$company_two.id$']
        ),
        DataFixture(
            NegotiableQuote::class,
            [
                NegotiableQuoteInterface::QUOTE_NAME => 'NQ_Four_',
                'quote' => [
                    'customer_id' => '$customer.id$',
                    CartInterface::KEY_ITEMS => [
                        [
                            CartItemInterface::KEY_SKU => '$product_two.sku$',
                            CartItemInterface::KEY_QTY => 2
                        ],
                    ],
                ],
            ],
            'NQ_Four_mask'
        ),
        DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$NQ_Four_mask.id$']),
        DataFixture(
            AssignCompanyToQuote::class,
            [CartInterface::KEY_ENTITY_ID => '$NQ_Four_mask.id$', 'company_id' => '$company_two.id$']
        ),
        DataFixture(
            SetRolesForCompanyUser::class,
            [
                'customer_id' => '$customer.id$',
                'company_id' => '$company_two.id$',
                'role_ids' => ['$role_two.id$']
            ]
        ),
    ]
    /**
     * Test quote loading in different company scopes.
     *
     * @dataProvider getMutationData
     * @param array       $quoteMasks
     * @param string|null $company
     * @param string|null $exception
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testMutation(
        array $quoteMasks,
        ?string $company = null,
        ?array $errors = null
    ): void {
        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $headers = ['Authorization' => 'Bearer ' . $customerToken];
        if ($company) {
            $companyId = FixtureManager::getStorage()->get($company)->getId();
            $headers['X-Adobe-Company'] = base64_encode($companyId);
        }

        $response = $this->graphQlMutation(
            $this->getMutation(
                $quoteMasks
            ),
            [],
            '',
            $headers
        );

        if ($errors != null) {
            foreach ($quoteMasks as $key => $quoteMask) {
                if ($errors[$key] === null) {
                    continue;
                }
                $expectedErrorMessage = $errors[$key];
                $this->assertOperationResultFailure(
                    $response['deleteNegotiableQuotes']['operation_results'][$key],
                    $quoteMask,
                    'NoSuchEntityUidError',
                    $expectedErrorMessage
                );
            }
        }

        if ($errors === null) {
            $this->assertNotEmpty($response);
            $this->assertArrayHasKey('result_status', $response['deleteNegotiableQuotes']);
            $this->assertEquals(BatchResult::STATUS_SUCCESS, $response['deleteNegotiableQuotes']['result_status']);
        }
    }

    /**
     * Assert operation result is a failure.
     *
     * @param array $operationResult
     * @param string $expectedUid
     * @param string $expectedErrorType
     * @param string $expectedErrorMessage
     * @return void
     */
    private function assertOperationResultFailure(
        array $operationResult,
        string $expectedUid,
        string $expectedErrorType,
        string $expectedErrorMessage
    ) {
        // Assert result type is a failure for the expected quote_uid
        $this->assertEquals('DeleteNegotiableQuoteOperationFailure', $operationResult['__typename']);
        $this->assertArrayHasKey('quote_uid', $operationResult);
        $this->assertEquals($expectedUid, $operationResult['quote_uid']);

        // Assert exactly 1 error is present
        $this->assertArrayHasKey('errors', $operationResult);
        $this->assertCount(1, $operationResult['errors']);

        // Assert error type and message
        $error = $operationResult['errors'][0];
        $this->assertEquals($expectedErrorType, $error['__typename']);
        $this->assertArrayHasKey('message', $error);
        $this->assertEquals($expectedErrorMessage, $error['message']);
    }

    /**
     * Data provider for testMutation.
     *
     * @return array
     */
    public function getMutationData(): array
    {
        return [
            [['NQ_One_mask'], null, null],
            [['NQ_One_mask'], 'company_one', null],
            [['NQ_One_mask'], 'company_two', ['Could not find a quote with the specified UID.']],
            [['NQ_Three_mask'], null, ['Could not find a quote with the specified UID.']],
            [['NQ_Three_mask'], 'company_one', ['Could not find a quote with the specified UID.']],
            [['NQ_Three_mask'], 'company_two', null],
            [['NQ_One_mask', 'NQ_Two_mask'], null, null],
            [['NQ_One_mask', 'NQ_Two_mask'], 'company_one', null],
            [['NQ_Three_mask', 'NQ_Four_mask'], null, [null, 'Could not find a quote with the specified UID.']],
            [['NQ_Two_mask', 'NQ_Four_mask'], 'company_two', ['Could not find a quote with the specified UID.', null]]
        ];
    }

    /**
     * Generates GraphQl mutation
     *
     * @param array $quoteIds
     * @return string
     */
    private function getMutation(array $quoteIds): string
    {
        $quoteIds = '"' . implode('","', $quoteIds) . '"';
        return <<<MUTATION
mutation {
  deleteNegotiableQuotes(
    input: {
      quote_uids: [{$quoteIds}]
    }
  ) {
    result_status
    operation_results {
      ...on NegotiableQuoteUidOperationSuccess{
        __typename
        quote_uid
      }
      ...on DeleteNegotiableQuoteOperationFailure{
        __typename
        quote_uid
        errors {
          __typename
          ...on ErrorInterface{
            message
          }
          ...on NoSuchEntityUidError{
            uid
          }
        }
      }
    }
  }
}
MUTATION;
    }
}
