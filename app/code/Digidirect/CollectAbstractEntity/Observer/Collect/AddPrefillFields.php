<?php
namespace Digidirect\CollectAbstractEntity\Observer\Collect;

use Digidirect\CollectAbstractEntity\Helper\Config as ConfigHelper;
use Digidirect\CollectAbstractEntity\Model\Checkout\Provider\CheckoutConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Digidirect\CollectAbstractEntity\Model\PrefillFields;
use Magento\Directory\Model\Data\RegionInformation;
use Magento\Directory\Model\Data\CountryInformation;

/**
 * Class AddPrefillFields
 *
 * @package Digidirect\CollectAbstractEntity\Observer\Collect
 */
class AddPrefillFields implements ObserverInterface
{
    const PREFILL_FIELDS = CheckoutConfigProvider::PREFILL_SHIPPING_FIELDS;

    /**
     * @var \Digidirect\CollectAbstractEntity\Helper\Config
     */
    protected $configHelper;

    /**
     * @var string
     */
    protected $prefillParamName;

    /**
     * @var string
     */
    protected $collectPlaceParam;

    /**
     * @var null
     */
    protected $injectToParam;

    /**
     * @var PrefillFields
     */
    protected $prefillFieldsModel;

    /**
     * CheckoutConfigCollectPlaceInformation constructor.
     *
     * @param \Digidirect\CollectAbstractEntity\Helper\Config $configHelper
     * @param \Digidirect\CollectAbstractEntity\Model\PrefillFields $prefillFieldsModel
     * @param string $prefillParamName
     * @param string $collectPlaceParam
     * @param null $injectToParam
     */
    public function __construct(
        ConfigHelper $configHelper,
        PrefillFields $prefillFieldsModel,
        $prefillParamName = self::PREFILL_FIELDS,
        $collectPlaceParam = 'collect_place',
        $injectToParam = null
    ) {
        $this->configHelper = $configHelper;
        $this->prefillParamName = $prefillParamName;
        $this->collectPlaceParam = $collectPlaceParam;
        $this->injectToParam = $injectToParam;
        $this->prefillFieldsModel = $prefillFieldsModel;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->prefillParamName || !$this->collectPlaceParam || !$this->injectToParam) {
            return;
        }

        /** @var DataObject $transport */
        $collectPlace = $observer->getEvent()->getData($this->collectPlaceParam);
        if (!$collectPlace) {
            return;
        }

        $prefillFieldsMatrix = $this->configHelper->getPrefillShippingAddressFieldsMatrix();
        if (!$prefillFieldsMatrix) {
            return;
        }

        $prefillFields = [];
        foreach ($prefillFieldsMatrix as $addressField => $entityField) {
            $prefillFields[$addressField] = $collectPlace->getData($entityField);
        }

        $info = $observer->getEvent()->getData($this->injectToParam);
        if (!$info) {
            return;
        }

        $this->addRegionAdditionalData($prefillFields);
        $info->setData($this->prefillParamName, $prefillFields);
    }

    /**
     * @param array $prefillFields
     * @return void
     */
    public function addRegionAdditionalData(&$prefillFields)
    {
        if (
            isset($prefillFields[PrefillFields::REGION_FIELD]) &&
            isset($prefillFields[CountryInformation::KEY_COUNTRY_ID])
        ) {
            $region = $this->prefillFieldsModel->getRegion(
                $prefillFields[PrefillFields::REGION_FIELD],
                $prefillFields[CountryInformation::KEY_COUNTRY_ID]
            );
            if ($region->getId()) {
                $prefillFields[RegionInformation::KEY_REGION_ID] = $region->getId();
            }
        }
    }
}
