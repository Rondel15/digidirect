<?php

namespace Digidirect\FreeGift\Plugin\Magento\Sales\Model\Order\Email;

use Magento\Sales\Model\Order\Email\SenderBuilder as OriginalSenderBuilder;
use Magento\Framework\Registry;
use Digidirect\FreeGift\Helper\Data as DataHelper;

class SenderBuilder
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Collection constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->_registry = $registry;
    }

    /**
     * @param OriginalSenderBuilder $subject
     * @param \Closure $proceed
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSend(OriginalSenderBuilder $subject, \Closure $proceed)
    {
        $flag = DataHelper::REGISTRY_HIDE_HIDDEN_FREE_GIFT_ORDER_ITEMS;
        $flagValue = $this->_registry->registry($flag);

        $this->_registry->unregister($flag);
        $this->_registry->register($flag, true, true);

        $data = $proceed();

        $this->_registry->unregister($flag);
        $this->_registry->register($flag, $flagValue, true);
        return $data;
    }
}
