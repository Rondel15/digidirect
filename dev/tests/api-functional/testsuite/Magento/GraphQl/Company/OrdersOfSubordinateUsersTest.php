<?php
/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
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
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQl\Company;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Company\Api\RoleManagementInterface;
use Magento\Company\Model\PermissionManagementInterface;
use Magento\Company\Model\ResourceModel\Permission\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class OrdersOfSubordinateUsersTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthenticationHeader;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var PermissionManagementInterface
     */
    private $permissionManagement;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RoleManagementInterface
     */
    private $roleManagement;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getCustomerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->order = $objectManager->create(OrderInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->permissionManagement = $objectManager->get(PermissionManagementInterface::class);
        $this->roleRepository = $objectManager->get(RoleRepositoryInterface::class);
        $this->collectionFactory = $objectManager->get(CollectionFactory::class);
        $this->roleManagement = $objectManager->get(RoleManagementInterface::class);
    }

    #[
        Config('btob/website_configuration/company_active', 1),
    ]
    /**
     * @magentoApiDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoApiDataFixture Magento/Company/_files/company_order.php
     */
    public function testUserCanViewSubordinateOrders() : void
    {
        $levelOneCustomer = $this->customerRepository->get('veronica.costello@example.com');
        $levelTwoCustomer = $this->customerRepository->get('alex.smith@example.com');

        $this->assignOrderToCustomer($levelTwoCustomer);

        $query = $this->getOrdersQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($levelOneCustomer->getEmail())
        );
        $this->assertEmpty($response['customer']['orders']['items']);
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($levelTwoCustomer->getEmail())
        );
        $this->assertNotEmpty($response['customer']['orders']['items']);

        $this->enableAllPermissionsForCustomerRole($levelOneCustomer);

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthenticationHeader->execute($levelOneCustomer->getEmail())
        );
        $this->assertNotEmpty($response['customer']['orders']['items']);
    }

    /**
     * @param CustomerInterface $customer
     * @return void
     */
    private function assignOrderToCustomer(CustomerInterface $customer) : void
    {
        $this->order->loadByIncrementId('100000001');
        $this->order->setCustomerEmail($customer->getEmail());
        $this->order->setCustomerId($customer->getId());
        $this->order->save();
    }

    /**
     * @param CustomerInterface $customer
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function enableAllPermissionsForCustomerRole(CustomerInterface $customer) : void
    {
        $defaultRole = $this->roleManagement->getCompanyDefaultRole(
            $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );
        $rolePermissions = $this->collectionFactory
            ->create()
            ->addFieldToFilter('role_id', ['eq' => $defaultRole->getId()])
            ->getColumnValues('resource_id');
        $defaultRole->setPermissions($this->permissionManagement->populatePermissions($rolePermissions));
        $this->roleRepository->save($defaultRole);
    }

    /**
     * Prepare query for
     *
     * @return string
     */
    private function getOrdersQuery() : string
    {
        $query = <<<QUERY
{
    customer {
        orders(
            pageSize: 20
        ) {
            items {
                increment_id
                order_date
                total {
                    grand_total {
                        value
                        currency
                    }
                }
                status
            }
        }
    }
}
QUERY;
        return $query;
    }
}
