<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company;

use Laminas\Http\Headers;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Test\Fixture\AssignCompany;
use Magento\Company\Test\Fixture\Company;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerGridCollection;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\User\Test\Fixture\User;

class CustomerGridCollectionTest extends AbstractController
{
    /**
     * @var CustomerGridCollection
     */
    private $customerGridCollection;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $indexerRegistry = Bootstrap::getObjectManager()->create(IndexerRegistry::class);
        $indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();

        $this->customerGridCollection = Bootstrap::getObjectManager()->create(CustomerGridCollection::class);
        $this->url = Bootstrap::getObjectManager()->create(UrlInterface::class);

        parent::setUp();
    }

    /**
     * Test backoffice customer grid provides total result count when filtering by customer type (e.g. company admin,
     * company user, regular customer)
     *
     * Given one storefront non-company customers, two company admins,
     * and a company user assigned to 2 different companies
     * When the backoffice customer grid is filtered by customer type of company admin
     * Then 2 results are returned
     * When the backoffice customer grid is filtered by customer type of company user
     * Then 2 results are returned
     * When the backoffice customer grid is filtered by customer type of individual (non-company) user
     * Then one results is returned
     *
     * @param int $customerType
     * @param int $expectedCount
     * @dataProvider getTotalCountDataProvider
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_b'),
        DataFixture(CustomerFixture::class, as: 'customer_ab'),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
    ]
    public function testGetCustomerCountByCustomerType(int $customerType, int $expectedCount)
    {
        $this->customerGridCollection->addFieldToFilter('customer_type', $customerType);
        $count = $this->customerGridCollection->getTotalCount();
        $this->assertEquals($expectedCount, $count);
    }

    /**
     * @return array
     */
    public static function getTotalCountDataProvider(): array
    {
        return [
            [
                CompanyCustomerInterface::TYPE_COMPANY_ADMIN,
                2,
            ],
            [
                CompanyCustomerInterface::TYPE_COMPANY_USER,
                2,
            ],
            [
                CompanyCustomerInterface::TYPE_INDIVIDUAL_USER,
                1,
            ],
        ];
    }

    /**
     * Test backoffice customer grid can be filtered by customer type (e.g. company admin, company user, regular
     * customer)
     *
     * Given one storefront non-company customers, two company admins,
     * and a company user assigned to 2 different companies
     * When the backoffice customer grid is filtered by customer type of company admin
     * Then 2 results are returned
     * And that result's email is the 2 company admin customer's email
     * When the backoffice customer grid is filtered by customer type of company user
     * Then 2 results are returned
     * And that result's email is the 2 company user customer's email
     * When the backoffice customer grid is filtered by customer type of individual (non-company) user
     * Then 1 result is returned
     * And the results' emails are the 1 non-company customers
     *
     * @param int $customerType
     * @param array $expectedEmails
     * @dataProvider getItemsDataProvider
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer@email.com',
            ],
            'customer'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_a@email.com',
            ],
            'customer_a'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_b@email.com',
            ],
            'customer_b'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_ab@email.com',
            ],
            'customer_ab'
        ),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
    ]
    public function testGetCustomersByCustomerType(int $customerType, array $expectedEmails)
    {
        $this->customerGridCollection->addFieldToFilter('customer_type', $customerType);
        $items = $this->customerGridCollection->getItems();
        $emails = [];
        foreach ($items as $item) {
            $emails[] = $item->getCustomAttribute('email')->getValue();
        }
        $this->assertSame($expectedEmails, $emails);
    }

    /**
     * @return array
     */
    public static function getItemsDataProvider(): array
    {
        return [
            [
                CompanyCustomerInterface::TYPE_COMPANY_ADMIN,
                ['customer_a@email.com', 'customer_b@email.com'],
            ],
            [
                CompanyCustomerInterface::TYPE_COMPANY_USER,
                ['customer_ab@email.com', 'customer_ab@email.com'],
            ],
            [
                CompanyCustomerInterface::TYPE_INDIVIDUAL_USER,
                ['customer@email.com'],
            ],
        ];
    }

    /**
     * Test backoffice customer grid can be filtered by Sales Representative usernames
     *
     * Given Company Admin customers with a unique Sales Representative for each customer
     * and one customer assigned to multiple companies
     * When the backoffice customer grid is filtered by a Sales Representative's username
     * Then the associated Company Admin customer will appear
     * When the backoffice customer grid is filtered by a nonexistent Sales Representative's username
     * Then an empty result set is returned
     *
     * @param string $salesRepresentativeUsername
     * @param array $expectedCompanyAdminEmail
     * @dataProvider getCustomersBySalesRepresentativeUsernameDataProvider
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_a@email.com',
            ],
            'customer_a'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_b@email.com',
            ],
            'customer_b'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_c@email.com',
            ],
            'customer_c'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_ab@email.com',
            ],
            'customer_ab'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'user_a',
            ],
            'user_a'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'user_b',
            ],
            'user_b'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'user_c',
            ],
            'user_c'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user_a.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user_b.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user_c.id$',
                'super_user_id' => '$customer_c.id$'
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
    ]
    public function testGetCustomersBySalesRepresentativeUsername(
        string $salesRepresentativeUsername,
        array $expectedCompanyAdminEmail
    ) {
        $this->customerGridCollection->addFieldToFilter('sales_representative_username', $salesRepresentativeUsername);
        $customerResults = $this->customerGridCollection->getItems();
        $this->assertCount(count($expectedCompanyAdminEmail), $customerResults);
        $emails = [];
        foreach ($customerResults as $item) {
            $emails[] = $item->getCustomAttribute('email')->getValue();
        }
        $this->assertSame($expectedCompanyAdminEmail, $emails);
    }

    /**
     * @return array
     */
    public static function getCustomersBySalesRepresentativeUsernameDataProvider(): array
    {
        return [
            [
                'user_a',
                ['customer_a@email.com', 'customer_ab@email.com']
            ],
            [
                'user_b',
                ['customer_b@email.com', 'customer_ab@email.com']
            ],
            [
                'user_c',
                ['customer_c@email.com']
            ],
            [
                'Nobody admin',
                []
            ]
        ];
    }

    /**
     * Test backend customer grid can be sorted by Sales Representative username in alphabetical order
     *
     * Given multiple Sales Representatives whose usernames are not in alphabetical order
     * When the backoffice customer grid is sorted by Sales Representative username ascending
     * Then the associated customer results will be sorted by Sales Representative username alphabetically ascending
     *
     * @param string $sortOrder
     * @param array $expectedSalesRepresentativeUsernames
     * @dataProvider sortingBySalesRepresentativeUsernameDataProvider
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(CustomerFixture::class, as: 'customer_a'),
        DataFixture(CustomerFixture::class, as: 'customer_b'),
        DataFixture(CustomerFixture::class, as: 'customer_c'),
        DataFixture(CustomerFixture::class, as: 'customer_ab'),
        DataFixture(
            User::class,
            [
                'username' => 'user_a',
            ],
            'user_a'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'user_b',
            ],
            'user_b'
        ),
        DataFixture(
            User::class,
            [
                'username' => 'user_c',
            ],
            'user_c'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user_a.id$',
                'super_user_id' => '$customer_a.id$',

            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user_b.id$',
                'super_user_id' => '$customer_b.id$'
            ],
            'company_b'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user_c.id$',
                'super_user_id' => '$customer_c.id$'
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
    ]
    public function testSortingBySalesRepresentativeUsername(
        string $sortOrder,
        array $expectedSalesRepresentativeUsernames
    ) {
        $this->customerGridCollection->setOrder(
            'sales_representative_username',
            $sortOrder
        );

        $customerResults = $this->customerGridCollection->getItems();

        $salesRepresentativeUsernames = array_map(function ($customerResult) {
            return $customerResult->getCustomAttribute('sales_representative_username')->getValue();
        }, $customerResults);

        $salesRepresentativeUsernames = array_values(array_filter($salesRepresentativeUsernames));

        $this->assertEquals($expectedSalesRepresentativeUsernames, $salesRepresentativeUsernames);
    }

    /**
     * @return array
     */
    public static function sortingBySalesRepresentativeUsernameDataProvider()
    {
        return [
            [
                CustomerGridCollection::SORT_ORDER_ASC,
                [
                    'user_a',
                    'user_a',
                    'user_b',
                    'user_b',
                    'user_c',
                ]
            ],
            [
                CustomerGridCollection::SORT_ORDER_DESC,
                [
                    'user_c',
                    'user_b',
                    'user_b',
                    'user_a',
                    'user_a',
                ]
            ]
        ];
    }

    /**
     * Test backoffice customer grid can be filtered by company name correctly using exact, partial, and non-existent
     * matches
     *
     * Given Company Admin customers with a unique Company Name for each customer
     * and one customer assigned to multiple companies
     * When the backoffice customer grid is filtered by a Company Name
     * Then the associated Company Admin customer will appear
     * When the backoffice customer grid is filtered by partial Company Name
     * Then the associated Company Admin customer will still appear
     * When the backoffice customer grid is filtered by a nonexistent Company Name
     * Then an empty result set is returned
     *
     * @param string $companyName
     * @param array $expectedCompanyAdminEmail
     * @dataProvider getCustomersByCompanyNameDataProvider
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer@email.com',
            ],
            'customer'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_a@email.com',
            ],
            'customer_a'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_b@email.com',
            ],
            'customer_b'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_c@email.com',
            ],
            'customer_c'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_ab@email.com',
            ],
            'customer_ab'
        ),
        DataFixture(User::class, as: 'user'),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_a.id$',
                'company_name' => 'Test Company A Name',
            ],
            'company_a'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_b.id$',
                'company_name' => 'Test Company B Name',
            ],
            'company_b'
        ),
        DataFixture(
            Company::class,
            [
                'sales_representative_id' => '$user.id$',
                'super_user_id' => '$customer_c.id$',
                'company_name' => 'Test Company C Name',
            ],
            'company_c'
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_a.id$',
                'customer_id' => '$customer_ab.id$'
            ]
        ),
        DataFixture(
            AssignCompany::class,
            [
                'company_id' => '$company_b.id$',
                'customer_id' => '$customer_ab.id$',
            ]
        ),
    ]
    public function testGetCustomersByCompanyName(
        string $companyName,
        array $expectedCompanyAdminEmail
    ) {
        $this->_objectManager->get(Auth::class)->login(
            \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        $params = [
            'namespace' => 'customer_listing',
            'filters[company_name]' => $companyName,
            'isAjax' => 1,
            UrlInterface::SECRET_KEY_PARAM_NAME => $this->url->getSecretKey('mui', 'index', 'render'),
        ];

        $this->getRequest()->setHeaders(Headers::fromString('Accept: application/json'));

        $this->dispatch('backend/mui/index/render?' . http_build_query($params));

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $responseBody = json_decode($this->getResponse()->getBody(), true);

        $customerResults = $responseBody['items'];
        $this->assertCount(count($expectedCompanyAdminEmail), $customerResults);

        $emails = [];
        foreach ($customerResults as $item) {
            $emails[] = $item['email'];
        }
        $this->assertSame($expectedCompanyAdminEmail, $emails);
    }

    /**
     * @return array
     */
    public static function getCustomersByCompanyNameDataProvider(): array
    {
        return [
            [
                'Test Company A Name',
                ['customer_a@email.com', 'customer_ab@email.com']
            ],
            [
                'TEST COMPANY B NAME',
                ['customer_b@email.com', 'customer_ab@email.com']
            ],
            [
                'C Name',
                ['customer_c@email.com']
            ],
            [
                'Nobody Company',
                []
            ]
        ];
    }

    /**
     * Test backoffice customer grid can be filtered by customer group
     *
     * @param int $customerGroupId
     * @param array $expectedEmails
     * @dataProvider getItemsFilteredByCustomerGroup
     */
    #[
        AppArea('adminhtml'),
        DbIsolation(false),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer@email.com',
                'group_id' => 3
            ],
            'customer'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_a@email.com',
                'group_id' => 1
            ],
            'customer_a'
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer_b@email.com',
                'group_id' => 1
            ],
            'customer_b'
        )
    ]
    public function testGetCustomersByCustomerGroup(int $customerGroupId, array $expectedEmails)
    {
        $this->customerGridCollection->addFieldToFilter('group_id', $customerGroupId);
        $items = $this->customerGridCollection->getItems();
        $emails = [];
        foreach ($items as $item) {
            $emails[] = $item->getCustomAttribute('email')->getValue();
        }
        $this->assertSame($expectedEmails, $emails);
    }

    /**
     * @return array
     */
    public function getItemsFilteredByCustomerGroup(): array
    {
        return [
            [
                1,
                ['customer_a@email.com', 'customer_b@email.com'],
            ],
            [
                3,
                ['customer@email.com']
            ]
        ];
    }
}
