<?php
namespace Digidirect\Catalog\Plugin\Model;

class Config
{
    /**
     * Add custom Sort By option
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param array $options
     * @return array []
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        if (array_key_exists('name', $options)) unset($options['name']);
        if (array_key_exists('price', $options)) unset($options['price']);
        if (array_key_exists('position', $options)) unset($options['position']);
        
        // nmost viewed
        $customOption['most_viewed'] = __('Most Viewed');
        // new products
        $customOption['latest'] = __('New Products');
        // bestsellers
        $customOption['best_sellers'] = __('Best Sellers');
        // product name a to z
        $customOption['product_name_asc'] = __('Product Name A-Z');
        // product name z to a
        $customOption['product_name_desc'] = __('Product Name Z-A');
        // price lowest first
        $customOption['price_lowest_first'] = __('Price Lowest First');
        // price highest first
        $customOption['price_highest_first'] = __('Price Highest First');
        // highest percentage discount
        //$customOption['highest_percent_discount'] = __('Highest % Discount');

        // merge default sorting options with custom options
        $options = array_merge($customOption, $options);
        
        $order = ['most_viewed', 'latest', 'best_sellers', 'product_name_asc', 'product_name_desc', 'price_lowest_first', 'price_highest_first', 'highest_percent_discount'];
        
        uksort($options, function($key1, $key2) use ($order) {
            return ((array_search($key1, $order) > array_search($key2, $order)) ? 1 : -1);
        });

        return $options;
    }
}