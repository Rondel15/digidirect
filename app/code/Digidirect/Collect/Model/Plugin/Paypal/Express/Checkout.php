<?php

namespace Digidirect\Collect\Model\Plugin\Paypal\Express;

use Digidirect\Collect\Helper\Data as CollectHelper;

/**
 * Class Checkout
 *
 * @package Digidirect\Collect\Model\Plugin\Paypal\Express
 */
class Checkout
{
    const NEED_COPY_BILLING_ADDRESS = 'digidirect_collect_need_copy_billing_address';

    /**
     * @var CollectHelper
     */
    protected $collectHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Checkout constructor.
     *
     * @param CollectHelper $collectHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        CollectHelper $collectHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->collectHelper = $collectHelper;
        $this->checkoutSession = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * If billing address is null assign to it address that was gotten from PayPal
     * See bug #226390
     *
     * @param \Magento\Paypal\Model\Express\Checkout $checkout
     * @param \Closure $proceed
     * @param string $token
     * @return mixed|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see \Digidirect\Collect\Model\Plugin\Quote\Payment::aroundGetAdditionalInformation
     * @see \Digidirect\Collect\Model\Plugin\Paypal\Api\Nvp::aroundGetData
     * @see \Magento\Paypal\Model\Express\Checkout::returnFromPaypal
     */
    public function aroundReturnFromPaypal(
        \Magento\Paypal\Model\Express\Checkout $checkout,
        \Closure $proceed,
        $token
    ) {
        $quote = $this->checkoutSession->getQuote();
        $needCopyBillingAddress = $this->collectHelper->isCollectEnable()
            && $this->collectHelper->isFullVariation()
            && $this->collectHelper->hasQuoteCollectShippingMethod($quote)
            && !$quote->getBillingAddress()->getCountryId();

        if ($needCopyBillingAddress) {
            $this->coreRegistry->register(self::NEED_COPY_BILLING_ADDRESS, true);
        }

        $result = $proceed($token);

        if ($needCopyBillingAddress) {
            $this->coreRegistry->unregister(self::NEED_COPY_BILLING_ADDRESS);
        }

        return $result;
    }
}
