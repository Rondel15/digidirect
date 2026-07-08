<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrder\Controller\PurchaseOrder\PaymentDetails;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\PurchaseOrder\Api\Data\PurchaseOrderInterface;
use Magento\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Magento\PurchaseOrder\Controller\PurchaseOrder\PaymentDetailsAbstract;

/**
 * Controller test class for the purchase order payment details page.
 *
 * @see \Magento\PurchaseOrder\Controller\PurchaseOrder\View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 */
class PaymentDetailsPageTest extends PaymentDetailsAbstract
{
    /**
     * Test payment details page. Checking access, po quote total
     *
     * @param string $customerEmail
     * @param string $purchaseOrderCreatorEmail
     * @param string $orderStatus
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     * @throws LocalizedException
     * @dataProvider paymentDetailsPageDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPaymentDetailsPage(
        $customerEmail,
        $purchaseOrderCreatorEmail,
        $orderStatus,
        $expectedHttpResponseCode,
        $expectedRedirect
    ) {
        $this->setCompanyPurchaseOrderConfig('Magento', true);

        // Log in as the current user
        $purchaseOrderId = $this->getPurchaseOrderForCustomer($purchaseOrderCreatorEmail)->getEntityId();
        $currentUser = $this->objectManager->get(CustomerRegistry::class)->retrieveByEmail($customerEmail);

        $this->getRequest()->setParam('purchaseOrderId', $purchaseOrderId);
        $this->session->setCustomerAsLoggedIn($currentUser);

        $purchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($purchaseOrderId);
        $purchaseOrder->setStatus($orderStatus);
        $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->save($purchaseOrder);

        // Dispatch the request to the view payment details page for the desired purchase order
        $this->dispatch(self::URI . '/purchaseOrderId/' . $purchaseOrderId);

        // Perform assertions
        $this->assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());

        if ($expectedRedirect) {
            $this->assertRedirect($this->stringContains($expectedRedirect));
        } else {
            $this->assertStringContainsString(
                '"base_grand_total":' . $purchaseOrder->getSnapshotQuote()->getBaseGrandTotal(),
                $this->getResponse()->getBody()
            );
        }

        $this->session->logout();
    }

    /**
     * Data provider for various view action scenarios for company users.
     *
     * @return array
     */
    public static function paymentDetailsPageDataProvider()
    {
        return [
            'po_creator_with_po_pending_payment' => [
                'customerEmail' => 'john.doe@example.com',
                'purchaseOrderCreatorEmail' => 'john.doe@example.com',
                'orderStatus' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'expectedHttpResponseCode' => 200,
                'expectedRedirect' => '',
            ],
            'other_company_customer_with_po_pending_payment' => [
                'customerEmail' => 'veronica.costello@example.com',
                'purchaseOrderCreatorEmail' => 'john.doe@example.com',
                'orderStatus' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'company/accessdenied'
            ],
            'po_creator_with_po_approved' => [
                'customerEmail' => 'john.doe@example.com',
                'purchaseOrderCreatorEmail' => 'john.doe@example.com',
                'orderStatus' => PurchaseOrderInterface::STATUS_APPROVED,
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'noroute',
            ]
        ];
    }
}
