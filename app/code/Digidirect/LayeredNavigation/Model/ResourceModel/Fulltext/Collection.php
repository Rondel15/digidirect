<?php

namespace Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Fulltext Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    const STATIC_PLACEHOLDERS_CACHE_ID = 'static_placeholder_cache_';

    /**
     * @var \Magento\Framework\Search\Response\QueryResponse
     */
    protected $queryResponse;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory = null;

    /**
     * @var \Digidirect\LayeredNavigation\Model\Request\Builder
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    protected $searchEngine;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;

    /**
     * @var string
     */
    protected $queryText;

    /**
     * @var null
     */
    protected $order = null;

    /**
     * @var \Digidirect\LayeredNavigation\Model\Request\Builder
     */
    public $memRequestBuilder;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $categoryIds = [];

    /**
     * @var array
     */
    protected $minMaxValuesAttributes = [];

    /**
     * @var array
     */
    protected $staticPlaceholders = [
        'category_ids',
        'price_dynamic_algorithm',
        'search_term',
    ];

    /**
     * @var array
     */
    protected $allowedPriceCalculationAlgorithms = [
        AlgorithmFactory::RANGE_CALCULATION_AUTO,
        AlgorithmFactory::RANGE_CALCULATION_MANUAL,
    ];

    /**
     * @var null
     */
    protected static $aggregations = null;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * Serializer for encode/decode string/data.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Serializer for encode/decode string/data.
     *
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * @var string|null
     */
    private $relevanceOrderDirection = null;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Search\Model\QueryFactory $catalogSearchData
     * @param \Digidirect\LayeredNavigation\Model\Request\Builder $requestBuilder
     * @param \Magento\Search\Model\SearchEngine $searchEngine
     * @param \Digidirect\LayeredNavigation\Helper\Data $helper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param array $allowedPriceCalculationAlgorithms
     * @param array $staticPlaceholders
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Digidirect\LayeredNavigation\Model\Request\Builder $requestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Digidirect\LayeredNavigation\Helper\Data $helper,
        $connection = null,
        array $allowedPriceCalculationAlgorithms = [],
        array $staticPlaceholders = [],
        \Magento\Framework\App\CacheInterface $cache = null,
        \Magento\Framework\Serialize\Serializer\Json $json = null,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize = null,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory = null
    ) {
        $this->queryFactory = $catalogSearchData;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
        $this->requestBuilder = $requestBuilder;
        $this->searchEngine = $searchEngine;
        $this->helper = $helper;
        if (!empty($allowedPriceCalculationAlgorithms)) {
            $this->allowedPriceCalculationAlgorithms = $allowedPriceCalculationAlgorithms;
        }
        if (!empty($staticPlaceholders)) {
            $this->staticPlaceholders = $staticPlaceholders;
        }

        $this->cache = $cache ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\CacheInterface::class);

        $this->json = $json ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->serialize = $serialize ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Serialize::class);

        $this->temporaryStorageFactory = $temporaryStorageFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory::class);
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->queryResponse !== null) {
            throw new \RuntimeException('Illegal state');
        }

        if (!is_array($condition) || (!in_array(key($condition), ['from', 'to'], true) && $field != 'visibility')) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }

            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }

        return $this;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRequestData($builder)
    {
        // reset columns, order and limitation conditions
        $this->_select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->_select->reset(\Magento\Framework\DB\Select::ORDER);
        $this->_select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $this->_select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);

        $this->requestBuilder = $builder;
        $this->queryResponse = null;
        $this->_isFiltersRendered = false;
    }

    /**
     * Render filters before
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->requestBuilder->bindDimension('scope', $this->getStoreId());
        if ($this->queryText) {
            $this->requestBuilder->bind('search_term', $this->queryText);
        }

        if (!empty($this->categoryIds)) {
            $categoryIds = $this->categoryIds;
            if (count($this->categoryIds) == 1) {
                $categoryIds = current($categoryIds);
            }
            $this->requestBuilder->bind('category_ids', $categoryIds);
        }

        $priceRangeCalculation = $this->_scopeConfig->getValue(
            AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
        if (!in_array($priceRangeCalculation, $this->allowedPriceCalculationAlgorithms)) {
            $priceRangeCalculation = AlgorithmFactory::RANGE_CALCULATION_AUTO;
        }

        $this->requestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
        $this->requestBuilder->autoSetRequestName();
        $this->memRequestBuilder = clone $this->requestBuilder;
        $queryRequest = $this->requestBuilder->create();

        $ids = [0];
        $this->queryResponse = $this->searchEngine->search($queryRequest);

        $items = [];
        /** @var \Magento\Framework\Search\Document $document */
        foreach ($this->queryResponse as $document) {
            $ids[] = $document->getId();
            $items[] = $document;
        }

        parent::addFieldToFilter('entity_id', ['in' => $ids]);
        //$this->_totalRecords = count($ids) - 1;

        $this->_prepareDefaultOrder();

