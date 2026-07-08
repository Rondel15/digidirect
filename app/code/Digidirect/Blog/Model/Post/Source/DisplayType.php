<?php

namespace Digidirect\Blog\Model\Post\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DisplayType
 */
class DisplayType implements ArrayInterface
{
    const SPECIFIED_POSTS_OPTION_VALUE = 'specified_posts';
    const RECENTLY_ADDED_POSTS_OPTION_VALUE = 'recent_posts';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => static::SPECIFIED_POSTS_OPTION_VALUE,
                'label' => __('Specified Posts')
            ],
            [
                'value' => static::RECENTLY_ADDED_POSTS_OPTION_VALUE,
                'label' => __('Recently Added Posts')
            ],
        ];
    }
}
