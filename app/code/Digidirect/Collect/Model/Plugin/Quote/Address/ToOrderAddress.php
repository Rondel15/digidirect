<?php

namespace Digidirect\Collect\Model\Plugin\Quote\Address;

use Closure;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class ToOrderAddress
 */
class ToOrderAddress
{
    /**
     * CollectHelper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * AroundConvert constructor.
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     */
    public function __construct(\Digidirect\Collect\Helper\Data $collectHelper)
    {
        $this->_collectHelper = $collectHelper;
    }

    /**
     * AroundConvert
     *
     * @param Address\ToOrderAddress $subject
     * @param Closure $proceed
     * @param Address $quoteAddress
     * @param array $data
     * @return OrderAddressInterface
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        Closure $proceed,
        Address $quoteAddress,
        $data = []
    ) {
        /** @var $addressItem \Magento\Sales\Api\Data\OrderAddressInterface */
        $addressItem = $proceed($quoteAddress, $data);
        if ($this->_collectHelper->isCollectEnable()) {
            $addressItem->setIsCollect($quoteAddress->getIsCollect());
        }
        return $addressItem;
    }
}
