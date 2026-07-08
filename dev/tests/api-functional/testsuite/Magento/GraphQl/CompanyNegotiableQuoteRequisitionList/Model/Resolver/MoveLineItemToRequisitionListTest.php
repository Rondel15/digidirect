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

namespace Magento\GraphQl\CompanyNegotiableQuoteRequisitionList\Model\Resolver;

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
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\RequisitionList\Test\Fixture\RequisitionList;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Exception;
use Magento\NegotiableQuote\Test\Fixture\SendByAdminNegotiableQuote;
use Magento\NegotiableQuote\Test\Fixture\NegotiableQuoteSnapshot;

/**
 * Test moving negotiable quote item to requisition list in different company scopes.
 */
#[
    AppArea('graphql'),
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
    DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote_one'),
    DataFixture(
        AssignCompanyToQuote::class,
        [CartInterface::KEY_ENTITY_ID => '$quote_one.id$', 'company_id' => '$company_one.id$']
    ),
    DataFixture(
        AddProductToCart::class,
        ['cart_id' => '$quote_one.id$', 'product_id' => '$product.id$', 'qty' => 1],
        'item_one'
    ),
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
        'nq_one'
    ),
    DataFixture(
        NegotiableQuoteSnapshot::class,
        [
            NegotiableQuoteSnapshot::QUOTE_ID => '$quote_one.id$',
        ]
    ),
    DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$quote_one.id$']),
    DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'quote_two'),
    DataFixture(
        AssignCompanyToQuote::class,
        [CartInterface::KEY_ENTITY_ID => '$quote_two.id$', 'company_id' => '$company_two.id$']
    ),
    DataFixture(
        AddProductToCart::class,
        ['cart_id' => '$quote_two.id$', 'product_id' => '$product.id$', 'qty' => 1],
        'item_two'
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
        'nq_two'
    ),
    DataFixture(
        NegotiableQuoteSnapshot::class,
        [
            NegotiableQuoteSnapshot::QUOTE_ID => '$quote_two.id$',
        ]
    ),
    DataFixture(SendByAdminNegotiableQuote::class, ['cart_id' => '$quote_two.id$']),
    DataFixture(RequisitionList::class, ['customer_id' => '$customer.id$'], 'requisitionList'),
]
class MoveLineItemToRequisitionListTest extends GraphQlAbstract
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

    /**
     * Test moving negotiable quote item to a requisition list in different company scopes.
     *
     * @dataProvider getMutationData
     * @param string $quoteMask
     * @param string $itemName
     * @param string|null $company
     * @param string|null $exception
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', 1),
        ConfigFixture('btob/website_configuration/requisition_list_active', 1),
    ]
    public function testMutation(
        string $quoteMask,
        string $itemName,
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

        /** @var CartItemInterface $item */
        $item = FixtureManager::getStorage()->get($itemName);
        $itemIdMask = base64_encode((string)$item->getItemId());

        /** @var RequisitionListInterface $rqList */
        $rqList = FixtureManager::getStorage()->get('requisitionList');
        $rqListIdMask = base64_encode((string)$rqList->getId());

        $input = [
            'quote_uid' => $quoteMask,
            'quote_item_uid' => $itemIdMask,
            'requisition_list_uid' => $rqListIdMask,
        ];
        $response = $this->graphQlMutation(
            $this->getMutation($input),
            [],
            '',
            $headers
        );

        if (!$exception) {
            $this->assertNotEmpty($response['moveLineItemToRequisitionList']);
            $this->assertArrayHasKey('quote', $response['moveLineItemToRequisitionList']);
            $this->assertArrayHasKey('uid', $response['moveLineItemToRequisitionList']['quote']);
            $this->assertEquals($quoteMask, $response['moveLineItemToRequisitionList']['quote']['uid']);
            $this->assertArrayHasKey('items', $response['moveLineItemToRequisitionList']['quote']);
            $this->assertEmpty($response['moveLineItemToRequisitionList']['quote']['items']);

            /** @var RequisitionListItemInterface[] $rqListItems */
            $rqListItems = $rqList->getItems();
            $this->assertEquals(1, count($rqListItems));
            foreach ($rqListItems as $rqListItem) {
                $this->assertEquals($item->getSku(), $rqListItem->getSku());
            }
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
            ['NQ_One_mask', 'item_one', null, null],
            ['NQ_One_mask', 'item_one', 'company_one', null],
            // Commented out the following variations due to B2B-4062
            //['NQ_One_mask', 'item_one', 'company_two', 'Could not find a quote with the specified UID.'],
            //['NQ_Two_mask', 'item_two', null, 'Could not find a quote with the specified UID.'],
            //['NQ_Two_mask', 'item_two', 'company_one', 'Could not find a quote with the specified UID.'],
            ['NQ_Two_mask', 'item_two', 'company_two', null],
        ];
    }

    /**
     * Test moving non-existing item to a requisition list.
     */
    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', 1),
        ConfigFixture('btob/website_configuration/requisition_list_active', 1),
    ]
    public function testMovingNonExistentItemToRequisitionList(): void
    {
        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $headers = ['Authorization' => 'Bearer ' . $customerToken];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The quote item does not exist.');

        $itemId = 12345; // Non-existing item id
        /** @var RequisitionListInterface $rqList */
        $rqList = FixtureManager::getStorage()->get('requisitionList');

        $input = [
            'quote_uid' => 'NQ_One_mask',
            'quote_item_uid' => base64_encode((string)$itemId),
            'requisition_list_uid' => base64_encode((string)$rqList->getId()),
        ];
        $this->graphQlMutation(
            $this->getMutation($input),
            [],
            '',
            $headers
        );
    }

    /**
     * Test missing various required parameters.
     *
     * @dataProvider getMissingInputData
     * @param string $key
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        ConfigFixture('btob/website_configuration/company_active', 1),
        ConfigFixture('btob/website_configuration/negotiablequote_active', 1),
        ConfigFixture('btob/website_configuration/requisition_list_active', 1),
    ]
    public function testMissingRequiredParameters(string $key): void
    {
        /** @var CustomerInterface $customer */
        $customer = FixtureManager::getStorage()->get('customer');
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $headers = ['Authorization' => 'Bearer ' . $customerToken];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Field MoveLineItemToRequisitionListInput.{$key} of required type ID! was not provided"
        );

        /** @var CartItemInterface $item */
        $item = FixtureManager::getStorage()->get('item_one');
        /** @var RequisitionListInterface $rqList */
        $rqList = FixtureManager::getStorage()->get('requisitionList');

        $input = [
            'quote_uid' => 'NQ_One_mask',
            'quote_item_uid' => base64_encode((string)$item->getItemId()),
            'requisition_list_uid' => base64_encode((string)$rqList->getId()),
        ];
        // Unset a required parameter
        unset($input[$key]);

        $this->graphQlMutation(
            $this->getMutation($input),
            [],
            '',
            $headers
        );
    }

    /**
     * Data provider for testMissingRequiredParameters.
     *
     * @return array
     */
    public function getMissingInputData(): array
    {
        return [
            ['quote_uid'],
            ['quote_item_uid'],
            ['requisition_list_uid'],
        ];
    }

    /**
     * Generates GraphQl mutation
     *
     * @param array $params
     * @return string
     */
    private function getMutation(array $params): string
    {
        $input = '';
        if (isset($params['quote_uid'])) {
            $input .= 'quote_uid: "'. $params['quote_uid'] . '" ';
        }
        if (isset($params['quote_item_uid'])) {
            $input .= 'quote_item_uid: "'. $params['quote_item_uid'] . '" ';
        }
        if (isset($params['requisition_list_uid'])) {
            $input .= 'requisition_list_uid: "'. $params['requisition_list_uid'] . '" ';
        }
        return <<<MUTATION
mutation {
  moveLineItemToRequisitionList(
    input: {
        {$input}
    }
  ) {
    quote {
      uid
      status
      items {
        id
      }
    }
  }
}
MUTATION;
    }
}
