<?php

namespace Digidirect\Collect\Model\Plugin\Quote;

use Digidirect\Collect\Model\Plugin\Paypal\Express\Checkout;

/**
 * Class Payment
 *
 * @package Digidirect\Collect\Model\Plugin\Quote
 */
class Payment
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Payment constructor.
     *
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * If billing address is null assign to it address that was gotten from PayPal
     * See bug #226390
     * Also @see \Digidirect\Collect\Model\Plugin\Paypal\Express\Checkout::aroundReturnFromPaypal
     * and @see \Digidirect\Collect\Model\Plugin\Paypal\Api\Nvp::aroundGetData
     *
     * @param \Magento\Payment\Model\InfoInterface $info
     * @param \Closure $proceed
     * @param null $key
     * @return array|null|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see \Magento\Payment\Model\Info::getAdditionalInformation
     */
    public function aroundGetAdditionalInformation(
        \Magento\Payment\Model\InfoInterface $info,
        \Closure $proceed,
        $key = null
    ) {
        $result = $proceed($key);

        if (\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON === $key
            && $this->coreRegistry->registry(Checkout::NEED_COPY_BILLING_ADDRESS)
        ) {
            return 1;
        }

        return $result;
    }
}
