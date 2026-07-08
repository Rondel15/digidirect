<?php
namespace Digidirect\LayeredNavigation\Model\Layer\Filter;

use Digidirect\LayeredNavigation\Model\Source\DisplayMode;
use Digidirect\LayeredNavigation\Helper\UrlParser;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as PriceDataProvider;

class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price
{
    /**
     * @var array
     */
    protected $_fromTo;

    /**
     * @var array
     */
    protected $minMaxPrice;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\FilterSetting
     */
    protected $settingHelper;

    /**
     * @var string
     */
    protected $currencySymbol;

    /**
     * @var \Digidirect\LayeredNavigation\Model\Layer\PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var null
     */
    public $attributeValue;

    /**
     * @var \Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    /**
     * Price constructor.
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm
     * @param \Digidirect\LayeredNavigation\Model\Layer\PriceCurrency $priceCurrency
     * @param \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory
     * @param \Digidirect\LayeredNavigation\Helper\FilterSetting $settingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Digidirect\LayeredNavigation\Model\Layer\PriceCurrency $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        \Digidirect\LayeredNavigation\Helper\FilterSetting $settingHelper,
        array $data = []
    ) {
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @param float|string $from
     * @return float|string
     */
    protected function getTo($from)
    {
        if ($from == '*') {
            return '';
        }
        return parent::getTo($from);
    }

