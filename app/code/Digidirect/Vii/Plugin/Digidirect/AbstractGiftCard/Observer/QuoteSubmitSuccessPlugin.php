<?php

namespace Digidirect\Vii\Plugin\Digidirect\AbstractGiftCard\Observer;

use Digidirect\AbstractGiftCard\Observer\QuoteSubmitSuccess as Subject;
use Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface;
use Digidirect\AbstractGiftCard\Helper\Data;
use Digidirect\Vii\Service\Config\Config;
use Magento\Sales\Model\Order;

class QuoteSubmitSuccessPlugin
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * QuoteSubmitSuccessPlugin constructor.
     * @param Config $config
     * @param Data $helper
     */
    public function __construct(Config $config, Data $helper)
    {
        $this->config = $config;
    }

    /**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param Order $order
     * @param AbstractGiftCardEntityInterface $abstractGiftCard
     * @param array $giftCard
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAcceptAvailable(
        Subject $subject,
        \Closure $proceed,
        $order,
        $abstractGiftCard,
        $giftCard
    ) {
        if (!$this->config->isActive($order->getStoreId()) || $abstractGiftCard->getServiceCode() != 'vii') {
            return $proceed($order, $abstractGiftCard, $giftCard);
        }
        return $this->config->isAcceptAvailable($order);
    }
}
