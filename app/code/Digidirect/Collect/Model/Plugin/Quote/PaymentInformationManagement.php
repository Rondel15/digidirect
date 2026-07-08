<?php

namespace Digidirect\Collect\Model\Plugin\Quote;

use Digidirect\Collect\Helper\Storage\Data;

class PaymentInformationManagement
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Data
     */
    private $collectStorageHelper;

    /**
     * PaymentInformationManagement constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Data $collectStorageHelper
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Data $collectStorageHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->collectStorageHelper = $collectStorageHelper;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quote = $this->quoteRepository->get($cartId);
        $shipmentCountryId = $quote->getShippingAddress()->getCountryId();
        if (empty($shipmentCountryId)) {
            $countryId = $this->collectStorageHelper->getCountryId() ?: Data::DEFAULT_COUNTRY_ID;
            $quote->getShippingAddress()->setCountryId($countryId);
        }

        return [$cartId, $paymentMethod, $billingAddress];
    }
}
