<?php
namespace Digidirect\LayeredNavigation\Helper;

use Digidirect\LayeredNavigation;
use Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting\Collection;
use Digidirect\LayeredNavigation\Model\ResourceModel\FilterSetting\CollectionFactory;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\Context;

class FilterSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var LayeredNavigation\Model\FilterSettingFactory
     */
    protected $settingFactory;

    /**
     * FilterSetting constructor.
     * @param Context $context
     * @param CollectionFactory $settingCollectionFactory
     * @param LayeredNavigation\Model\FilterSettingFactory $settingFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $settingCollectionFactory,
        LayeredNavigation\Model\FilterSettingFactory $settingFactory
    ) {
        parent::__construct($context);
        $this->collection = $settingCollectionFactory->create();
        $this->settingFactory = $settingFactory;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return LayeredNavigation\Api\Data\FilterSettingInterface
     */
    public function getSettingByLayerFilter(FilterInterface $layerFilter)
    {
        $filterCode = $this->getFilterCode($layerFilter);
        $setting = null;
        if (isset($filterCode)) {
            $setting = $this->collection->getItemByColumnValue(
                LayeredNavigation\Model\FilterSetting::FILTER_CODE,
                $filterCode
            );
        }

        if ($setting === null) {
            $setting = $this->settingFactory->create();
        }

        if ($filterCode == LayeredNavigation\Helper\Data::CATEGORY_REQUEST_VAR) {
            $setting->setIsMultiselect($this->allowCategoryMultiSelect());
            $setting->setDisplayMode($this->getCategoryDisplayMode());
            $setting->setShowMoreEnabled($this->isCategoryShowMoreEnabled());
            $setting->setShowMoreCount($this->getCategoryShowMoreCount());
        }

        return $setting;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return null|string
     */
    private function getFilterCode(FilterInterface $layerFilter)
    {
        $attributeCode = $layerFilter->getRequestVar();
        $attribute = $layerFilter->getData('attribute_model');
        if ($attribute !== null) {
            $attributeCode = 'attr_' . $attribute->getAttributeCode();
        }

        return $attributeCode;
    }

    /**
     * @return bool
     */
    public function allowCategoryMultiSelect()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/category/allow_multiselect',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return int
     */
    public function getCategoryDisplayMode()
    {
        return $this->scopeConfig->getValue(
            'digidirect_layerednavigation/category/display_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function isCategoryShowMoreEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/category/enable_show_more',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return int
     */
    public function getCategoryShowMoreCount()
    {
        return (int)$this->scopeConfig->getValue(
            'digidirect_layerednavigation/category/options_per_filter',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
}
