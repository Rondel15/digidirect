<?php

namespace Digidirect\MyStoreWidget\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Digidirect\MyStoreWidget\Helper\Data as Helper;
use Digidirect\MyStoreWidget\Helper\Config as ConfigHelper;
use Digidirect\MyStoreWidget\Block\MyStore as MyStoreBlock;
use \Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\AbstractEntity\Helper\Url;

/**
 * Class CheckoutConfigProvider
 * @package Digidirect\MyStoreWidget\Model
 */
class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var MyStoreRepositoryInterface
     */
    protected $myStoreRepository;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * CheckoutConfigProvider constructor.
     * @param Helper $helper
     * @param ConfigHelper $configHelper
     * @param UrlInterface $urlBuilder
     * @param JsonHelper $jsonHelper
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param Url $url
     */
    public function __construct(
        Helper $helper,
        ConfigHelper $configHelper,
        UrlInterface $urlBuilder,
        JsonHelper $jsonHelper,
        MyStoreRepositoryInterface $myStoreRepository,
        Url $url
    ) {
        $this->helper = $helper;
        $this->configHelper = $configHelper;
        $this->urlBuilder = $urlBuilder;
        $this->jsonHelper = $jsonHelper;
        $this->myStoreRepository = $myStoreRepository;
        $this->urlHelper = $url;
    }

    /**
     * @return array|mixed
     */
    public function getConfig()
    {
        $output = [];
        if (!$this->configHelper->isEnable() || !$this->configHelper->isShowWidgetOnCheckout()) {
            return $output;
        }
        $selectedStore = $this->getStoreName();
        $availableCountries = $this->configHelper->getAvailableCountries();
        $countCountries = count($availableCountries);
        $store = $this->getStore();
        if ($countCountries == 1) {
            $singleCountryData = current($availableCountries);
        } else {
            $singleCountryData = $availableCountries;
        }

        $myStoreWidget['selected_store'] = $selectedStore;
        $myStoreWidget['available_countries'] = $this->jsonHelper->serialize($availableCountries);
        $myStoreWidget['count_countries'] = $countCountries;
        $myStoreWidget['search_url'] = $this->urlBuilder->getUrl(MyStoreBlock::SEARCH_URL_PATH);
        $myStoreWidget['save_url'] = $this->urlBuilder->getUrl(MyStoreBlock::SAVE_URL_PATH);
        $myStoreWidget['is_geo_location_enabled'] = (int)$this->configHelper->isGeoLocationEnabled();
        $myStoreWidget['is_need_keep_geo_location'] = (int)$this->configHelper->isNeedKeepGeolocation();
        $myStoreWidget['stores_list'] = $this->getStores();
        $myStoreWidget['is_google_auto_suggest_enabled'] = (int)$this->configHelper->isGoogleAutoSuggestEnabled();
        $myStoreWidget['google_auto_suggest_api_key'] = $this->configHelper->getGoogleAutoSuggestApiKey();
        $myStoreWidget['min_length'] = (int)$this->configHelper->getSearchMinLength();
        $myStoreWidget['is_selected_store'] = $selectedStore ? 1 : 0;
        $myStoreWidget['abstract_entity_id'] = $store ? $store->getId() : '';
        $myStoreWidget['single_country_data'] = $singleCountryData;
        if ($store) {
            $myStoreWidget['store_url'] = $this->urlHelper->getAbstractEntityUrl($store->getId(), $store->getStoreId());
            $myStoreWidget['entity_name'] = $store->getEntityName();
        }

        $output['myStoreWidget'] = $myStoreWidget;

        return $output;
    }

    /**
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface|DataObject
     */
    public function getStore()
    {
        return $this->helper->getCurrentStore();
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        $name = '';
        if ($myStore = $this->getStore()) {
            $name = $this->formatStoreName($myStore);
        }
        return $name;
    }

    /**
     * @param DataObject $store
     * @return Phrase
     */
    public function formatStoreName(DataObject $store)
    {
        if (empty($this->data['storeNameFormat'])) {
            $this->data['storeNameFormat'] = '%name';
        }
        return new Phrase($this->data['storeNameFormat'], $store->getData());
    }

    /**
     * @return bool|string
     */
    public function getStores()
    {
        return $this->jsonHelper->serialize($this->myStoreRepository->getStoresCollection());
    }
}
