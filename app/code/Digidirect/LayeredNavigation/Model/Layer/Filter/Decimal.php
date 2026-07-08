<?php
namespace Digidirect\LayeredNavigation\Model\Layer\Filter;

use Digidirect\LayeredNavigation\Model\Source\DisplayMode;
use Digidirect\LayeredNavigation\Helper\UrlParser;
use Magento\Framework\App\RequestInterface;

class Decimal extends \Magento\CatalogSearch\Model\Layer\Filter\Decimal
{
    /** Price delta for filter  */
    const VALUE_DELTA = 0.001;

    /**
     * @var array
     */
    protected $_fromTo;

    /**
     * @var array
     */
    protected $minMaxValue;

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
     * @var \Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var null
     */
    public $attributeValue;

    /**
     * Decimal constructor.
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory
     * @param \Digidirect\LayeredNavigation\Model\Layer\PriceCurrency $priceCurrency
     * @param \Digidirect\LayeredNavigation\Helper\FilterSetting $settingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Digidirect\LayeredNavigation\Model\Layer\PriceCurrency $priceCurrency,
        \Digidirect\LayeredNavigation\Helper\FilterSetting $settingHelper,
        array $data
    ) {
        $this->settingHelper = $settingHelper;
        $this->currencySymbol = $priceCurrency->getCurrencySymbol();
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $filterDecimalFactory,
            $priceCurrency,
            $data
        );
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    protected function removeMaxValueFilter(RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar());
        if ($filter) {
            $maxPrice = $this->getMaxValue();
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        $isMultiSelect = $this->getFilterSetting()->isMultiselect();
        if (!$isMultiSelect) {
            $this->removeMaxValueFilter($request);
        }

        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $this->attributeValue = $filter;
        $filterParams = explode(UrlParser::ALIAS_DELIMITER, $filter);
        if (!$isMultiSelect && count($filterParams) > 1) {
            $filterParams = [$filterParams[0]];
        }

        $fromMulti = [];
        $toMulti = [];
        $validatedFilters = [];
        foreach ($filterParams as $filterParam) {
            $validatedFilter = explode('-', $filterParam);
            if ($validatedFilter) {
                $validatedFilters[] = $validatedFilter;
                list($from, $to) = $validatedFilter;
                $fromMulti[] = $from;
                if ($isMultiSelect && $to > 0) {
                    $to = $to - self::VALUE_DELTA;
                } else {
                    $to = empty($to) || $from == $to ? $to : $to - self::VALUE_DELTA;
                }
                $toMulti[] = $to;
            }
        }

        $this->getLayer()->getProductCollection()->addFieldToFilter($this->getAttributeModel()->getAttributeCode(), [
            'from' => implode(',', $fromMulti),
            'to' =>  implode(',', $toMulti),
        ]);

        foreach ($validatedFilters as $validatedFilter) {
            list($from, $to) = $validatedFilter;
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $validatedFilter)
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
        return empty($this->_fromTo['from']) ? $this->getMinValue() : $this->_fromTo['from'];
    }

    /**
     * @return mixed
     */
    public function getCurrentTo()
    {
        return empty($this->_fromTo['to']) ? $this->getMaxValue() : $this->_fromTo['to'];
    }

    /**
     * @param null $from
     * @param null $to
     * @return void
     */
    public function setFromTo($from = null, $to = null)
    {
        $this->_fromTo['from'] = $from;
        $this->_fromTo['to'] = $to;
    }

    /**
     * Init Min-Max Price
     * @return void
     */
    protected function initMinMaxValue()
    {
        $collection = clone $this->getLayer()->getProductCollection();
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        /** @var \Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $collection */
        $this->minMaxValue = $collection->getMinMaxValueByAttribute($this->getAttributeModel());
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMinValue()
    {
        if ($this->minMaxValue === null) {
            $this->initMinMaxValue();
        }
        return $this->minMaxValue['min'];
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMaxValue()
    {
        if ($this->minMaxValue === null) {
            $this->initMinMaxValue();
        }
        return $this->minMaxValue['max'];
    }

    /**
     * @return array|\Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected function _initItems()
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        if ($filterSetting->getDisplayMode() != DisplayMode::MODE_SLIDER) {
            return parent::_initItems();
        }

        if (!$this->getMinValue()) {
            $this->_items = [];
            return $this->_items;
        }

        $this->_items = [
            [
                'from' => $this->getCurrentFrom(),
                'to' => $this->getCurrentTo(),
                'min' => $this->getMinValue(),
                'max' => $this->getMaxValue(),
                'requestVar' => $this->getRequestVar(),
                'step' => round($filterSetting->getSliderStep(), 4),
                'template' => !$filterSetting->getUnitsLabelUseCurrencySymbol()
                    ? '{amount} ' . $filterSetting->getUnitsLabel()
                    : $this->currencySymbol . '{amount}'
            ]
        ];
        return $this;
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return \Magento\Framework\Phrase
     */
    protected function renderRangeLabel($fromPrice, $toPrice, $isLast = false)
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        return $this->priceCurrency->renderRangeLabel($fromPrice, $toPrice, $filterSetting);
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        /** @var \Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollectionOrigin = $this->getLayer()->getProductCollection();

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

        $productSize = $collection->getSize();
        $facets = $collection->getFacetedData($attribute->getAttributeCode());

        $data = [];
        foreach ($facets as $key => $aggregation) {
            $count = $aggregation['count'];
            if (!$this->isOptionReducesResults($count, $productSize)) {
                continue;
            }
            list($from, $to) = explode('_', $key);
            if ($from == '*') {
                $from = '';
            }
            if ($to == '*') {
                $to = '';
            }
            $label = $this->renderRangeLabel(
                empty($from) ? 0 : $from,
                empty($to) ? $to : $to
            );
            $value = $from . '-' . $to;

            $data[] = [
                'label' => $label,
                'value' => $value,
                'count' => $count,
                'from' => $from,
                'to' => $to
            ];
        }

        return $data;
    }
}
