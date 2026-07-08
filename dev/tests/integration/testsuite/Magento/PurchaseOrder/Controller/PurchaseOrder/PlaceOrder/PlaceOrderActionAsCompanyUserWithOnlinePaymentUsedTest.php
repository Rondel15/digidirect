<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrder\Controller\PurchaseOrder\PlaceOrder;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PurchaseOrder\Api\Data\PurchaseOrderInterface;
use Magento\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Magento\PurchaseOrder\Controller\PurchaseOrder\PlaceOrderAbstract;

/**
 * Controller test class for the purchase order place order.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @see \Magento\PurchaseOrder\Controller\PurchaseOrder\PlaceOrder
 */
class PlaceOrderActionAsCompanyUserWithOnlinePaymentUsedTest extends PlaceOrderAbstract
{
    /**
     * @param string $currentUserEmail
     * @param string $createdByUserEmail
     * @param $usedPaymentMethod
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @dataProvider placeOrderActionAsCompanyUserWithOnlinePaymentDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPlaceOrderActionAsCompanyUserWithOnlinePaymentUsed(
        $currentUserEmail,
        $createdByUserEmail,
        $usedPaymentMethod,
        $expectedHttpResponseCode,
        $expectedRedirect
    ) {
        // Log in as the current user
        $currentUser = $this->objectManager->get(CustomerRepositoryInterface::class)->get($currentUserEmail);
        $this->session->loginById($currentUser->getId());

        // Dispatch the request
        $this->getRequest()->setMethod(Http::METHOD_POST)->setParam('payment_redirect', '1');
        $purchaseOrder = $this->getPurchaseOrderForCustomer($createdByUserEmail);
        $purchaseOrder->setPaymentMethod($usedPaymentMethod);
        $purchaseOrder->setStatus(PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT);
        $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->save($purchaseOrder);
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());

        // Perform assertions
        self::assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect(self::stringContains($expectedRedirect));

        $this->session->logout();
    }

    /**
     * Data provider for a place order action scenario when online payment is selected for company users.
     *
     * @return array
     */
    public static function placeOrderActionAsCompanyUserWithOnlinePaymentDataProvider()
    {
        return [
            'place_order_my_purchase_order' => [
                'currentUserEmail' => 'veronica.costello@example.com',
                'createdByUserEmail' => 'veronica.costello@example.com',
                'usedPaymentMethod' => 'paypal_express',
                'expectedHttpResponseCode' => 302,
                'expectedRedirect' => 'checkout/index/index/purchaseOrderId/'
            ]
        ];
    }
}