    /**
     * @return array|\Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function _initItems()
    {
        $filterSetting = $this->getFilterSetting();
        if ($filterSetting->getDisplayMode() != DisplayMode::MODE_SLIDER) {
            return parent::_initItems();
        }

        if (!$this->getMinPrice()) {
            $this->_items = [];
            return $this->_items;
        }

        $this->_items = [
            [
                'from' => $this->getCurrentFrom(),
                'to' => $this->getCurrentTo(),
                'min' => $this->getMinPrice(),
                'max' => $this->getMaxPrice(),
                'requestVar' => $this->getRequestVar(),
                'step' => round($filterSetting->getSliderStep(), 4),
                'template' => !$filterSetting->getUnitsLabelUseCurrencySymbol()
                    ? '{amount} ' . $filterSetting->getUnitsLabel()
                    : $this->currencySymbol . '{amount}',
            ]
        ];

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        if ($this->minMaxPrice === null) {
            $this->initMinMaxPrice();
        }

        return $this->minMaxPrice['min'];
    }

    /**
     * @return mixed
     */
    public function getMaxPrice()
    {
        if ($this->minMaxPrice === null) {
            $this->initMinMaxPrice();
        }
        return $this->minMaxPrice['max'];
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $isMultiSelect = $this->getFilterSetting()->isMultiselect();
        if (!$isMultiSelect) {
            $this->removeMaxPriceFilter($request);
        }

        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $this->attributeValue = $filter;
        $filterParams = explode(',', $filter);
        if (!$isMultiSelect && count($filterParams) > 1) {
            $filterParams = [$filterParams[0]];
        }

        $fromMulti = [];
        $toMulti = [];
        $validatedFilters = [];
        foreach ($filterParams as $filterParam) {
            $validatedFilter = $this->dataProvider->validateFilter($filterParam);
            if ($validatedFilter) {
                $validatedFilters[] = $validatedFilter;
                list($from, $to) = $validatedFilter;
                $fromMulti[] = $from;
                if ($isMultiSelect && $to > 0) {
                    $to = $to - self::PRICE_DELTA;
                } else {
                    $to = empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA;
                }
                $toMulti[] = $to;
            }
        }
		
		if (!$fromMulti || !$toMulti) {
            return $this;
        }

        $this->getLayer()->getProductCollection()->addFieldToFilter('price', [
            'from' => implode(',', $fromMulti),
            'to' =>  implode(',', $toMulti),
        ]);

        foreach ($validatedFilters as $validatedFilter) {
            list($from, $to) = $validatedFilter;
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $validatedFilter)
            );
        }

        $this->_fromTo['from'] = min($fromMulti);
        $this->_fromTo['to'] = max($toMulti);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentFrom()
    {
        return empty($this->_fromTo['from']) ? $this->getMinPrice() : $this->_fromTo['from'];
    }

    /**
     * @return mixed
     */
    public function getCurrentTo()
    {
        return empty($this->_fromTo['to']) ? $this->getMaxPrice() : $this->_fromTo['to'];
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return float|\Magento\Framework\Phrase
     */
    protected function _renderRangeLabel($fromPrice, $toPrice, $isLast = false)
    {
        return $this->priceCurrency->renderRangeLabel($fromPrice, $toPrice, $this->getFilterSetting());
    }

    /**
     * Init Min-Max Price
     * @return void
     */
    protected function initMinMaxPrice()
    {
        $collection = clone $this->getLayer()->getProductCollection();
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        $this->minMaxPrice = [
            'min' => $collection->getMinPrice(),
            'max' => $collection->getMaxPrice(),
        ];
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    protected function removeMaxPriceFilter(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar());
        if ($filter) {
            $maxPrice = $this->getMaxPrice();
            $options = explode(UrlParser::ALIAS_DELIMITER, $filter);
            foreach ($options as &$option) {
                $range = explode('-', $option);
                if (count($range) == 2 && $range[1] >= $maxPrice) {
                    $option = $range[0] . '-';
                }
            }

            $filter = implode(UrlParser::ALIAS_DELIMITER, $options);
            $request->setParam($this->getRequestVar(), $filter);
        }
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollectionOrigin = $this->getLayer()
            ->getProductCollection();

        if ($this->attributeValue) {
            $productCollection = clone $productCollectionOrigin;
            if ($productCollection->memRequestBuilder !== null) {
                $requestBuilder = clone $productCollection->memRequestBuilder;
                $requestBuilder->removePlaceholder($attribute->getAttributeCode() . '.from');
                $requestBuilder->removePlaceholder($attribute->getAttributeCode() . '.to');
                $productCollection->setRequestData($requestBuilder);
            }

            $productCollection->clear();
            $productCollection->loadWithFilter();
            $collection = $productCollection;
        } else {
            $collection = $productCollectionOrigin;
        }

        $data = [];
        $facets = $collection->getFacetedData($attribute->getAttributeCode());
        foreach ($facets as $key => $aggregation) {
            $count = $aggregation['count'];
            if (strpos($key, '_') === false) {
                continue;
            }
            $data[] = $this->prepareData($key, $count, $data);
        }

        if (count($data) == 1) {
            $data = [];
        }

        return $data;
    }

    /**
     * @param string $key
     * @param int $count
     * @param array $itemsData
     * @return array
     */
    private function prepareData($key, $count, $itemsData = [])
    {
        list($from, $to) = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        if ($to == '*') {
            if ($this->getFilterSetting()->isMultiselect()) {
                $to = $from + $this->getPriceRange($itemsData);
            } else {
                $to = $this->getTo($to);
            }
        }
        $label = $this->_renderRangeLabel(
            empty($from) ? 0 : $from * $this->getCurrencyRate(),
            empty($to) ? $to : $to * $this->getCurrencyRate()
        );
        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }

    /**
     * @return \Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface
     */
    protected function getFilterSetting()
    {
        if ($this->filterSetting === null) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }

        return $this->filterSetting;
    }

    /**
     * @param array $itemsData
     * @return float|int
     */
    public function getPriceRange($itemsData = [])
    {
        if (!empty($itemsData)) {
            $item = current($itemsData);
            $from = isset($item['from']) ? (float)$item['from'] : 0;
            $to = isset($item['to']) ? (float)$item['to'] : 0;
            if ($to > $from) {
                return $to - $from;
            }
        }

        $calculation = $this->dataProvider->getRangeCalculationValue();
        if ($calculation == PriceDataProvider::RANGE_CALCULATION_MANUAL) {
            return (float)$this->dataProvider->getRangeStepValue();
        }

        return PriceDataProvider::MIN_RANGE_POWER;
    }
}
