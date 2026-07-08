<?php

namespace Digidirect\Vii\Plugin\Magento\Checkout\Model;

use Digidirect\Vii\Service\Exeption\ServiceExeption;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Framework\Exception\CouldNotSaveException;

class PaymentInformationManagementPlugin extends AbstractPaymentInformationManagementPlugin
{
    /**
     * @param PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        try {
            $orderId = $proceed($cartId, $paymentMethod, $billingAddress);
        } catch (CouldNotSaveException $exception) {
            $previousException = $exception->getPrevious();
            if ($previousException instanceof ServiceExeption) {
                if ($this->serviceTransactionManagement->reversePreviousTransaction($cartId, null, true)) {
                    $this->setErrorMessage(
                        $previousException->getMessage(),
                        $this->serviceTransactionManagement->isProcessQueued()
                    );
                    return null;
                }
            }
            throw $exception;
        }
        return $orderId;
    }
}
