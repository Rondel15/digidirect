<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\RequisitionList;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for Requisition List pagination atest
 */
class RequisitionListPaginationTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test fetching customer Requisition list
     *
     * @magentoConfigFixture btob/website_configuration/requisition_list_active 1
     * @magentoApiDataFixture Magento/RequisitionList/_files/customer_for_requisition_list.php
     * @magentoApiDataFixture Magento/RequisitionList/_files/requisition_lists.php
     */
    public function testDefaultPagination(): void
    {
        $query = <<<QUERY
{
customer {
  requisition_lists {
    items {
      name
    }
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
  }
  }
}
QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderAuthentication());
        $this->assertEquals(4, $response['customer']['requisition_lists']['total_count']);
        $this->assertCount(4, $response['customer']['requisition_lists']['items']);
        $this->assertArrayHasKey('page_info', $response['customer']['requisition_lists']);
        $pageInfo = $response['customer']['requisition_lists']['page_info'];
        $this->assertEquals(1, $pageInfo['current_page']);
        $this->assertEquals(20, $pageInfo['page_size']);
        $this->assertEquals(1, $pageInfo['total_pages']);
    }

    /**
     * @magentoConfigFixture btob/website_configuration/requisition_list_active 1
     * @magentoApiDataFixture Magento/RequisitionList/_files/customer_for_requisition_list.php
     * @magentoApiDataFixture Magento/RequisitionList/_files/requisition_lists.php
     * @dataProvider paginationDataProvider
     * @param int $pageSize
     * @param int $currentPage
     * @param int $totalPages
     * @param int $itemsCount
     */
    public function testPagination(int $pageSize, int $currentPage, int $totalPages, int $itemsCount): void
    {
        $query = <<<QUERY
{
customer {
  requisition_lists(
    pageSize: $pageSize
    currentPage: $currentPage
  ) {
    items {
      name
    }
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
  }
 }
}
QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderAuthentication());
        $requisitionLists = $response['customer']['requisition_lists'];
        $this->assertEquals($itemsCount, $requisitionLists['total_count']);
        $this->assertCount($itemsCount, $requisitionLists['items']);
        $this->assertArrayHasKey('page_info', $requisitionLists);
        $this->assertEquals($currentPage, $requisitionLists['page_info']['current_page']);
        $this->assertEquals($pageSize, $requisitionLists['page_info']['page_size']);
        $this->assertEquals($totalPages, $requisitionLists['page_info']['total_pages']);
    }

    public static function paginationDataProvider(): array
    {
        return [
            [2, 1, 2, 2],
            [3, 2, 2, 1],
            [3, 3, 2, 0],
        ];
    }

    /**
     * @magentoConfigFixture btob/website_configuration/requisition_list_active 1
     * @magentoApiDataFixture Magento/RequisitionList/_files/customer_for_requisition_list.php
     */
    public function testCurrentPageZero()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currentPage value must be greater than 0.');

        $query = <<<QUERY
{
customer {
  requisition_lists(
    filter: {name: {match: "List"}}
    currentPage: 0
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
  }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getHeaderAuthentication());
    }

    /**
     * @magentoConfigFixture btob/website_configuration/requisition_list_active 1
     * @magentoApiDataFixture Magento/RequisitionList/_files/customer_for_requisition_list.php
     */
    public function testPageSizeZero()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currentPage value must be greater than 0.');

        $query = <<<QUERY
{
  customer {
  requisition_lists(
    filter: {name: {match: "List"}}
    currentPage: 0
  ) {
    total_count
    page_info {
      current_page
      page_size
      total_pages
    }
    items {
      name
    }
  }
  }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getHeaderAuthentication());
    }

    /**
     * Authentication header mapping
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    private function getHeaderAuthentication(
        string $username = 'customer@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
