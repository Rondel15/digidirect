<?php
namespace Digidirect\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Digidirect\LayeredNavigation\Helper\UrlParser;

class SwatchRenderer extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{
    /**
     * @var \Digidirect\LayeredNavigation\Helper\UrlBuilder
     */
    protected $urlBuilderHelper;

    /**
     * Filter setting helper
     * @var \Digidirect\LayeredNavigation\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * Path to template file.
     * @var string
     */
    protected $_template = 'Digidirect_LayeredNavigation::layer/filter/swatch.phtml';

    /**
     * SwatchRenderer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Attribute $eavAttribute
     * @param AttributeFactory $layerAttribute
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Swatches\Helper\Media $mediaHelper
     * @param \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilderHelper
     * @param \Digidirect\LayeredNavigation\Helper\FilterSetting $filterSettingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        \Digidirect\LayeredNavigation\Helper\UrlBuilder $urlBuilderHelper,
        \Digidirect\LayeredNavigation\Helper\FilterSetting $filterSettingHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $eavAttribute,
            $layerAttribute,
            $swatchHelper,
            $mediaHelper,
            $data
        );
        $this->urlBuilderHelper = $urlBuilderHelper;
        $this->filterSettingHelper = $filterSettingHelper;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        return $this->urlBuilderHelper->buildUrl($this->filter, $optionId);
    }

    /**
     * @param int $value
     * @return bool
     */
    public function checkedFilter($value)
    {
        $data = $this->getRequest()->getParam($this->filter->getRequestVar());
        if (!empty($data)) {
            $ids = explode(UrlParser::ALIAS_DELIMITER, $data);
            if (in_array($value, $ids)) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * Get if attribute is multiple select
     * @return bool|null
     */
    public function getIsMultipleSelect()
    {
        return $this->filterSettingHelper->getSettingByLayerFilter($this->filter)->isMultiselect();
    }
}
