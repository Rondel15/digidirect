<?php

namespace Digidirect\Collect\Model\Plugin\Quote;

use Digidirect\Collect\Exception\CollectPlaceQtyException;
use Digidirect\Collect\Exception\SelectedShippingMethodException;
use Digidirect\Collect\Model\Carrier\Collectcarrier;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\Quote\Address\RateFactory as AddressRateFactory;

/**
 * Class QuoteManagement
 * @package Digidirect\Collect\Model\Plugin\Quote
 */
class QuoteManagement
{
    /**
     * @var \Digidirect\Collect\Helper\Data
     */
    private $collectHelper;

    /**
     * @var \Digidirect\Collect\Model\CollectQuantityValidator
     */
    private $collectQuantityValidator;

    /**
     * @var \Digidirect\Collect\Helper\Config\Address
     */
    private $addressHelper;

    /**
     * @var AddressRateFactory
     */
    private $addressRateFactory;

    /**
     * QuoteManagement constructor.
     *
     * @param \Digidirect\Collect\Helper\Data $collectHelper
     * @param \Digidirect\Collect\Model\CollectQuantityValidator $collectQuantityValidator
     * @param \Digidirect\Collect\Helper\Config\Address $addressHelper
     * @param AddressRateFactory $addressRateFactory
     */
    public function __construct(
        \Digidirect\Collect\Helper\Data $collectHelper,
        \Digidirect\Collect\Model\CollectQuantityValidator $collectQuantityValidator,
        \Digidirect\Collect\Helper\Config\Address $addressHelper,
        AddressRateFactory $addressRateFactory
    ) {
        $this->collectHelper = $collectHelper;
        $this->collectQuantityValidator = $collectQuantityValidator;
        $this->addressHelper = $addressHelper;
        $this->addressRateFactory = $addressRateFactory;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $orderData
     * @return array
     * @throws CollectPlaceQtyException
     * @throws SelectedShippingMethodException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(
        $subject,
        QuoteEntity $quote,
        $orderData = []
    ) {
        if ($this->collectHelper->isCollectEnable()) {
            $this->validateShippingMethod($quote);
            $this->validateProductsQty($quote);
            $this->setCollectAddress($quote);
        }

        return [$quote, $orderData];
    }

    /**
     * @param QuoteEntity $quote
     * @return bool
     * @throws SelectedShippingMethodException
     */
    protected function validateShippingMethod(QuoteEntity $quote)
    {
        if ($this->collectHelper->isSingleVariation()
            && ((!$shippingMethod = $quote->getShippingAddress()->getShippingMethod())
                || Collectcarrier::COLLECT_SHIPPING_METHOD == $shippingMethod)
        ) {
            $this->applyDummyAddress($quote->getShippingAddress());
            // Ensure all required fields are present for collect
            $this->ensureRequiredShippingAddress($quote);
        } elseif ($this->collectHelper->isCollectItems($quote->getId()) &&
            !$this->collectHelper->isDeliveryItems($quote->getId())
        ) {
            $qouteShippingAddress = $quote->getShippingAddress();

            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

            $address = $this->addressHelper->applyDummyAddress($quote->getShippingAddress());
            $address->setShippingMethod(Collectcarrier::COLLECT_SHIPPING_METHOD);
            $rate = $address->getShippingRateByCode($address->getShippingMethod());
            if (!$rate) {
                $rate = $this->createAddressRateForCollect();
                $address->addShippingRate($rate);
            }
            $address->setShippingDescription($rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle());

            // Ensure all required fields are present for collect
            $this->ensureRequiredShippingAddress($quote);
        }

    }


    /**
     * Ensure required shipping address fields are set for collect shipping.
     *
     * @param QuoteEntity $quote
     * @return void
     */
    protected function ensureRequiredShippingAddress(QuoteEntity $quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress) {
            return;
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        if ($shippingMethod !== Collectcarrier::COLLECT_SHIPPING_METHOD) {
            return;
        }

        $billing = $quote->getBillingAddress();

        $val = function ($getter, $billingGetter, $fallback = null) use ($shippingAddress, $billing) {
            $v = null;
            if ($shippingAddress && method_exists($shippingAddress, $getter)) {
                $v = $shippingAddress->{$getter}();
            }
            if (($v === null || $v === '' || $v === [] || $v === 0) && $billing && method_exists($billing, $billingGetter)) {
                $v = $billing->{$billingGetter}();
            }
            return ($v === null || $v === '' || $v === []) ? $fallback : $v;
        };

        $firstname = $val('getFirstname', 'getFirstname', 'Store');
        $lastname  = $val('getLastname',  'getLastname',  'Pickup');
        $telephone = $val('getTelephone', 'getTelephone', '0000000000');

        $street = $shippingAddress->getStreet();
        if (empty($street) || !is_array($street) || count(array_filter($street)) === 0) {
            $street = $billing ? $billing->getStreet() : null;
        }
        if (empty($street) || !is_array($street) || count(array_filter($street)) === 0) {
            $street = ['Store Pickup'];
        }

        $city      = $val('getCity',      'getCity',      'Store Pickup');
        $postcode  = $val('getPostcode',  'getPostcode',  '0000');
        $countryId = $val('getCountryId', 'getCountryId', 'AU');

        // Region/RegionId are optional but keep consistent shape
        $region    = $val('getRegion',    'getRegion',    null);
        $regionId  = $val('getRegionId',  'getRegionId',  null);

        $shippingAddress->setFirstname($firstname);
        $shippingAddress->setLastname($lastname);
        $shippingAddress->setTelephone($telephone);
        $shippingAddress->setStreet($street);
        $shippingAddress->setCity($city);
        $shippingAddress->setPostcode($postcode);
        $shippingAddress->setCountryId($countryId);

        if ($region !== null && $region !== '') {
            $shippingAddress->setRegion($region);
        }
        if ($regionId) {
            $shippingAddress->setRegionId($regionId);
        }

        $shippingAddress->setShippingMethod(Collectcarrier::COLLECT_SHIPPING_METHOD);
        $rate = $shippingAddress->getShippingRateByCode(Collectcarrier::COLLECT_SHIPPING_METHOD);
        if (!$rate) {
            $rate = $this->createAddressRateForCollect();
            $shippingAddress->addShippingRate($rate);
        }

        $shippingAddress->setSaveInAddressBook(0);
    }

    /**
     * Validate product qty in storage
     *
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return void
     * @throws \Digidirect\Collect\Exception\CollectPlaceQtyException
     */
    protected function validateProductsQty(QuoteEntity $quote)
    {
        if ($this->collectHelper->hasStockUpdateInterface()
            && true !== ($sku = $this->collectQuantityValidator->isQuoteProductsQtyValid($quote))
        ) {
            $message = __('Not enough quantity for item SKU = %1', $sku);
            $this->collectHelper->logError($message);
            throw new CollectPlaceQtyException(
                $message
            );
        }
    }

    /**
     * Set collect address
     *
     * @param QuoteEntity $quote
     *
     * @return void
     */
    protected function setCollectAddress(QuoteEntity $quote)
    {
        if ($this->collectHelper->isSingleVariation()
            && ((!$shippingMethod = $quote->getShippingAddress()->getShippingMethod())
                || Collectcarrier::COLLECT_SHIPPING_METHOD == $shippingMethod)
        ) {
            $this->applyDummyAddress($quote->getShippingAddress());
        } elseif ($this->collectHelper->isCollectItems($quote->getId()) &&
            !$this->collectHelper->isDeliveryItems($quote->getId())
        ) {
            $qouteShippingAddress = $quote->getShippingAddress();

            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

            $address = $this->addressHelper->applyDummyAddress($quote->getShippingAddress());
            $address->setShippingMethod(Collectcarrier::COLLECT_SHIPPING_METHOD);
            $rate = $address->getShippingRateByCode($address->getShippingMethod());
            if (!$rate) {
                $rate = $this->createAddressRateForCollect();
                $address->addShippingRate($rate);
            }
            $address->setShippingDescription($rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle());
        }
    }

    /**
     * Apply dummy address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function applyDummyAddress(\Magento\Quote\Model\Quote\Address $address)
    {
        $this->addressHelper->applyDummyAddress($address);
        $address->setIsCollect(true);

        return $address;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address\Rate
     */
    public function createAddressRateForCollect()
    {
        return $this->addressRateFactory->create()
            ->setCode(Collectcarrier::COLLECT_SHIPPING_METHOD)
            ->setCarrier(Collectcarrier::COLLECT_CARRIER_CODE)
            ->setCarrierTitle($this->collectHelper->getCollectMethodTitle())
            ->setMethod(Collectcarrier::COLLECT_CARRIER_CODE)
            ->setMethodTitle($this->collectHelper->getCollectMethodName())
            ->setMethodDescription(null)
            ->setPrice('0.0');
    }
}
