<?php

namespace Digidirect\StoreLocator\Helper;

use Digidirect\Locator\Helper\DefaultConfiguration;
use Digidirect\StoreLocator\Component\Serialize;
use Magento\Store\Model\ScopeInterface;
use Digidirect\StoreLocator\Model\Config\Source\Metric;
use Magento\Framework\App\Helper\Context;
use Digidirect\Locator\Helper\LocalizationConfig;

/**
 * @since 1.4.0 it extends DefaultConfiguration Helper
 */
class Config extends DefaultConfiguration
{
    const DEFAULT_COUNTRY = 'default_country';
    const AVAILABLE_COUNTRIES = 'available_countries';
    const STORE_LOCATOR_DEBUG = 'store_locator_debug';
    const ATTRIBUTE_SET_NAME = 'store';
    const ATTRIBUTE_FEATURED = 'featured';
    const ATTRIBUTE_PRIORITY = 'priority';
    const ATTRIBUTE_PHONE_NUMBER = 'phone_number';
    const ATTRIBUTE_DESCRIPTION = 'description';
    const ATTRIBUTE_OPENING_HOURS = 'opening_hours';
    const ATTRIBUTE_DISPLAY_ON_MAP = 'display_on_map';
    const DEFAULT_RADIUS = 25;
    const DEFAULT_STORES_ON_LOCATOR_PAGE = 10;
    const DEFAULT_RADIUS_OPTIONS = '10,15,20,25,30,50,75,100';
    const XML_PATH_ENABLED = 'digidirect_storelocator_config/general/enabled';
    const XML_PATH_PAGE_URL = 'digidirect_storelocator_config/list_settings/page_url';
    const XML_PATH_PAGE_META_DESCRIPTION = 'digidirect_storelocator_config/list_settings/page_meta_description';
    const XML_PATH_DEFAULT_RADIUS = 'digidirect_storelocator_config/list_settings/default_radius';
    const XML_PATH_DEFAULT_METRIC = 'digidirect_storelocator_config/list_settings/metric';
    const XML_PATH_RADIUS_OPTIONS = 'digidirect_storelocator_config/list_settings/radius_options';
    const XML_PATH_DEFAULT_COUNTRY = 'digidirect_storelocator_config/list_settings/' . self::DEFAULT_COUNTRY;
    const XML_PATH_AVAILABLE_COUNTRIES = 'digidirect_storelocator_config/list_settings/' . self::AVAILABLE_COUNTRIES;
    const XML_PATH_DEFAULT_IMAGE = 'digidirect_storelocator_config/list_settings/default_image';
    const XML_PATH_STORES_ON_LOCATOR_PAGE = 'digidirect_storelocator_config/list_settings/stores_on_locator_page';
    const XML_PATH_MAX_STORES_TO_SHOW = 'digidirect_storelocator_config/list_settings/max_stores_to_show';
    // @codingStandardsIgnoreStart
    const XML_PATH_GROUP_SEARCH_RESULTS_BY_PARENT = 'digidirect_storelocator_config/list_settings/group_search_results_by_parent_entity';
    // @codingStandardsIgnoreStart
    const XML_PATH_EXTEND_RADIUS = 'digidirect_storelocator_config/search_settings/extend_radius';
    const XML_PATH_SORT_ORDER = 'digidirect_storelocator_config/search_settings/sort_order';
    // @codingStandardsIgnoreStart
    const XML_PATH_SHOW_FEATURED_STORES_AT_THE_TOP = 'digidirect_storelocator_config/search_settings/show_featured_stores_at_the_top';
    // @codingStandardsIgnoreEnd
    const XML_PATH_ATTRIBUTES = 'digidirect_storelocator_config/search_settings/search_attributes';
    const XML_PATH_ENTITIES = 'digidirect_storelocator_config/search_settings/entities';
    const XML_PATH_SEARCH_MIN_LENGTH = 'digidirect_storelocator_config/search_settings/search_min_length';
    const XML_PATH_EXCLUSIVE_ICON = 'digidirect_storelocator_config/exclusive_management/exclusive_icon';
    const XML_PATH_ENABLE_DIRECTIONS = 'digidirect_storelocator_config/store_details/enable_directions';
    const STORE_LOCATOR_UPLOAD_DIR = 'digidirect/storelocator/image';
    const XML_PATH_API = 'digidirect_storelocator_config/general/api';
    const XML_PATH_ASK_TO_USE_GEOLOCATION_ON_FIRST_VISIT =
        'digidirect_storelocator_config/general/ask_to_use_geolocation_on_first_visit';
    const XML_PATH_DETAIL_CLICK_ACTION = 'digidirect_storelocator_config/list_settings/detail_click_action';
    const XML_PATH_MAIN_ENTITY = 'digidirect_storelocator_config/dev/main_entity';
    const XML_PATH_ENTITY_LAYOUT_CONFIGURATION = 'digidirect_storelocator_config/dev/entity_layout_mapping';
    const XML_PAT_NAME_HISTORY = 'digidirect_storelocator/dev/name_history';
    const XML_PAT_LOAD_ALL_CHILDREN_FOR_PARENT = 'digidirect_storelocator_config/dev/load_all_children_for_parent';
    const XML_PATH_GUESS_STATE = 'digidirect_storelocator_config/dev/guess_state';
    const XML_PATH_GUESS_COUNTRY = 'digidirect_storelocator_config/dev/guess_country';
    const XML_PATH_GUESS_POSTCODE = 'digidirect_storelocator_config/dev/guess_postcode';
    const XML_PATH_TRAILING_SLASH = 'digidirect_storelocator_config/seo/remove_trailing_slash';
    const XML_PATH_IGNORE_SEARCH_TERM = 'digidirect_storelocator_config/search_settings/ignore_search_term';
    const XML_PATH_TAKE_CONFIGURATION_FROM = 'digidirect_storelocator_config/list_settings/countries_xml_path';
    const XML_PATH_SEARCH_RESULTS_DISPLAY_MODE = 'digidirect_storelocator_config/general/search_results_display_mode';

