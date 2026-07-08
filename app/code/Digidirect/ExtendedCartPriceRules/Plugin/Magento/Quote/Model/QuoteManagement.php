<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class QuoteManagement
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model
 */
class QuoteManagement
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Digidirect\ExtendedCartPriceRules\Helper\Data
     */
    protected $helper;

    /**
     * QuoteManagement constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Digidirect\ExtendedCartPriceRules\Helper\Data $helper
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Digidirect\ExtendedCartPriceRules\Helper\Data $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePlaceOrder(
        \Magento\Quote\Model\QuoteManagement $subject,
        $cartId,
        PaymentInterface $paymentMethod = null
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $allowedPaymentMethods = $this->helper->getAvailableMethods();
        if ((!$allowedPaymentMethods || in_array($quote->getPayment()->getMethod(), $allowedPaymentMethods))
            && !empty($quote->getAppliedRuleIds())
        ) {
            $extendData = $this->helper->getExtendRulesData($quote);
            $appliedRuleIds = explode(',', $quote->getAppliedRuleIds());

            foreach ($appliedRuleIds as $ruleId) {
                if ($extendData[$ruleId]['isEnableUnavailablePaymentMethods']) {
                    $message = $extendData[$ruleId]['messageForUnavailablePaymentMethod']
                        ?? __('Selected Payment Method Is Not Available For Your Order');

                    throw new LocalizedException($message);
                }
            }
        }

        return [$cartId, $paymentMethod];
    }
}
