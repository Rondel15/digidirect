<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PurchaseOrderRule;

use Exception;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Role;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\PurchaseOrder\Test\Encoder;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\User\Test\Fixture\User;
use Magento\PurchaseOrder\Test\GetCustomerHeaders;

#[
    Config('btob/website_configuration/company_active', 1),
    Config('btob/website_configuration/purchaseorder_enabled', 1),
    DataFixture(Customer::class, as: 'company_admin'),
    DataFixture(Customer::class, as: 'company_buyer'),
    DataFixture(User::class, as: 'user'),
    DataFixture(
        Company::class,
        [
            'super_user_id' => '$company_admin.entity_id$'
        ],
        'company'
    ),

    DataFixture(
        Role::class,
        [
            'company_id' => '$company.entity_id$',
            'role_name' => 'Company Administrator'
        ],
        'role_administrator'
    ),
    DataFixture(
        Role::class,
        [
            'company_id' => '$company.entity_id$',
            'role_name' => 'Purchaser\'s Manager'
        ],
        'role_purchase_manager'
    )
]
class ValidateCreatePurchaseRuleTest extends GraphQlAbstract
{

    private const QUERY = <<<QUERY
    mutation {
        createPurchaseOrderApprovalRule(input: {
            name: "%s"
            description: "%s"
            applies_to: "%s"
            status: %s
            approvers: ["%s"]
            condition: {
              attribute: %s
              operator: %s
              quantity: %s
            }
        }) {
            uid
            name
            __typename
           }
      }
    QUERY;

    private const RULE_ARGS = [
        [
            'name' => 'Rule 001',
            'description' => 'Rule 001 description',
            'status' => 'ENABLED',
            'condition_attribute' => 'NUMBER_OF_SKUS',
            'condition_operators' => 'MORE_THAN',
            'amount_value' => '10'
        ]
    ];

    /**
     * @var GetCustomerHeaders
     */
    private $getCustomerHeaders;

    /**
     * @var Uid
     */
    private $uid;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * Set up the objects used across all scenarios
     *
     * @return void
     */
    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->encoder = $objectManager->get(Encoder::class);
        $this->uid = $objectManager->get(Uid::class);
        $this->getCustomerHeaders = $objectManager->get(GetCustomerHeaders::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
        Config('btob/website_configuration/purchaseorder_enabled', 1)
    ]
    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function testCreatePurchaseRuleForQuantity()
    {
        $role = DataFixtureStorageManager::getStorage()->get('role_administrator');
        $response = $this->graphQlMutation(
            sprintf(
                self::QUERY,
                ValidateCreatePurchaseRuleTest::RULE_ARGS[0]['name'],
                ValidateCreatePurchaseRuleTest::RULE_ARGS[0]['description'],
                $this->encoder->encode($role->getRoleId()),
                ValidateCreatePurchaseRuleTest::RULE_ARGS[0]['status'],
                $this->encoder->encode($role->getRoleId()),
                ValidateCreatePurchaseRuleTest::RULE_ARGS[0]['condition_attribute'],
                ValidateCreatePurchaseRuleTest::RULE_ARGS[0]['condition_operators'],
                ValidateCreatePurchaseRuleTest::RULE_ARGS[0]['amount_value']
            ),
            [],
            '',
            $this->getCustomerHeaders->execute('company_admin')
        );
        $this->assertArrayHasKey('createPurchaseOrderApprovalRule', $response);
    }
}
