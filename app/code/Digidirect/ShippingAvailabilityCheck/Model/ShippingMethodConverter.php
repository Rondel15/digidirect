<?php

namespace Digidirect\ShippingAvailabilityCheck\Model;

/**
 * Class ShippingMethodConverter
 * @package Digidirect\ShippingAvailabilityCheck\Model
 */
class ShippingMethodConverter
{
    /**
     * Shipping method data factory.
     *
     * @var \Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterfaceFactory
     */
    protected $shippingMethodDataFactory;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * Constructs a shipping method converter object.
     *
     * @param \Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager interface.
     * @param \Magento\Tax\Helper\Data $taxHelper Tax data helper.
     */
    public function __construct(
        \Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->shippingMethodDataFactory = $shippingMethodDataFactory;
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param string $quoteCurrencyCode The quote currency code.
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel The rate model.
     * @return \Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterface Shipping method data object.
     */
    public function modelToDataObject($rateModel, $quoteCurrencyCode)
    {
        $errorMessage = $rateModel->getErrorMessage();

        /**
         * @var \Magento\Quote\Api\Data\ShippingMethodInterface $object
         */
        $object = $this->shippingMethodDataFactory->create()
            ->setCarrierCode($rateModel->getCarrier())
            ->setMethodCode($rateModel->getMethod())
            ->setCarrierTitle($rateModel->getCarrierTitle())
            ->setMethodTitle($rateModel->getMethodTitle())
            ->setAvailable(empty($errorMessage))
            ->setErrorMessage(empty($errorMessage) ? false : $errorMessage);

        $this->addPriceDataToObject($object, $rateModel, $quoteCurrencyCode);

        return $object;
    }

    /**
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface|\Magento\Framework\DataObject $object
     * @param \Magento\Quote\Model\Quote\Address\Rate|\Magento\Framework\Model\AbstractModel $rateModel
     * @param string $quoteCurrencyCode
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addPriceDataToObject($object, $rateModel, $quoteCurrencyCode)
    {
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->storeManager->getStore()->getBaseCurrency();
        $object->setAmount($currency->convert($rateModel->getPrice(), $quoteCurrencyCode))
        ->setBaseAmount($rateModel->getPrice());

        $priceExclTax = $currency->convert($this->getShippingPriceWithFlag($rateModel, false), $quoteCurrencyCode);
        $priceInclTax = $currency->convert($this->getShippingPriceWithFlag($rateModel, true), $quoteCurrencyCode);

        if ($this->isDisplayShippingBothPrices()) {
            $object->setPriceExclTax($priceExclTax)
                ->setPriceInclTax($priceInclTax)
                ->setFormattedPriceExclTax($currency->format($priceExclTax))
                ->setFormattedPriceInclTax($currency->format($priceInclTax));
        } else {
            if ($this->isDisplayShippingPriceExclTax()) {
                $object->setPriceExclTax($priceExclTax)
                    ->setFormattedPriceExclTax($currency->format($priceExclTax));
            } else {
                $object->setPriceInclTax($priceInclTax)
                    ->setFormattedPriceInclTax($currency->format($priceInclTax));
            }
        }
    }

    /**
     * Get Shipping Price including or excluding tax
     *
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param bool $flag
     * @return float
     */
    private function getShippingPriceWithFlag($rateModel, $flag)
    {
        return $this->taxHelper->getShippingPrice(
            $rateModel->getPrice(),
            $flag,
            $rateModel->getAddress(),
            $rateModel->getAddress()->getQuote()->getCustomerTaxClassId()
        );
    }

    /**
     * Return flag whether to display shipping price excluding tax
     *
     * @return bool
     */
    public function isDisplayShippingPriceExclTax()
    {
        return $this->taxHelper->displayShippingPriceExcludingTax();
    }

    /**
     * Return flag whether to display shipping price including and excluding tax
     *
     * @return bool
     */
    public function isDisplayShippingBothPrices()
    {
        return $this->taxHelper->displayShippingBothPrices();
    }
}