//        if ($this->relevanceOrderDirection) {
//            $this->getSelect()->order(
//                new \Zend_Db_Expr(
//                    $this->_conn->quoteInto(
//                        'FIELD(e.entity_id, ?) ' . $this->relevanceOrderDirection,
//                        $ids
//                    )
//                )
//            );
//        }

        $joinedTables = $this->getSelect()->getPart('from');
//        if (!isset($joinedTables['search_result'])) {
//            $temporaryStorage = $this->temporaryStorageFactory->create();
//            $table = $temporaryStorage->storeApiDocuments($items);
//            $this->getSelect()->joinInner(
//                [
//                    'search_result' => $table->getName(),
//                ],
//                'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
//                []
//            );
//        }
    }

    /**
     * @return void
     */
    protected function _prepareDefaultOrder()
    {
        if (null === $this->relevanceOrderDirection && empty($this->_orders)) {
            $this->setOrder('relevance', Select::SQL_ASC);
        }
    }

    /**
     * @return $this
     */
    protected function _renderFilters()
    {
        $this->_filters = [];
        return parent::_renderFilters();
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        if ($attribute === 'relevance') {
            $this->relevanceOrderDirection = $dir;
        } else {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     * @return array
     */
    public function getFacetedData($field)
    {
        if (null === self::$aggregations) {
            $this->_renderFilters();

            $requestBuilder = $this->requestBuilder;
            $requestBuilder->bindDimension('scope', $this->getStoreId());
            $requestBuilder->autoSetRequestName();

            $queryResponse = $this->queryResponse;
            if ($this->helper->isApplyButtonEnabled() || !$this->helper->hideOptionsAfterSelect()) {
                $appliedFilters = $requestBuilder->getAllPlaceholders();
                foreach (array_keys($appliedFilters) as $placeholder) {
                    if (!in_array($placeholder, $this->staticPlaceholders)) {
                        $requestBuilder->removePlaceholder($placeholder);
                    }
                }

                $this->_prepareProductLimitationFilters();
                if ($requestBuilder->hasPlaceholder('category_ids')) {
                    $requestBuilder->bind('category_ids', $this->_productLimitationFilters['category_id']);
                }

                $newPlaceholders = $this->mergePlaceholderValues($requestBuilder->getAllPlaceholders());
                $appliedFilters = $this->mergePlaceholderValues($appliedFilters);

                $newParametersInRequest = array_merge(
                    array_diff_assoc($appliedFilters, $newPlaceholders),
                    array_diff_assoc($newPlaceholders, $appliedFilters)
                );

                if (!empty($newParametersInRequest)) {
                    //We need to cache static placeholders for each category
                    $cacheKey = self::STATIC_PLACEHOLDERS_CACHE_ID . $this->json->serialize($newPlaceholders);
                    $queryRequestSerialized = $this->cache->load($cacheKey);
                    if (!$queryRequestSerialized) {
                        $queryRequest = $requestBuilder->create();
                        $queryResponse = $this->searchEngine->search($queryRequest);

                        $queryRequestSerialized = $this->serialize->serialize($queryResponse);
                        $this->cache->save($queryRequestSerialized, $cacheKey, [self::STATIC_PLACEHOLDERS_CACHE_ID]);
                    } else {
                        try {
                            // $this->serialize->unserialize method doesn't allow to unserialize objects.
                            $queryResponse = unserialize($queryRequestSerialized);
                        } catch (\Throwable $e) {
                            $this->cache->remove($cacheKey);
                        }
                    }
                }
            }
            self::$aggregations = $queryResponse->getAggregations();
        }

        $bucket = self::$aggregations->getBucket($field . '_bucket');
        if (!$bucket) {
            return [];
        }

        $result = [];
        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result[$metrics['value']] = $metrics;
        }
        return $result;
    }

    /**
     * @param array $placeholder
     * @return array
     */
    protected function mergePlaceholderValues(array $placeholder)
    {
        foreach ($placeholder as $key => &$value) {
            if (is_array($value)) {
                asort($value);
                $value = implode(',', $value);
            }
        }
        return $placeholder;
    }

    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $key
     * @return $this
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category, $key = false)
    {
        $this->addFieldToFilter('category_ids', $category->getId());
        if ($key) {
            $this->categoryIds[] = $category->getId();
        } else {
            parent::addCategoryFilter($category);
        }
        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return parent::setVisibility($visibility);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinMaxValueByAttribute(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        if (!isset($this->minMaxValuesAttributes[$attribute->getAttributeCode()])) {
            if ($this->memRequestBuilder->hasPlaceholder($attribute->getAttributeCode() . ".from")
                || $this->memRequestBuilder->hasPlaceholder($attribute->getAttributeCode() . ".to")
            ) {
                /**
                 * @var $requestBuilder \Digidirect\LayeredNavigation\Model\Request\Builder
                 */
                $requestBuilder = clone $this->memRequestBuilder;
                $requestBuilder->removePlaceholder($attribute->getAttributeCode() . ".from");
                $requestBuilder->removePlaceholder($attribute->getAttributeCode() . ".to");
                $queryRequest = $requestBuilder->create();
                $queryResponse = $this->searchEngine->search($queryRequest);
                $productIds = [0];
                /** @var \Magento\Framework\Search\Document $document */
                foreach ($queryResponse as $document) {
                    $productIds[] = $document->getId();
                }
            } else {
                $productIds = $this->getAllIds();
            }

            $select = clone $this->getSelect();
            $storeId = $this->_storeManager->getStore()->getId();

            $select->reset()->from(
                ['main_table' => $this->getConnection()->getTableName('catalog_product_entity')],
                []
            )->join(
                ['attribute_table' => $attribute->getBackend()->getTable()],
                new \Zend_Db_Expr('main_table.row_id = main_table.row_id'),
                [
                    'min' => 'ROUND(MIN(value))',
                    'max' => 'ROUND(MAX(value))',
                ]
            )->where(
                'attribute_table.attribute_id = ?',
                (int)$attribute->getId()
            )->where(
                'attribute_table.store_id = ? OR attribute_table.store_id = 0',
                $storeId
            )->where(
                'main_table.entity_id IN(?)',
                $productIds
            );

            $this->minMaxValuesAttributes[$attribute->getAttributeCode()] = $this->getConnection()->fetchRow($select);
        }

        return $this->minMaxValuesAttributes[$attribute->getAttributeCode()];
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareStatisticsData()
    {
        if ($this->memRequestBuilder) {
            $requestBuilder = clone $this->memRequestBuilder;
            $requestBuilder->removePlaceholder("price.from");
            $requestBuilder->removePlaceholder("price.to");
            $this->requestBuilder = $requestBuilder;
            $this->queryResponse = null;
            $this->_isFiltersRendered = false;
            $where = $this->getSelect()->getPart(\Zend_Db_Select::WHERE);
            foreach ($where as $whereId => $wherePart) {
                if (strpos($wherePart, 'entity_id') !== false) {
                    if ($whereId == 0) {
                        $where[$whereId] = '1';
                    } else {
                        unset($where[$whereId]);
                    }
                }
            }

            $this->getSelect()->setPart(\Zend_Db_Select::WHERE, $where);
            $this->_renderFilters();
        }

        $groupPart = $this->getSelect()->getPart(\Zend_Db_Select::GROUP);
        $this->getSelect()->reset(\Zend_Db_Select::GROUP);

        parent::_prepareStatisticsData();

        $this->getSelect()->setPart(\Zend_Db_Select::GROUP, $groupPart);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        return $this->_getSelectCountSqlEvent();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getSelectCountSqlEvent($select = null, $resetLeftJoins = true)
    {
        $countSelect = parent::_getSelectCountSql($select, $resetLeftJoins);
        $this->_eventManager->dispatch(
            'catalog_product_collection_prepare_select_count_sql_after',
            [
                'collection' => $this,
                'select' => $select,
                'count_select' => $countSelect,
            ]
        );
        return $countSelect;
    }
}
