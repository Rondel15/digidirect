<?php

namespace Digidirect\Vii\Plugin\Magento\Checkout\Model;

use Digidirect\Vii\Service\Config\Config;
use Digidirect\Vii\Service\Exeption\ServiceExeption;
use Digidirect\Vii\Model\ServiceTransactionManagement;
use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;

class GuestPaymentInformationManagementPlugin extends AbstractPaymentInformationManagementPlugin
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * GuestPaymentInformationManagementPlugin constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param ServiceTransactionManagement $serviceTransactionManagement
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ServiceTransactionManagement $serviceTransactionManagement,
        Config $config,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct(
            $cartRepository,
            $serviceTransactionManagement,
            $config,
            $messageManager,
            $storeManager
        );
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param GuestPaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return mixed
     * @throws \Exception
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        try {
            $orderId = $proceed($cartId, $email, $paymentMethod, $billingAddress);
        } catch (\Exception $e) {
            $previousException = $e->getPrevious();
            if ($previousException instanceof ServiceExeption) {
                $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
                $quote = $this->cartRepository->get($quoteIdMask->getQuoteId());
                if ($this->serviceTransactionManagement->reversePreviousTransaction($quote->getId(), null, true)) {
                    $this->setErrorMessage(
                        $previousException->getMessage(),
                        $this->serviceTransactionManagement->isProcessQueued()
                    );
                    return null;
                }
            }
            throw $e;
        }
        return $orderId;
    }
}
