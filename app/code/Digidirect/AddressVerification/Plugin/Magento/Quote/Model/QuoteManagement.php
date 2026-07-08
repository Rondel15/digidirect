<?php
namespace Digidirect\AddressVerification\Plugin\Magento\Quote\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as QuoteEntity;
use Digidirect\AddressVerification\Helper\Autocomplete;
use Digidirect\AddressVerification\Helper\Aupost;

class QuoteManagement
{
    /**
     * @var Autocomplete
     */
    private $autocomplete;
    /**
     * @var Aupost
     */
    private $aupost;

    /**
     * QuoteManagement constructor.
     * @param Autocomplete $autocomplete
     * @param Aupost $aupost
     */
    public function __construct(
        Autocomplete $autocomplete,
        Aupost $aupost
    ) {
        $this->autocomplete = $autocomplete;
        $this->aupost = $aupost;
    }

    /**
     * @param $object
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit($object, QuoteEntity $quote, $orderData = [])
    {
        if (!$this->autocomplete->isAuPostEnabled()) {
            return null;
        }
        $shippingAddress = $quote->getShippingAddress();
        $this->validateAddress($shippingAddress);
        $billingAddress = $quote->getBillingAddress();
        $this->validateAddress($billingAddress);

        return [$quote, $orderData];
    }

    /**
     * @param $address
     * @throws LocalizedException
     */
    public function validateAddress($address)
    {
        if (!$address || !$address->getCountryId()) {
            return;
        }
        $isAddressValid = $this->aupost->isCombinationValid(
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getCity(),
            $address->getRegion(),
            $address->getRegionId(),
            false
        );
        if (!$isAddressValid) {
            throw new LocalizedException(__('Invalid Postcode and/or Suburb. Please, check and try again.'));
        }
    }
}
