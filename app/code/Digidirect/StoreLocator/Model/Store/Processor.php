<?php

namespace Digidirect\StoreLocator\Model\Store;

use Digidirect\StoreLocator\Component\AttributeSetNameNormalizer;
use Digidirect\StoreLocator\Data\ProcessorConstants;
use Digidirect\StoreLocator\Helper\Config;
use Digidirect\StoreLocator\Helper\Directory;
use Digidirect\StoreLocator\Model\Config\Source\DisplayOnMap;
use Digidirect\Locator\Model\ProcessorInterface;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use Digidirect\StoreLocator\Model\Config\Source\SortOrder;
use Digidirect\StoreLocator\Model\Frontend\UrlModifier;
use Magento\Framework\Api\SortOrder as SortOrderApi;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntityIndex;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity\Attribute\Source\VisibleOnFrontend;
use Digidirect\AbstractEntity\Model\AbstractEntity\Attribute\Source\Status;
use Magento\Framework\App\Cache;
use Magento\Framework\App\ObjectManager;
use \Magento\Framework\App\ResourceConnection;
use Digidirect\AbstractEntity\Model\AbstractEntity\Media\Config as MediaConfig;
use Magento\Framework\UrlInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Digidirect\StoreLocator\Helper\AeIndex as AeHelper;
use Magento\Catalog\Model\Template\Filter as CatalogTemplateFilter;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Digidirect\Locator\Helper\LocalizationConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Processor implements ProcessorInterface
{
    const DISTANCE_FIELD = 'distance';
    const ADDITIONAL_SETTINGS = 'additional_settings';
    const COUNTRY_FILTER = 'country_filter';
    const REGION_FILTER = 'region_filter';
    const SEARCH_TERM_PARAM = 'searchTerm';
    const SEARCH_PARAMS_DO_NOT_FILTER_BY_COUNTRY = 'do_not_filter_by_country';
    const DIGIDIRECT_STORE_LOCATOR_CACHE_TABLES = 'digidirect_storelocator_cache_tables';
    const SEARCH_PARAMS_DO_NOT_FILTER_BY_REGION = 'do_not_filter_by_region';
    const SEARCH_PARAM_GUESS_STATE = 'guess_state';
    const SEARCH_PARAMS_GUESS_COUNTRY = 'guess_country';
    const QUERY = 'query';
    const POSTCODE_QUERY_CHANGE = 'postcode_query_change';
    const COUNTRY_QUERY_CHANGE = 'country_query_change';
    const STATE_QUERY_CHANGE = 'state_query_change';
    const STORE_LOCATOR_ENTITY_CHANGE_NAME = 'store_entity_change_name';

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var AbstractEntityIndex
     */
    protected $abstractEntityIndex;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var AeHelper
     */
    protected $aeHelper;

    /**
     * @var null
     */
    protected $setCollection = null;

    /**
     * @var AttributeSetNameNormalizer
     */
    protected $attributeSetNormalizer;

    /**
     * @var Cache
     */
    protected $cacheManager;

    /**
     * @var array
     */
    protected $existingTables = [];

    /**
     * @var CatalogTemplateFilter
     */
    protected $catalogTemplateFilter;

    /**
     * @var LocalizationConfig
     */
    protected $localizationConfig;

    /**
     * @var Directory
     */
    protected $directoryHelper;

    /**
     * @var null|string
     */
    private $searchTermCountryBySearchTerm = [];

    /**
     * @var mixed
     */
    protected $eventManager;

    /**
     * @var array
     */
    private $debugInfo = [];

    /**
     * @var null|UrlModifier
     */
    private $urlModifier = null;

    /**
     * Processor constructor.
     * @param ConfigHelper $configHelper
     * @param AbstractEntityIndex $abstractEntityIndex
     * @param ResourceConnection $resourceConnection
     * @param MediaConfig $mediaConfig
     * @param UrlInterface $urlBuilder
     * @param CollectionFactory $collectionFactory
     * @param AeHelper $aeHelper
     * @param CatalogTemplateFilter $catalogTemplateFilter
     * @param AttributeSetNameNormalizer $attributeSetNameNormalizer
     * @param Cache $cache
     * @param LocalizationConfig $localizationConfig
     * @param Directory $directory
     * @param EventManagerInterface|null $eventManager
     * @param UrlModifier $urlModifier
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigHelper $configHelper,
        AbstractEntityIndex $abstractEntityIndex,
        ResourceConnection $resourceConnection,
        MediaConfig $mediaConfig,
        UrlInterface $urlBuilder,
        CollectionFactory $collectionFactory,
        AeHelper $aeHelper,
        CatalogTemplateFilter $catalogTemplateFilter,
        AttributeSetNameNormalizer $attributeSetNameNormalizer,
        Cache $cache,
        LocalizationConfig $localizationConfig,
        Directory $directory = null,
        EventManagerInterface $eventManager = null,
        UrlModifier $urlModifier = null
    ) {
        $this->cacheManager = $cache;
        $this->attributeSetNormalizer = $attributeSetNameNormalizer;
        $this->configHelper = $configHelper;
        $this->abstractEntityIndex = $abstractEntityIndex;
        $this->resource = $resourceConnection;
        $this->mediaConfig = $mediaConfig;
        $this->urlBuilder = $urlBuilder;
        $this->attributeSetCollectionFactory = $collectionFactory;
        $this->aeHelper = $aeHelper;
        $this->catalogTemplateFilter = $catalogTemplateFilter;
        $this->localizationConfig = $localizationConfig;
        $this->directoryHelper = $directory ?: ObjectManager::getInstance()->get(Directory::class);
        $objectManager = ObjectManager::getInstance();
        $this->eventManager = $eventManager ?: $objectManager->get(EventManagerInterface::class);
        $this->urlModifier = $urlModifier ?: $objectManager->get(UrlModifier::class);
    }

    /**
     * @param array $data
     * @param array $searchParams
     * @return array
     */
    public function process($data, $searchParams)
    {
        $result = [];
        $store = null;
        if (!empty($searchParams['store'])) {
            $store = $searchParams['store'];
        }
        $attributes = $this->configHelper->getAttributes();
        $setName = null;
        if (!empty($data['attribute_set'])) {
            $setName = $data['attribute_set'];
        }
        $callbacks = null;
        if (!empty($data['callbacks'])) {
            $callbacks = $data['callbacks'];
        }
        $items = $this->searchStores($setName, $searchParams, $attributes);

        $transportObject = new \Magento\Framework\DataObject([ProcessorConstants::ITEMS => $items]);
        $this->eventManager->dispatch(
            'digidirect_storelocator_add_extra_info_to_stores_items',
            ['transport_object' => $transportObject]
        );
        $items = $transportObject->getItems();

        $result[ProcessorConstants::ITEMS] = $this->prepareStores($items, $store, $callbacks);
        $result[ProcessorConstants::SETTINGS] = [
            ProcessorConstants::SHOW_FEATURED_AT_THE_TOP => $this->configHelper->isShowFeaturedStoresAtTheTop(),
            ProcessorConstants::SORT_ORDER => $this->configHelper->getSortOrder(),
            ProcessorConstants::DEFAULT_IMAGE => $this->configHelper->getDefaultImage(),
            ProcessorConstants::EXCLUSIVE_ICON => $this->configHelper->getExclusiveIcon(),
            ProcessorConstants::STORES_ON_LOCATOR_PAGE => $this->configHelper->getStoresOnLocatorPage(),
            ProcessorConstants::OPEN_IN_POPUP => $this->configHelper->getOpenInPopup()
        ];
        if ($this->isDebugMode()) {
            $result[ProcessorConstants::DEBUG] = $this->debugInfo;
        }

        try {
            /**
             * Usage: Add array ['setting_code' => 'xml_path'] to request params
             * It will automatically retrieve these settings from configuration
             */
            $additionalSettings = $searchParams[self::ADDITIONAL_SETTINGS] ?? [];
            if (!empty($additionalSettings) && \is_array($additionalSettings)) {
                $settings = $this->configHelper->setRequiredSettings($additionalSettings)->getSettings();
                foreach ($settings as $settingCode => $settingValue) {
                    $result[ProcessorConstants::SETTINGS][$settingCode] = $settingValue;
                }
            }
        } catch (\Throwable $exception) {
            return $result;
        }

        return $result;
    }

    /**
     * @param string $storeLocatorName
     * @return mixed|string
     */
    protected function changeStoreLocatorName($storeLocatorName)
    {
        $storeLocatorName = $this->normalizeName($storeLocatorName);

        $history = $this->configHelper->getHistory();
        $currentId = $history[$storeLocatorName] ?? null;
        if (!$currentId) {
            //Means that was not changed
            return $storeLocatorName;
        }

        $attributeSet = $this->aeHelper->getAttributeSetById($currentId);
        if (!($attributeSetName = $attributeSet->getAttributeSetName())) {
            return $storeLocatorName;
        }

        if ($this->getExistsTableNameByAttributeSetName($attributeSetName)) {
            return $attributeSetName;
        }
        return $storeLocatorName;
    }

    /**
     * Get AE index table name by attribute set name and check if table exists
     *
     * @param string $attributeSetName
     * @return string|null
     */
    protected function getExistsTableNameByAttributeSetName($attributeSetName)
    {
        return $this->aeHelper->getExistsTableNameByAttributeSetName($attributeSetName);
    }

    /**
     * @param string $tableName
     * @return bool
     */
    protected function tableExists($tableName)
    {
        if (!isset($this->existingTables[$tableName])) {
            $cacheKey = static::DIGIDIRECT_STORE_LOCATOR_CACHE_TABLES . '_' . $tableName;
            $tableInCache = $this->cacheManager->load($cacheKey);
            try {
                $tables = $tableInCache;
            } catch (\Throwable $exception) {
                $tables = [];
            }
            if (!$tables) {
                $tableExists = !empty($this->abstractEntityIndex->getConnection()->getTables($tableName));
                if (false !== $tableExists) {
                    $this->cacheManager->save(
                        $tableExists,
                        $cacheKey,
                        [\Magento\Framework\View\Element\AbstractBlock::CACHE_GROUP]
                    );
                }
                $this->existingTables[$tableName] = $tableExists;
            } else {
                $this->existingTables[$tableName] = $tables;
            }
        }
        return !empty($this->existingTables[$tableName]);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeName(string $name)
    {
        return $this->attributeSetNormalizer->normalize($name);
    }

    /**
     * @param string $attributeSetName
     * @param array $searchParams
     * @param array $attributes
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function searchStores(
        $attributeSetName = ConfigHelper::ATTRIBUTE_SET_NAME,
        $searchParams = [],
        array $attributes = []
    ) {
        $attributeSetName = $this->changeStoreLocatorName($attributeSetName);

        $sortDistanceField = self::DISTANCE_FIELD;
        if (empty($searchParams['latitude'])
            || empty($searchParams['longitude'])
            || !empty($searchParams['specified_ids'])
            || !$this->aeHelper->isAttributeDescribed($attributeSetName, 'latitude')
            || !$this->aeHelper->isAttributeDescribed($attributeSetName, 'longitude')
        ) {
            $sortDistanceField = AbstractEntityInterface::NAME;
        }

        $searchTerm = isset($searchParams[self::SEARCH_TERM_PARAM]) ? $searchParams[self::SEARCH_TERM_PARAM] : null;

        $sortOrder = $this->getSortOrders($sortDistanceField);
        try {
            $attributes = $this->getExistsAttributes($attributeSetName, $attributes);

            $searchTerm = $this->modifyQuery($searchTerm);
            if(empty($searchTerm))
            {
                $searchTerm = null;
            }
            if (empty($searchTerm)){
                $searchTerm = null;
            }


            $select = $this->abstractEntityIndex->searchEntities(
                $attributeSetName,
                $searchTerm,
                $attributes,
                $sortOrder,
                true,
                $searchParams['completeCoincidence'] ?? false
            );
        } catch (\Throwable $throwable) {
            $select = [];
        }

        if (\is_array($select)) {
            return $select;
        }
        $select->where(
            AbstractEntityInterface::VISIBLE_ON_FRONTEND . '=?',
            VisibleOnFrontend::VISIBLE_ON_FRONTEND_ENABLED
        );

        $select->where(AbstractEntityInterface::STATUS . '=?', Status::STATUS_ENABLED);
        $select->where(ConfigHelper::ATTRIBUTE_DISPLAY_ON_MAP . '=?', DisplayOnMap::DISPLAY_ON_MAP_ENABLED);
        $this->addAttributeToFilter($select, $attributes, 'longitude', 'NOT ISNULL(longitude)');
        $this->addAttributeToFilter($select, $attributes, 'longitude', 'NOT ISNULL(latitude)');
        if (!empty($searchParams['specified_ids']) && \is_array($searchParams['specified_ids'])) {
            $select->where('entity_id IN (?)', $searchParams['specified_ids']);
        } else {
            if (!empty($searchParams['latitude'])
                && $this->aeHelper->isAttributeDescribed($attributeSetName, 'latitude')
                && !empty($searchParams['longitude'])
                && $this->aeHelper->isAttributeDescribed($attributeSetName, 'longitude')
            ) {
                $defaultRadius = $this->configHelper->getDefaultRadius();
                $distance = empty($searchParams['radius']) ?
                    $this->localizationConfig->convertUnitToKilometres($defaultRadius) :
                    $searchParams['radius'] * 1000;

                if (!empty($searchParams['without_radius']) && $searchParams['without_radius']) {
                    $distance = null;
                }
                $this->addLatLngToFilterDistance(
                    $select,
                    $searchParams['latitude'],
                    $searchParams['longitude'],
                    $distance
                );
            }

            /**
             * In case you need to display all stores in all countries
             */
            $doNotFilterByCountry = $searchParams[self::SEARCH_PARAMS_DO_NOT_FILTER_BY_COUNTRY] ?? null;
            if (!$doNotFilterByCountry) {
                $select->where('country  IN (?)', $this->getCountryFilter($searchParams));
            }

            $doNotFilterByRegion = $searchParams[self::SEARCH_PARAMS_DO_NOT_FILTER_BY_REGION] ?? null;
            if (!$doNotFilterByRegion) {
                $regionFilter = $this->getRegionFilter($searchParams);
                if (!empty($regionFilter)) {
                    $this->tryRegionFilter($select, $regionFilter, $attributes);
                }
            }
        }

        $limit = $this->configHelper->getMaxStoresToShow();
        if (!empty($searchParams['limit'])) {
            $limit = $searchParams['limit'];
        }
        $select->limit($limit);
        try {
            if ($this->isDebugMode()) {
                $this->addDebug(static::QUERY, $select->__toString());
            }
            return $this->resource->getConnection()->fetchAll($select);
        } catch (\Throwable $exception) {
            return [
                'error' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param Select $select
     * @param array $regionFilter
     * @param array $attributes
     * @return void
     */
    private function tryRegionFilter(\Magento\Framework\Db\Select $select, $regionFilter, $attributes)
    {
        if (\in_array('state', $attributes)) {
            $select->where('state  IN (?)', $this->getCountryFilter($regionFilter));
        }
    }

    /**
     * @param Select $select
     * @param array $attributes
     * @param string $attribute
     * @param string $condition
     * @return void
     */
    protected function addAttributeToFilter(Select $select, array $attributes, $attribute, $condition)
    {
        if (\in_array($attribute, $attributes)) {
            $select->where($condition);
        }
    }

    /**
     * @param string $attribteSetName
     * @param array $attributes
     * @return array
     */
    protected function getExistsAttributes($attribteSetName, array $attributes)
    {
        $tableName = $this->getExistsTableNameByAttributeSetName($attribteSetName);
        if ($tableName !== null) {
            $describedAttributes = $this->abstractEntityIndex->getConnection()->describeTable($tableName);
            $attributes = \array_intersect($attributes, \array_keys($describedAttributes));
        }
        return $attributes;
    }

    /**
     * @param array $searchParams
     * @return bool
     */
    protected function areCoordinatesPresented($searchParams)
    {
        return !empty($searchParams['latitude']) && !empty($searchParams['longitude']);
    }

    /**
     * @param array $searchParams
     * @return array
     */
    protected function getCountryFilter($searchParams)
    {
        $countryCodes = [];
        if ($this->isCountryFilterPresented($searchParams)) {
            $countryCodes = $searchParams[self::COUNTRY_FILTER] ?? [];
        }

        if (!is_array($countryCodes)) {
            $countryCodes = \explode(',', $countryCodes);
        }

        $countryCodes = \array_filter($countryCodes);

        if (!empty($countryCodes)) {
            return $countryCodes;
        }

        /**
         * If country filter is not presented - try to filter by entered country
         */
        $searchTerm = isset($searchParams[self::SEARCH_TERM_PARAM]) ? $searchParams[self::SEARCH_TERM_PARAM] : null;
        if (!empty($this->getSearchTermCountry($searchTerm))) {
            return $this->getSearchTermCountry($searchTerm);
        }

        if ($this->areCoordinatesPresented($searchParams)) {
            return $this->configHelper->getAvailableCountries();
        }

        return $this->configHelper->getCountriesListForStoreLocatorPageFrom() == Config::AVAILABLE_COUNTRIES
            ? $this->configHelper->getAvailableCountries()
            : [$this->configHelper->getDefaultCountry()];
    }


    /**
     * Get regions codes as array.
     * @param array $searchParams
     * @return array|null
     */
    protected function getRegionFilter($searchParams)
    {
        if (!$this->isRegionFilterPresented($searchParams)) {
            return null;
        }

        $regions = $searchParams[static::REGION_FILTER] ?? [];
        if (!\is_array($regions)) {
            $regionsCodes = \explode(',', $regions);
            $regions = $regionsCodes;
        }
        $regionsCodes = \array_filter($regions);
        return $regionsCodes;
    }

    /**
     * @param string $sortDistanceField
     * @return array
     */
    protected function getSortOrders($sortDistanceField = self::DISTANCE_FIELD)
    {
        $sortOrders = [];

        if ($this->configHelper->isShowFeaturedStoresAtTheTop()) {
            $sortOrders[] = ConfigHelper::ATTRIBUTE_FEATURED . ' ' . SortOrderApi::SORT_DESC;
        }
        // @codingStandardsIgnoreStart
        switch ($this->configHelper->getSortOrder()) {
            case SortOrder::SORT_PRIORITY:
                $priorityField = ConfigHelper::ATTRIBUTE_PRIORITY;
                $sortOrders[] = 'if(ISNULL(' . $priorityField . ') OR ' . $priorityField . '=0,0,1) '
                    . SortOrderApi::SORT_DESC;
                $sortOrders[] = ConfigHelper::ATTRIBUTE_PRIORITY . ' ' . SortOrderApi::SORT_ASC;
                $sortOrders[] = AbstractEntityInterface::ENTITY_ID . ' ' . SortOrderApi::SORT_ASC;
                break;
            case SortOrder::SORT_DISTANCE:
                $sortOrders[] = $sortDistanceField . ' ' . SortOrderApi::SORT_ASC;
                break;
            default:
                return $sortOrders;
        }
        // @codingStandardsIgnoreEnd

        return $sortOrders;
    }

    /**
     * @param array $items
     * @param null $store
     * @return array
     */
    protected function prepareStores($items, $store, $callbacks)
    {
        foreach ($items as &$item) {
            if (!empty($item['url_key'])) {
                if ($store) {
                    $this->urlBuilder->setScope($store);
                }
                $item['url_key'] = $this->getUrl((string)$this->urlBuilder->getUrl($item['url_key'], ['_nosid' => 1]));
            }

            if (!empty($item['image'])) {
                $item['image'] = $this->mediaConfig->getBaseMediaUrl() . $item['image'];
            }

            if (!empty($callbacks)) {
                foreach ($callbacks as $attribute => $callback) {
                    if (!empty($item[$attribute])) {
                        if (\is_callable($callback)) {
                            $item[$attribute] = \call_user_func_array($callback, [$item[$attribute]]);
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * @param string $url
     * @return string
     */
    private function getUrl(string $url): string
    {
        return $this->urlModifier->modify($url);
    }

    /**
     * @param \Magento\Framework\Db\Select $select
     * @param string $lat
     * @param string $lng
     * @param null|int $distance
     * @return $this
     */
    protected function addLatLngToFilterDistance($select, $lat, $lng, $distance = null)
    {
        // @codingStandardsIgnoreStart
        $expression = "(1609.34*((acos(sin(({{lat}}*pi()/180)) * sin((`{{latitude}}`*pi()/180))+cos(($lat *pi()/180)) * cos((`{{latitude}}`*pi()/180)) * cos((({{lng}} - `{{longitude}}`)*pi()/180))))*180/pi())*60*1.1515)";
        $this->addExpressionFieldToSelect($select, self::DISTANCE_FIELD, $expression,
            ['latitude' => 'latitude', 'longitude' => 'longitude', 'lat' => $lat, 'lng' => $lng]);
        // @codingStandardsIgnoreEnd
        if ($distance) {
            $select->having(self::DISTANCE_FIELD . ' <= ?', $distance);
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Db\Select $select
     * @param string $alias
     * @param string $expression
     * @param string $fields
     * @return $this
     */
    protected function addExpressionFieldToSelect($select, $alias, $expression, $fields)
    {
        // validate alias
        if (!is_array($fields)) {
            $fields = [$fields => $fields];
        }

        $fullExpression = $expression;
        foreach ($fields as $fieldKey => $fieldItem) {
            $fullExpression = \str_replace('{{' . $fieldKey . '}}', $fieldItem, $fullExpression);
        }

        $select->columns([$alias => $fullExpression]);

        return $this;
    }

    /**
     * @param array $searchParams
     * @return bool
     */
    protected function isCountryFilterPresented($searchParams)
    {
        return isset($searchParams[self::COUNTRY_FILTER]);
    }

    /**
     * @param array $searchParams
     * @return bool
     */
    protected function isRegionFilterPresented($searchParams)
    {
        return isset($searchParams[self::REGION_FILTER]);
    }

    /**
     * Experimental. Enables in an admin area to take control of this functionality as it can work incorrectly or slow
     * @param string $searchTerm
     * @return string
     */
    protected function guessState($searchTerm)
    {
        if (!$this->configHelper->guessState()) {
            return $searchTerm;
        }
        $searchTermOriginal = $searchTerm;
        $availableCountries = $this->configHelper->getAvailableCountries();
        $cacheKey = \md5(\json_encode($availableCountries));
        $cache = $this->cacheManager->load($cacheKey);
        if (false === $cache) {
            $regions = [];
            foreach ($availableCountries as $country) {
                $regionsList = $this->directoryHelper->getRegionsByCountry($country);
                foreach ($regionsList as $configuration) {
                    $code = $configuration['code'] ?? null;
                    $name = $configuration['name'] ?? null;
                    $regions[$code] = $name;
                }
            }
            $this->cacheManager->save(\json_encode($regions), $cacheKey);
        } else {
            $regions = \json_decode($cache, true);
        }
        if (!empty($regions)) {
            $searchTermExploded = explode(',', $searchTerm);
            foreach ($searchTermExploded as $key => $value) {
                $value = \trim($value);
                if (isset($regions[$value])) {
                    $searchTermExploded[$key] = $regions[$value];
                    break;
                }
            }
            $searchTerm = \implode(',', $searchTermExploded);
        }

        if ($this->isDebugMode()) {
            $this->addDebug(
                static::STATE_QUERY_CHANGE,
                \sprintf(
                    'Query changed from %s to %s as region code is detected.',
                    $searchTermOriginal,
                    $searchTerm
                )
            );
        }

        return $searchTerm;
    }

    /**
     * @param string $searchTerm
     * @return bool
     */
    protected function isOnlyCountryInSearchTerm($searchTerm)
    {
        return !empty($this->getSearchTermCountry($searchTerm));
    }

    /**
     * @param string $searchTerm
     * @return null|string
     */
    protected function modifyQuery($searchTerm)
    {
        if ($this->configHelper->ignoreSearchTerm()) {
            return '';
        }
        /**
         * If user types only country name in a search => just remove search term and use country filter
         */
        if ($this->isOnlyCountryInSearchTerm($searchTerm)) {

            if ($this->isDebugMode()) {
                $this->addDebug(
                    static::COUNTRY_QUERY_CHANGE,
                    \sprintf(
                        'Query changed from %s to %s as there is only country provided in a query',
                        $searchTerm,
                        ''
                    )
                );
            }

            return null;
        }

        $searchTerm = $this->guessState($searchTerm);
        $searchTerm = $this->guessPostcode($searchTerm);
        return $searchTerm;
    }

    /**
     * @param string $searchTerm
     * @return mixed
     */
    private function getSearchTermCountry($searchTerm)
    {
        if (!$this->configHelper->guessCountry()) {
            return null;
        }
        if (!isset($this->searchTermCountryBySearchTerm[$searchTerm])) {
            $this->searchTermCountryBySearchTerm[$searchTerm] = $this->directoryHelper->getCountryCodeByName($searchTerm);
        }

        return $this->searchTermCountryBySearchTerm[$searchTerm];
    }

    /**
     * @param string|null $searchTerm
     * @return string
     */
    private function guessPostcode($searchTerm)
    {
        if ($this->configHelper->guessPostcode() && \is_numeric(\trim($searchTerm))) {
            if ($this->isDebugMode()) {
                $this->addDebug(
                    static::POSTCODE_QUERY_CHANGE,
                    \sprintf(
                        'Query has been changed from %s to %s as guess postcode setting is on and query is numeric',
                        $searchTerm,
                        ''
                    )
                );
            }
            return '';
        }

        return $searchTerm;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ProcessorInterface
     */
    private function addDebug(string $key, $value): ProcessorInterface
    {
        if ($this->isDebugMode()) {
            $this->debugInfo[$key] = $value;
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isDebugMode(): bool
    {
        return (bool)$this->configHelper->isDebugMode();
    }
}
