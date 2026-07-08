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

namespace Magento\GraphQl\ConfigurableRequisitionList;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Test\Fixture\RequisitionList;
use Magento\RequisitionList\Test\Fixture\AddProductToRequisitionList;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for getting configurable product Requisition List
 */
class GetConfigurableProductRequisitionListTest extends GraphQlAbstract
{

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    #[
        Config('btob/website_configuration/requisition_list_active', 1),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(ConfigurableProductFixture::class, ['_options' => ['$attr$'], '_links' => ['$p1$']], as: 'cp1'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(RequisitionList::class, ['customer_id' => '$customer.id$'], as: 'requisitionList'),
        DataFixture(
            AddProductToRequisitionList::class,
            [
                'requisition_list_id' => '$requisitionList.id$',
                'sku' => '$cp1.sku$',
                'qty' => 2,
                'options' => [
                    'info_buyRequest' => [
                        'product' => '$cp1.id$',
                        'selected_configurable_option' => '$cp1.id$',
                        'item' => '$cp1.id$',
                        'qty' => 2,
                        'super_attribute' => [
                            10 => 12
                        ]
                    ]
                ]

            ]
        ),
    ]
    public function testCustomerRequisitionListRunsWithoutException(): void
    {
        /** @var RequisitionListInterface $customer */
        $requisitionList = DataFixtureStorageManager::getStorage()->get('requisitionList');
        $requisitionListId = base64_encode($requisitionList->getId());
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $query = $this->getQuery(1, 1, $requisitionListId);

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap($customer->getEmail()));
        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('requisition_lists', $response['customer']);
        $this->assertArrayHasKey(
            'configurable_options',
            $response['customer']['requisition_lists']['items'][0]['items']['items'][0]
        );
    }

    /**
     * Get query.
     *
     * @param int $pageSize
     * @param int $currentPage
     * @param string $id
     * @return string
     */
    private function getQuery(int $pageSize, int $currentPage, string $id): string
    {
        return <<<QUERY
{
    customer
    {
        requisition_lists(filter: {uids: {eq: "$id"}})
        {
          items
          {
            items(pageSize: $pageSize, currentPage: $currentPage)
            {
              items
                {
                    ... on ConfigurableRequisitionListItem
                    {
                      configurable_options
                        {
                            value_id
                            option_label
                            id
                            value_label
                            configurable_product_option_uid
                            configurable_product_option_value_uid
                        }
                    }
                }
            }
          }
        }
    }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
