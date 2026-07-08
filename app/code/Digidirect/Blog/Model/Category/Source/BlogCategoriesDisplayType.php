<?php

namespace Digidirect\Blog\Model\Category\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class BlogCategoriesDisplayType
 * @package Digidirect\Blog\Model\Category\Source
 */
class BlogCategoriesDisplayType implements ArrayInterface
{
    const SPECIFIED_CATEGORIES_OPTION_VALUE = 'specified_categories';
    const ALL_CATEGORIES_OPTION_VALUE = 'all_categories';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => static::SPECIFIED_CATEGORIES_OPTION_VALUE,
                'label' => __('Specified Categories')
            ],
            [
                'value' => static::ALL_CATEGORIES_OPTION_VALUE,
                'label' => __('All Categories')
            ],
        ];
    }
}
