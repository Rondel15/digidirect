<?php

namespace Digidirect\StoreLocator\Block\ListStore;

use Digidirect\AbstractEntity\Helper\Image;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use Digidirect\AbstractEntity\Model\AbstractEntity\Media\Config as MediaConfig;
use Digidirect\GoogleApi\Helper\Config as GoogleApiHelper;

/**
 * Class DetailPage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DetailPage extends \Digidirect\StoreLocator\Block\AbstractBlock
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var array
     */
    protected $storeData = [];

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * DetailPage constructor.
     * @param Registry $coreRegistry
     * @param Image $imageHelper
     * @param Context $context
     * @param ConfigHelper $configHelper
     * @param CountryFactory $countryFactory
     * @param MediaConfig $mediaConfig
     * @param GoogleApiHelper $googleApiConfigHelper
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Image $imageHelper,
        Context $context,
        ConfigHelper $configHelper,
        CountryFactory $countryFactory,
        MediaConfig $mediaConfig,
        GoogleApiHelper $googleApiConfigHelper,
        array $data = []
    ) {
        parent::__construct($context, $configHelper, $countryFactory, $googleApiConfigHelper, $data);
        $this->coreRegistry = $coreRegistry;
        $this->imageHelper = $imageHelper;
        $this->mediaConfig = $mediaConfig;
    }

    /**
     * Get current abstract entity
     *
     * @return \Digidirect\AbstractEntity\Model\AbstractEntity|null
     */
    public function getCurrentAbstractEntity()
    {
        return $this->coreRegistry->registry(Constants::CURRENT_ABSTRACT_ENTITY);
    }

    /**
     * Get image helper
     *
     * @return Image
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        $abstractEntity = $this->getCurrentAbstractEntity();
        $country = $this->getCountryName($abstractEntity->getCountry());
        $state = $abstractEntity->getState();
        $city = $abstractEntity->getCity();
        $streetAddress = $abstractEntity->getStreet();
        $postCode = $abstractEntity->getPostcode();

        return "$streetAddress, $city, $state, $postCode, $country";
    }

    /**
     * @return string
     */
    public function getStoreData()
    {
        if (empty($this->storeData)) {
            $abstractEntity = $this->getCurrentAbstractEntity();
            $data = $abstractEntity->getData();
            if (!empty($data['url_key'])) {
                $data['url_key'] = $this->getUrl($data['url_key']);
            }

            if (!empty($data['image'])) {
                $data['image'] = $this->mediaConfig->getBaseMediaUrl() . $data['image'];
            }
            $this->storeData = $data;
        }

        return json_encode($this->storeData);
    }
}
