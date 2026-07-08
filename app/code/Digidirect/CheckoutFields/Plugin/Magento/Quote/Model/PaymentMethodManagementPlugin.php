<?php

namespace Digidirect\CheckoutFields\Plugin\Magento\Quote\Model;

use Digidirect\CheckoutFields\Api\QuoteFieldValueManagementInterface;
use Digidirect\CheckoutFields\Model\QuoteFieldValueFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentExtension;
use Magento\Quote\Api\Data\PaymentExtensionFactory;
use Magento\Quote\Api\Data\PaymentExtensionInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Class LoadHandlerPlugin
 *
 * @package Digidirect\CheckoutFields\Plugin\Magento\Quote\QuoteRepository
 */
class PaymentMethodManagementPlugin
{
    /**
     * @var PaymentExtensionFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var QuoteFieldValueFactory
     */
    protected $quoteFieldValueModel;

    /**
     * @var QuoteFieldValueManagementInterface
     */
    protected $quoteFieldValueManagement;

    /**
     * PaymentMethodManagementPlugin constructor.
     *
     * @param CartRepositoryInterface            $quoteRepository
     * @param PaymentExtensionFactory            $paymentExtensionFactory
     * @param QuoteFieldValueManagementInterface $quoteFieldValueManagement
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        PaymentExtensionFactory $paymentExtensionFactory,
        QuoteFieldValueManagementInterface $quoteFieldValueManagement
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->quoteFieldValueManagement = $quoteFieldValueManagement;
    }

    /**
     * Transfer extension attributes from payment method to quote
     *
     * @param PaymentMethodManagementInterface $subject
     * @param                                  $result
     * @param                                  $cartId
     * @param PaymentInterface                 $paymentMethod
     * @param AddressInterface|null            $billingAddress
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterSet(
        PaymentMethodManagementInterface $subject,
        $result,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        $paymentExtension = $this->getPaymentExtension($paymentMethod);
        if (!$paymentExtension || !$paymentExtension->getQuoteFieldValues()) {
            return $result;
        }

        $quoteFieldValues = $paymentExtension->getQuoteFieldValues();
        if (!$quoteFieldValues) {
            return $result;
        }

        $quoteExtension = $this->quoteFieldValueManagement->getQuoteExtension($quote);
        $quoteExtension->setQuoteFieldValues($quoteFieldValues);
        $quote->setExtensionAttributes($quoteExtension);

        $this->quoteFieldValueManagement->saveToQuoteFromExtensionAttributes($quote);

        return $result;
    }

    /**
     * @param PaymentInterface $paymentMethod
     *
     * @return null|PaymentExtension|PaymentExtensionInterface
     */
    protected function getPaymentExtension(PaymentInterface $paymentMethod)
    {
        $paymentExtension = $paymentMethod->getExtensionAttributes();
        if ($paymentExtension === null) {
            $paymentExtension = $this->paymentExtensionFactory->create();
        }

        return $paymentExtension;
    }
}
