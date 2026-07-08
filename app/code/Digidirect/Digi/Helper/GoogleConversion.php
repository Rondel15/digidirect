<?php
declare(strict_types=1);

namespace Digidirect\Digi\Helper;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;

/**
 * Class GoogleAnalytic
 * @package Digidirect\Digi\Helper
 */
class GoogleConversion extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * GoogleAnalytics constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param CheckoutSession $checkoutSession
     * @param Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CheckoutSession $checkoutSession,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->jsonSerializer = $jsonSerializer;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    public function getPurchaseData(): string
    {
        $purchaseData = [];
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order) {
            $purchaseData = [
                'event'        => 'OrderComplete',
                'value'   => $order->getGrandTotal(),
                'transaction_id' => $order->getIncrementId(),
                'currency' => 'AUD',
                'user_data' => array($order->getCustomerEmail())
            ];
        }

        return $this->jsonSerializer->serialize($purchaseData);
    }
}
