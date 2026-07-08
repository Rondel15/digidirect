<?php
namespace Digidirect\LayeredNavigation\Model\Source;

class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * MODE_DEFAULT
     */
    const MODE_DEFAULT = 0;

    /**
     * MODE_DROPDOWN
     */
    const MODE_DROPDOWN = 1;

    /**
     * MODE_SLIDER
     */
    const MODE_SLIDER = 2;

    /**
     * ATTRUBUTE_DEFAULT
     */
    const ATTRUBUTE_DEFAULT = 'default';

    /**
     * ATTRUBUTE_DECIMAL
     */
    const ATTRUBUTE_DECIMAL = 'decimal';

    /**
     * @var string
     */
    protected $attributeType = self::ATTRUBUTE_DEFAULT;

    /**
     * @param string $attributeType
     * @return $this
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;
        return $this;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_getOptions();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [
            self::MODE_DEFAULT => __('Default'),
            self::MODE_DROPDOWN => __('Dropdown')
        ];

        switch ($this->attributeType) {
            case self::ATTRUBUTE_DECIMAL:
                $options[self::MODE_SLIDER] = __('Slider');
                break;
            default:
                break;
        }

        return $options;
    }
}