    /**
     * @var bool
     */
    private $storeLocatorDebugFlagValue = null;

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var Metric
     */
    protected $metric;

    /**
     * @var Directory
     */
    protected $directoryHelper;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var array
     */
    protected $layoutById = [];

    /**
     * @var LocalizationConfig
     */
    protected $localizationHelper;

    /**
     * Config constructor.
     * @param Context $context
     * @param Metric $metric
     * @param Directory $directory
     * @param Serialize $serialize
     * @param LocalizationConfig $localizationConfig
     */
    public function __construct(
        Context $context,
        Metric $metric,
        Directory $directory,
        Serialize $serialize,
        LocalizationConfig $localizationConfig
    ) {
        $this->serialize = $serialize;
        $this->metric = $metric;
        $this->directoryHelper = $directory;
        parent::__construct($context);
        $this->localizationHelper = $localizationConfig;
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getPageUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAGE_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getPageMetaDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAGE_META_DESCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param bool $km
     * @return int
     */
    public function getDefaultRadius($km = false)
    {
        $defaultRadius = (int)$this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_RADIUS,
            ScopeInterface::SCOPE_STORE
        );
        if (!$defaultRadius) {
            $defaultRadius = self::DEFAULT_RADIUS;
        }

        if (!$km) {
            $defaultRadius *= 1000;
        }

        return $defaultRadius;
    }

    /**
     * @return array
     */
    public function getRadiusOptions()
    {
        $options = $this->getMultiSelectConfig(self::XML_PATH_RADIUS_OPTIONS, ScopeInterface::SCOPE_STORE);
        if (!$options) {
            $options = \explode(',', self::DEFAULT_RADIUS_OPTIONS);
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return $this->directoryHelper->getDefaultCountry();
    }

    /**
     * @return array
     */
    public function getAvailableCountries()
    {
        return $this->directoryHelper->getAvailableCountries();
    }

    /**
     * @return string
     */
    public function getDefaultImage()
    {
        $imagePath = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_IMAGE,
            ScopeInterface::SCOPE_STORE
        );

        return $this->_getImage($imagePath);
    }

