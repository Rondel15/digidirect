<?php

namespace Digidirect\Collect\Model\Plugin\Paypal\Api;

use Digidirect\Collect\Helper\Data as CollectHelper;
use Digidirect\Collect\Model\Plugin\Paypal\Express\Checkout;

class Nvp
{
    const EXPORTED_SHIPPING_ADDRESS = 'exported_shipping_address';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var CollectHelper
     */
    private $collectHelper;

    /**
     * Nvp constructor.
     *
     * @param CollectHelper $collectHelper
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        CollectHelper $collectHelper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->collectHelper = $collectHelper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Set suppress shipping before @see \Magento\Paypal\Model\Api\Nvp::callSetExpressCheckout()
     *
     * @param \Magento\Paypal\Model\Api\Nvp $subject
     * @return void
     */
    public function beforeCallSetExpressCheckout(\Magento\Paypal\Model\Api\Nvp $subject)
    {
        $this->suppressShipping($subject);
    }

    /**
     * Set suppress shipping before @see \Magento\Paypal\Model\Api\Nvp::callDoExpressCheckoutPayment()
     *
     * @param \Magento\Paypal\Model\Api\Nvp $subject
     * @return void
     */
    public function beforeCallDoExpressCheckoutPayment(\Magento\Paypal\Model\Api\Nvp $subject)
    {
        $this->suppressShipping($subject);
    }

    /**
     * Work in complex with @see \Digidirect\Collect\Model\Plugin\Quote\Payment::aroundGetAdditionalInformation
     * and @see \Digidirect\Collect\Model\Plugin\Paypal\Express\Checkout::aroundReturnFromPaypal
     * It need for skipping assign null for shipping address fields
     * See bug #226390
     *
     * @param \Magento\Paypal\Model\Api\Nvp $subject
     * @param \Closure $closure
     * @param string $key
     * @param null $index
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @see \Magento\Paypal\Model\Api\Nvp::getData
     */
    public function aroundGetData(
        \Magento\Paypal\Model\Api\Nvp $subject,
        \Closure $closure,
        $key = '',
        $index = null
    ) {
        $result = $closure($key, $index);

        if (self::EXPORTED_SHIPPING_ADDRESS === $key
            && $this->coreRegistry->registry(Checkout::NEED_COPY_BILLING_ADDRESS)
        ) {
            return null;
        }

        return $result;
    }

    /**
     * Set suppress shipping if it is single variant of shipping
     *
     * @param \Magento\Paypal\Model\Api\Nvp $subject
     * @return void
     */
    protected function suppressShipping(\Magento\Paypal\Model\Api\Nvp $subject)
    {
        if ($this->collectHelper->isCollectEnable() && $this->collectHelper->isSingleVariation()
            && $this->collectHelper->hasQuoteCollectShippingMethod($subject->getQuote())
        ) {
            $subject->setSuppressShipping(true);
        }
    }
}
