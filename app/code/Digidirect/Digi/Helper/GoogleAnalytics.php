<?php
declare(strict_types=1);

namespace Digidirect\Digi\Helper;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;

/**
 * Class GoogleAnalytic
 * @package Digidirect\Digi\Helper
 */
class GoogleAnalytics extends \Magento\Framework\App\Helper\AbstractHelper
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
                'id'        => $order->getIncrementId(),
                'revenue'   => $order->getGrandTotal(),
            ];
        }
        return $this->jsonSerializer->serialize($purchaseData);
    }
}