    /**
     * @return int
     */
    public function getExtendRadius()
    {
        return (int)$this->scopeConfig->isSetFlag(self::XML_PATH_EXTEND_RADIUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SORT_ORDER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isShowFeaturedStoresAtTheTop()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOW_FEATURED_STORES_AT_THE_TOP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_ATTRIBUTES, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_ENTITIES, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return mixed
     */
    public function getSearchMinLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SEARCH_MIN_LENGTH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getExclusiveIcon()
    {
        $imagePath = $this->scopeConfig->getValue(
            self::XML_PATH_EXCLUSIVE_ICON,
            ScopeInterface::SCOPE_STORE
        );

        return $this->_getImage($imagePath);
    }

    /**
     * @param string $imagePath
     * @return string
     */
    protected function _getImage($imagePath)
    {
        $logoUrl = '';
        if ($imagePath) {
            $folderName = self::STORE_LOCATOR_UPLOAD_DIR;
            $path = $folderName . '/' . $imagePath;
            $logoUrl = $this->_urlBuilder
                    ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        }

        return $logoUrl;
    }

    /**
     * @return bool
     */
    public function isEnableDirections()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_DIRECTIONS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed|string
     */
    public function getMetric()
    {
        return $this->localizationHelper->getLengthUnit();

        /*$defaultMetricValue = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_METRIC,
            ScopeInterface::SCOPE_STORE
        );

        return $this->metric->getMetricByValue($defaultMetricValue);*/
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     * @return array
     */
    protected function getMultiSelectConfig($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $value = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
        if ($value) {
            return \explode(',', $value);
        }
        return [];
    }

    /**
     * @return int
     */
    public function getStoresOnLocatorPage()
    {
        $storesOnLocatorPage = (int)$this->scopeConfig->getValue(
            self::XML_PATH_STORES_ON_LOCATOR_PAGE,
            ScopeInterface::SCOPE_STORE
        );

        if (!$storesOnLocatorPage) {
            $storesOnLocatorPage = self::DEFAULT_STORES_ON_LOCATOR_PAGE;
        }

        return $storesOnLocatorPage;
    }

    /**
     * @return int
     */
    public function getMaxStoresToShow()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_MAX_STORES_TO_SHOW, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function isGroupSearchResultByParentEntity()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GROUP_SEARCH_RESULTS_BY_PARENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getApi($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return bool
     */
    public function isAskToUseGeolocationOnFirstVisit(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ASK_TO_USE_GEOLOCATION_ON_FIRST_VISIT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getStoreDetailClickAction($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DETAIL_CLICK_ACTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getOpenInPopup($storeId = null)
    {
        return $this->isOpenInPopup($storeId);
    }

    /**
     * @param array $paths
     * @return $this
     */
    public function setRequiredSettings(array $paths = [])
    {
        $this->paths = $paths;
        return $this;
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getSettings($storeId = null)
    {
        $settings = [];
        foreach ($this->paths as $code => $path) {
            $settings[$code] = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        }
        return $settings;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getMainEntityId($storeId = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_MAIN_ENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $entityId
     * @param null $storeId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getLayoutMapping($entityId, $storeId = null)
    {
        $mapping = $this->scopeConfig->getValue(
            static::XML_PATH_ENTITY_LAYOUT_CONFIGURATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        try {
            $result = $this->serialize->unserialize($mapping);
            if (\is_array($result)) {
                foreach ($result as $hash => $values) {
                    $entityValue = $values['entity_column'] ?? null;
                    if ($entityValue == $entityId) {
                        return $values['additional_layout_column'] ?? '';
                    }
                }
            }
            return '';
        } catch (\Throwable $throwable) {
            return '';
        }
    }

    /**
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getMappingAsArray($storeId = null)
    {
        if (empty($this->layoutById)) {
            $mapping = $this->scopeConfig->getValue(
                static::XML_PATH_ENTITY_LAYOUT_CONFIGURATION,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            try {
                $result = $this->serialize->unserialize($mapping);
                if (\is_array($result)) {
                    foreach ($result as $hash => $values) {
                        $entityValue = $values['entity_column'] ?? null;
                        $this->layoutById[$entityValue] = $values['additional_layout_column'] ?? null;
                    }
                }
                return $this->layoutById;
            } catch (\Throwable $throwable) {
                return $this->layoutById;
            }
        }

        return $this->layoutById;
    }

    /**
     * @param string|null $historyJson
     * @return array
     */
    public function getHistory($historyJson = null)
    {
        $result = [];
        try {
            if (null === $historyJson) {
                $historyJson = $this->scopeConfig->getValue(static::XML_PAT_NAME_HISTORY);
            }
            return $this->serialize->unserialize($historyJson);
        } catch (\Throwable $exception) {
            return $result;
        }
    }

    /**
     * @return int
     */
    public function isLoadAllChildrenForParent()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PAT_LOAD_ALL_CHILDREN_FOR_PARENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function guessState()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GUESS_STATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function guessCountry()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GUESS_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function guessPostcode()
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_GUESS_POSTCODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Added to easy debug why search works in a particular way
     * @return bool|mixed
     */
    public function isDebugMode()
    {
        if (null === $this->storeLocatorDebugFlagValue) {
            $this->storeLocatorDebugFlagValue = $this->_getRequest()->getParam(
                \md5(static::STORE_LOCATOR_DEBUG),
                false
            );
        }
        return $this->storeLocatorDebugFlagValue;
    }

    /**
     * @return bool
     */
    public function removeTrailingSlash(): bool
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_TRAILING_SLASH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function ignoreSearchTerm(): bool
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IGNORE_SEARCH_TERM,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return string
     */
    public function getCountriesListForStoreLocatorPageFrom(): string
    {
        return (string)$this->scopeConfig->getValue(
            static::XML_PATH_TAKE_CONFIGURATION_FROM,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return string
     */
    public function getSearchResultsDisplayMode()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_SEARCH_RESULTS_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
