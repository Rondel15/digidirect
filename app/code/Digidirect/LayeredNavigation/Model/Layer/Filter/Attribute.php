<?php
namespace Digidirect\LayeredNavigation\Model\Layer\Filter;

use Digidirect\LayeredNavigation\Helper\FilterSetting;
use Digidirect\LayeredNavigation\Helper\UrlParser;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\LayeredNavigation\Model\Source\DisplayMode;

/**
 * Layer attribute filter
 */
class Attribute extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    private $tagFilter;

    /**
     * @var FilterSetting
     */
    protected $settingHelper;

    /**
     * @var \Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var null
     */
    public $attributeValue = null;

    /**
     * @var array
     */
    protected $attributeItems = [];

    /**
     * Attribute constructor.
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param FilterSetting $settingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        FilterSetting $settingHelper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->settingHelper = $settingHelper;
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValue = $request->getParam($this->_requestVar);
        if (empty($attributeValue)) {
            return $this;
        }

        $this->attributeValue = $attributeValue;
        $values = explode(UrlParser::ALIAS_DELIMITER, $attributeValue);
        if (!$this->isMultiselectAllowed() && count($values) > 1) {
            $values = [$values[0]];
        }

        $attribute = $this->getAttributeModel();
        /** @var \Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()
            ->getProductCollection();

        $collectionValue = count($values) > 1 ? $values : $values[0];
        $productCollection->addFieldToFilter($attribute->getAttributeCode(), $collectionValue);

        foreach ($values as $value) {
            $label = $this->getOptionText($value);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $value));
        }

        if (!$this->isVisibleWhenSelected()) {
            $this->_items = [];
        }

        return $this;
    }

    /**
     * @return bool|null
     */
    private function isMultiselectAllowed()
    {
        return $this->getFilterSetting()->isMultiselect();
    }

    /**
     * @return bool
     */
    private function isVisibleWhenSelected()
    {
        return $this->isMultiselectAllowed() || $this->isDropdown();
    }

    /**
     * @return bool
     */
    private function isDropdown()
    {
        return $this->getFilterSetting()->getDisplayMode()== DisplayMode::MODE_DROPDOWN;
    }

    /**
     * Re-initialization of attribute options
     *
     * @return array
     */
    public function getItems()
    {
        $attributeCode = $this->getAttributeModel()->getAttributeCode();
        if (!array_key_exists($attributeCode, $this->attributeItems)) {
            $this->_items = null;
            $this->attributeItems[$attributeCode] = parent::getItems();
        }
        return $this->attributeItems[$attributeCode];
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
                $requestBuilder->removePlaceholder($attribute->getAttributeCode());
                $productCollection->setRequestData($requestBuilder);
            }

            $productCollection->clear();
            $productCollection->loadWithFilter();
            $collection = $productCollection;
        } else {
            $collection = $productCollectionOrigin;
        }

        $optionsFacetedData = $collection->getFacetedData($attribute->getAttributeCode());
        if (empty($optionsFacetedData)) {
            return $this->itemDataBuilder->build();
        }

        $options = $attribute->getFrontend()
            ->getSelectOptions();

        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }

            if (isset($optionsFacetedData[$option['value']])) {
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $optionsFacetedData[$option['value']]['count']
                );
            } else if ($this->getAttributeIsFilterable($attribute) !== static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS) {
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    0
                );
            }
        }

        $itemsData = $this->itemDataBuilder->build();
        $setting = $this->settingHelper->getSettingByLayerFilter($this);
        if ($setting->getHideOneOption()) {
            if (count($itemsData) == 1) {
                $itemsData = [];
            }
        }

        return $itemsData;
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
}
