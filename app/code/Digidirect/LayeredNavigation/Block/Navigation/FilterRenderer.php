<?php
namespace Digidirect\LayeredNavigation\Block\Navigation;

use Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface;
use Digidirect\LayeredNavigation\Helper\FilterSetting;
use Digidirect\LayeredNavigation\Helper\UrlBuilder;
use Digidirect\LayeredNavigation\Helper\UrlParser;
use Digidirect\LayeredNavigation\Model\Layer\Filter\Item;
use Digidirect\LayeredNavigation\Model\Source\DisplayMode;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
    /**
     * @var FilterSetting
     */
    protected $settingHelper;

    /**
     * @var UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var FilterInterface
     */
    protected $filter;

    /**
     * FilterRenderer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param FilterSetting $settingHelper
     * @param UrlBuilder $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        FilterSetting $settingHelper,
        UrlBuilder $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $this->filter = $filter;
        $setting = $this->settingHelper->getSettingByLayerFilter($filter);
        $template = $this->getTemplateByFilterSetting($setting);
        $this->setTemplate($template);
        $this->assign('filterSetting', $setting);
        return parent::render($filter);
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return string
     */
    public function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_SLIDER:
                $template = "layer/filter/slider.phtml";
                break;
            case DisplayMode::MODE_DROPDOWN:
                $template = "layer/filter/dropdown.phtml";
                break;
            default:
                $template = "layer/filter/default.phtml";
                break;
        }

        return $template;
    }

    /**
     * @param Item $filterItem
     * @return int
     */
    public function checkedFilter($filterItem)
    {
        $data = $this->getRequest()->getParam($filterItem->getFilter()->getRequestVar());
        if (!empty($data)) {
            $ids = explode(UrlParser::ALIAS_DELIMITER, $data);
            $values = explode(UrlParser::ALIAS_DELIMITER, $filterItem->getValue());
            if (in_array($filterItem->getValue(), $ids) || empty(array_diff($values, $ids))) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        if (!array_key_exists('filterItems', $this->_viewVars) || !is_array($this->_viewVars['filterItems'])) {
            return '';
        }

        $items = $this->_viewVars['filterItems'];
        foreach ($items as $item) {
            /** @var Item $item */
            if ($this->checkedFilter($item)) {
                return $item->getRemoveUrl();
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSliderUrlTemplate()
    {
        return $this->urlBuilder->buildUrl(
            $this->filter,
            'layered_navigation_slider_from-layered_navigation_slider_to'
        );
    }

    /**
     * @param FilterInterface $filter
     * @return string[]
     */
    public function getActiveLabels(FilterInterface $filter)
    {
        $labels = [];
        foreach ($filter->getItems() as $item) {
            if ($item instanceof Item && $this->checkedFilter($item)) {
                $labels[] = $item->getLabel();
            }
        }
        return $labels;
    }

    /**
     * @param FilterInterface $filter
     * @return int
     */
    public function getActiveCount(FilterInterface $filter)
    {
        $data = $this->getRequest()->getParam($filter->getRequestVar());
        if (!empty($data)) {
            return count(explode(UrlParser::ALIAS_DELIMITER, $data));
        }
        return 0;
    }
}
