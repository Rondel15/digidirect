<?php

namespace Digidirect\Collect\Model\Plugin\Checkout;

use Digidirect\Collect\Helper\Storage\Data;

class GuestShippingInformationManagement
{
    /**
     * @var \Digidirect\Collect\Helper\Data
     */
    private $collectHelper;

    /**
     * @var \Digidirect\Collect\Helper\Config\Address
     */
    private $addressHelper;

    /**
     * GuestShippingInformationManagement constructor.
     *
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Digidirect\Collect\Helper\Config\Address $addressHelper
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Digidirect\Collect\Helper\Config\Address $addressHelper
    ) {
        $this->collectHelper = $collectHelper;
        $this->addressHelper = $addressHelper;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if ($this->collectHelper->isCollectEnable()
            && $this->collectHelper->isSingleVariation()
            && $this->collectHelper->isCollectCarrierCode($addressInformation->getShippingCarrierCode())
        ) {
            $shippingAddress = $addressInformation->getShippingAddress();
            $this->addressHelper->applyDummyAddress($shippingAddress);
        }

        return [$cartId, $addressInformation];
    }
}
