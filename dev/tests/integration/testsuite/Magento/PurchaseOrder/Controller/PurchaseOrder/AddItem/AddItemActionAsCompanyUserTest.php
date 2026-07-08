<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrder\Controller\PurchaseOrder\AddItem;

use Magento\Company\Api\Data\PermissionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PurchaseOrder\Controller\PurchaseOrder\AddItemAbstract;

/**
 * Controller test class for adding purchase order items to the shopping cart.
 *
 * @see \Magento\PurchaseOrder\Controller\PurchaseOrder\AddItem
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 */
class AddItemActionAsCompanyUserTest extends AddItemAbstract
{
    /**
     * Test that a company user has the proper access to add purchase order items to the shopping cart action.
     *
     * This is based on various configuration/permission settings as well as the company hierarchy.
     *
     * @dataProvider addItemActionAsCompanyUserDataProvider
     * @param string $currentUserEmail
     * @param string $createdByUserEmail
     * @param int $companyPurchaseOrdersConfigEnabled
     * @param string[] $viewPurchaseOrdersPermissions
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     * @param string $purchaseOrderId
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testAddItemActionAsCompanyUser(
        $currentUserEmail,
        $createdByUserEmail,
        $companyPurchaseOrdersConfigEnabled,
        $viewPurchaseOrdersPermissions,
        $expectedHttpResponseCode,
        $expectedRedirect,
        $purchaseOrderId = ''
    ) {
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $session = $this->objectManager->get(Session::class);

        // Enable/Disable purchase order functionality for the specific company
        $this->setCompanyPurchaseOrderConfig('Magento', (bool) $companyPurchaseOrdersConfigEnabled);

        foreach ($viewPurchaseOrdersPermissions as $viewPurchaseOrdersPermission) {
            $this->setCompanyRolePermission(
                'Magento',
                'Default User',
                $viewPurchaseOrdersPermission,
                PermissionInterface::ALLOW_PERMISSION
            );
        }

        $deniedViewPurchaseOrdersPermissions = array_diff(
            [
                'Magento_PurchaseOrder::view_purchase_orders',
                'Magento_PurchaseOrder::view_purchase_orders_for_subordinates',
                'Magento_PurchaseOrder::view_purchase_orders_for_company',
            ],
            $viewPurchaseOrdersPermissions
        );
        foreach ($deniedViewPurchaseOrdersPermissions as $deniedViewPurchaseOrdersPermission) {
            $this->setCompanyRolePermission(
                'Magento',
                'Default User',
                $deniedViewPurchaseOrdersPermission,
                PermissionInterface::DENY_PERMISSION
            );
        };

        // Log in as the current user
        $currentUser = $customerRepository->get($currentUserEmail);
        $session->loginById($currentUser->getId());

        // Dispatch the request to add items of the desired purchase order
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $purchaseOrderId = $purchaseOrderId ?: $this->getPurchaseOrderForCustomer($createdByUserEmail)->getEntityId();
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrderId);

        // Perform assertions
        self::assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());

        if ($expectedRedirect) {
            self::assertRedirect($this->stringContains($expectedRedirect));
        }

        $session->logout();
    }

    /**
     * Data provider for various additem action scenarios for company users.
     *
     * @return array
     */
    public static function addItemActionAsCompanyUserDataProvider()
    {
        return [
            'add_my_purchase_order_item' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'veronica.costello@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => ['Magento_PurchaseOrder::view_purchase_orders'],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'checkout/cart',
            ],
            'add_my_purchase_order_item_without_view_permission' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'veronica.costello@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => [],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'company/accessdenied',
            ],
            'add_subordinate_purchase_order_item_no_view_subordinate_permission' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'alex.smith@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => ['Magento_PurchaseOrder::view_purchase_orders'],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => '',
            ],
            'add_subordinate_purchase_order_item_with_view_subordinate_permission' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'alex.smith@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => [
                    'Magento_PurchaseOrder::view_purchase_orders',
                    'Magento_PurchaseOrder::view_purchase_orders_for_subordinates',
                ],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'checkout/cart',
            ],
            'add_superior_purchase_order_item' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'john.doe@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => ['Magento_PurchaseOrder::view_purchase_orders'],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'company/accessdenied',
            ],
            'add_superior_purchase_order_item_with_view_company_permission' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'john.doe@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => [
                    'Magento_PurchaseOrder::view_purchase_orders',
                    'Magento_PurchaseOrder::view_purchase_orders_for_company',
                ],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'checkout/cart',
            ],
            'add_subordinate_purchase_order_item_with_view_company_permission' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'alex.smith@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => [
                    'Magento_PurchaseOrder::view_purchase_orders',
                    'Magento_PurchaseOrder::view_purchase_orders_for_company',
                ],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'checkout/cart',
            ],
            'company_admin_add_purchase_order_item' => [
                'currentUserEmail' => 'john.doe@example.com',
                'createdByUserEmail' => 'alex.smith@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => [],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'checkout/cart',
            ],
            'company_admin_add_!existing_purchase_order_item' => [
                'currentUserEmail' => 'john.doe@example.com',
                'createdByUserEmail' => 'alex.smith@example.com',
                'companyPurchaseOrdersConfigEnabled' => 1,
                'viewPurchaseOrdersPermissions' => [],
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'company/accessdenied',
                'purchaseOrderId' => '5000',
            ]
        ];
    }
}
