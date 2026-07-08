<?php
namespace Digidirect\Collect\Block\Plugin\Adminhtml\Order\View;

use Magento\Sales\Model\Order\Address;

/**
 * Class Info
 *
 * @package Digidirect\Collect\Block\Plugin\Adminhtml\Order\View
 */
class Info
{
    /**
     * @var \Digidirect\Collect\Model\OrderStoreLocatorInfo
     */
    protected $orderStoreLocatorInfo;

    /**
     * AroundGetItemData constructor.
     *
     * @param \Digidirect\Collect\Model\OrderStoreLocatorInfo $orderStoreLocatorInfo
     */
    public function __construct(
        \Digidirect\Collect\Model\OrderStoreLocatorInfo $orderStoreLocatorInfo
    ) {
        $this->orderStoreLocatorInfo = $orderStoreLocatorInfo;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param \Closure $proceed
     * @param Address $address
     * @return null|string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetFormattedAddress(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject,
        \Closure $proceed,
        Address $address
    ) {
        if ($address->getAddressType() == 'shipping'
            && $storeLocatorInfo = $this->getStoreLocatorInfoByOrder($address->getOrder())
        ) {
            return __('Store ID: %1', nl2br($storeLocatorInfo));
        }

        return $proceed($address);
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param \Closure $proceed
     * @param Address $address
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetAddressEditLink(
        \Magento\Sales\Block\Adminhtml\Order\View\Info $subject,
        \Closure $proceed,
        Address $address
    ) {
        if ($address->getAddressType() == 'shipping'
            && $storeLocatorInfo = $this->getStoreLocatorInfoByOrder($address->getOrder())
        ) {
            return '';
        }

        return $proceed($address);
    }

    /**
     * Get Store by Id
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @throws \Exception
     */
    public function getStoreLocatorInfoByOrder($order)
    {
        if ($storeLocatorItem = $this->getStoreLocatorItemByOrder($order)) {
            return nl2br(
                $storeLocatorItem->getId() . "\n" .
                $storeLocatorItem->getName() . "\n" .
                $storeLocatorItem->getAddress()
            );
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool|\Digidirect\Collect\Api\Data\CollectPlaceInterface
     * @throws \Exception
     */
    public function getStoreLocatorItemByOrder($order)
    {
        return $this->orderStoreLocatorInfo->getStoreLocatorItemByOrder($order);
    }
}
