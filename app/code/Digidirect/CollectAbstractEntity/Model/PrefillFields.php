<?php
namespace Digidirect\CollectAbstractEntity\Model;

use Digidirect\CollectAbstractEntity\Model\CollectPlaceRepository;
use Digidirect\Collect\Helper\Data as CollectHelper;
use Digidirect\CollectAbstractEntity\Helper\Config as ConfigHelper;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository as Subject;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\Data\RegionInformation;

/**
 * Class PrefillFields
 *
 * @package Digidirect\CollectAbstractEntity\Model
 */
class PrefillFields
{
    const PREFILLED = '__prefilled_flag';
    const REGION_FIELD = 'region';
    const REGION_CODE_FIELD = 'code';
    const COUNTRY_FIELD = 'country';

    /**
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $collectHelper;

    /**
     * @var \Digidirect\Collect\Api\CollectPlaceRepositoryInterface
     */
    protected $collectPlaceRepository;

    /**
     * @var \Digidirect\CollectAbstractEntity\Helper\Config
     */
    protected $configHelper;

    /**
     * @var Region
     */
    protected $directoryRegion;

    /**
     * @var array
     */
    protected $instanceCache = [];

    /**
     * PrefillFields constructor.
     * @param CollectHelper $collectHelper
     * @param \Digidirect\CollectAbstractEntity\Model\CollectPlaceRepository $collectPlaceRepository
     * @param ConfigHelper $configHelper
     * @param Region $region
     */
    public function __construct(
        CollectHelper $collectHelper,
        CollectPlaceRepository $collectPlaceRepository,
        ConfigHelper $configHelper,
        Region $region
    ) {
        $this->collectHelper = $collectHelper;
        $this->collectPlaceRepository = $collectPlaceRepository;
        $this->configHelper = $configHelper;
        $this->directoryRegion = $region;
    }

    /**
     * @param CartInterface&\Magento\Quote\Model\Quote $quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return bool
     */
    public function prefill(CartInterface $quote)
    {
        if (!$quote->hasData(self::PREFILLED)
            && $this->collectHelper->isCollectEnable()
            && $this->collectHelper->isSingleCartVariation()
            && $this->collectHelper->hasQuoteCollectShippingMethod($quote)
            && $this->collectHelper->hasCollectItemInCart($quote->getId())
            && ($prefillFields = $this->configHelper->getPrefillShippingAddressFieldsMatrix())
            && ($quoteItem = $quote->getItemsCollection()->getFirstItem())
            && ($entityId = $quoteItem->getCollectPlaceId())
        ) {
            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();
            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $shippingAssignment = $extensionAttributes->getShippingAssignments()[0]->getShipping()->getAddress();
            }

            $collectPlace = $this->collectPlaceRepository->getById($entityId);
            $country = $collectPlace->getData(self::COUNTRY_FIELD);
            foreach ($prefillFields as $addressField => $entityField) {
                $shippingAddress->setData($addressField, $collectPlace->getData($entityField));
                if ($addressField == self::REGION_FIELD && $country) {
                    $this->addRegionAdditionalFields($shippingAddress, $collectPlace->getData($entityField), $country);
                    if (!$billingAddress->getData(RegionInformation::KEY_REGION_ID)) {
                        $this->addRegionAdditionalFields($billingAddress, $collectPlace->getData($entityField), $country);
                    }
                }

                if (isset($shippingAssignment)) {
                    $shippingAssignment->setData($addressField, $collectPlace->getData($entityField));
                    if ($addressField == self::REGION_FIELD && $country) {
                        $this->addRegionAdditionalFields(
                            $shippingAssignment,
                            $collectPlace->getData($entityField),
                            $country
                        );
                    }
                }
            }

            $quote->setData(self::PREFILLED, true);

            return true;
        }

        return false;
    }

    /**
     * @param string $regionName
     * @param string $countryId
     * @return $this
     */
    public function addRegionAdditionalFields($object, $regionName, $countryId)
    {
        $region = $this->getRegion($regionName, $countryId);
        if ($region->getId()) {
            $object->setData(
                RegionInformation::KEY_REGION_ID,
                $region->getData(RegionInformation::KEY_REGION_ID)
            );
            $object->setData(
                RegionInformation::KEY_REGION_CODE,
                $region->getData(self::REGION_CODE_FIELD)
            );
        }

        return $object;
    }

    /**
     * @param string $regionName
     * @param string $countryId
     * @return mixed
     */
    public function getRegion($regionName, $countryId)
    {
        $key = str_replace(' ', '_', strtolower($regionName));
        if (!isset($this->instanceCache[$key])) {
            $this->instanceCache[$key] = $this->directoryRegion->loadByName($regionName, $countryId);
        }
        return $this->instanceCache[$key];
    }
}
