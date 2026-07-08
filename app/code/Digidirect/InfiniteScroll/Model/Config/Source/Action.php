<?php
namespace Digidirect\InfiniteScroll\Model\Config\Source;

class Action implements \Magento\Framework\Option\ArrayInterface
{
    const LOAD_ACTION_CLICK = 'click';
    const LOAD_ACTION_SCROLL = 'scroll';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LOAD_ACTION_CLICK, 'label' => __('Click')],
            ['value' => self::LOAD_ACTION_SCROLL, 'label' => __('Scroll')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::LOAD_ACTION_CLICK => __('Click'), self::LOAD_ACTION_SCROLL => __('Scroll')];
    }
}
