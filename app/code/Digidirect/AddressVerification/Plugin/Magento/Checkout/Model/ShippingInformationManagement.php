<?php
namespace Digidirect\AddressVerification\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Model\ShippingInformationManagement as MagentoShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Digidirect\AddressVerification\Helper\Autocomplete;
use Digidirect\AddressVerification\Helper\Aupost;
use Magento\Framework\Exception\InputException;

/**
 * Class ShippingInformationManagement
 *
 * @package Digidirect\AddressVerification\Plugin\Magento\Checkout\Model
 */
class ShippingInformationManagement
{
    /**
     * @var Aupost
     */
    protected $auPost;

    /**
     * @var Autocomplete
     */
    protected $autoCompleteHelper;

    /**
     * ShippingInformationManagement constructor.
     *
     * @param Aupost $aupost
     * @param Autocomplete $autocomplete
     */
    public function __construct(Aupost $aupost, Autocomplete $autocomplete)
    {
        $this->auPost = $aupost;
        $this->autoCompleteHelper = $autocomplete;
    }

    /**
     * @param MagentoShippingInformationManagement $shippingInformationManagement
     * @param int|string $cartId
     * @param $shippingInformation ShippingInformationInterface
     * @return null
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        MagentoShippingInformationManagement $shippingInformationManagement,
        $cartId,
        $shippingInformation
    ) {
        if ($this->autoCompleteHelper->isAuPostEnabled()) {
            $code = $shippingInformation->getShippingMethodCode();
            if ($this->auPost->isShippingAddressValidationRequired($code)) {
                $address = $shippingInformation->getShippingAddress();
                if (!$address || !$address->getCountryId()) {
                    return null;
                }
                if (!$this->auPost->isCombinationValid(
                    $address->getCountryId(),
                    $address->getPostcode(),
                    $address->getCity(),
                    $address->getRegion(),
                    $address->getRegionId(),
                    false
                )
                ) {
                    throw new InputException(__('You entered invalid Postcode and/or Suburb. Please, check and try again.'));
                }
            }

        }
        return null;
    }
}
